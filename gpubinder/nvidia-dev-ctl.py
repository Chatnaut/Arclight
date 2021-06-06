#!/usr/bin/env python3

#    Copyright (C) 2020  Dmitri Rubinstein, DFKI GmbH
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <https://www.gnu.org/licenses/>.
#
# Documentation
# https://docs.nvidia.com/grid/10.0/grid-vgpu-user-guide/index.html#vgpu-information-in-sysfs-file-system
# https://man7.org/linux/man-pages/man5/sysfs.5.html

import argparse
import logging
import os
import os.path
import grp
import subprocess
from subprocess import CalledProcessError
import sys
import time
import re
from collections import OrderedDict, defaultdict, namedtuple
from typing import Callable, Optional, Sequence, List, Union, NamedTuple
import uuid
import tempfile
import xml.etree.ElementTree as ET

LOG = logging.getLogger(__name__)

MDEV_BUS_CLASS_PATH = "/sys/class/mdev_bus"

MDEV_BUS_DEVICE_PATH = "/sys/bus/mdev/devices"

PCI_BUS_DEVICE_PATH = "/sys/bus/pci/devices"

PCI_BUS_DRIVER_PATH = "/sys/bus/pci/drivers"

NVIDIA_VENDOR = "10de"

TEXT_FORMAT = "text"
TABLE_FORMAT = "table"

PathWaiterFunc = Callable[[str], bool]
PathWaiterCB = Optional[PathWaiterFunc]


class DevCtlException(Exception):
    pass


class SysfsPathNotFoundError(DevCtlException):
    def __init__(self, path: str, message: str = None):
        super().__init__(message if message is not None else "Sysfs path '{}' not found".format(path))
        self.path = path


class DeviceDriverPathNotFound(SysfsPathNotFoundError):
    def __init__(self, path: str):
        super().__init__(path, "Device driver path '{}' not found".format(path))


class BindDriverPathNotFound(SysfsPathNotFoundError):
    def __init__(self, path: str):
        super().__init__(path, "Bind driver path '{}' not found".format(path))


class UnbindDriverPathNotFound(SysfsPathNotFoundError):
    def __init__(self, path: str):
        super().__init__(path, "Unbind driver path '{}' not found".format(path))


class DriverOverridePathNotFound(SysfsPathNotFoundError):
    def __init__(self, path: str):
        super().__init__(path, "Driver override path '{}' not found".format(path))


class MdevBusPathNotFound(SysfsPathNotFoundError):
    def __init__(self, path: str):
        super().__init__("MDEV path '{}' not found".format(path))


class InvalidPCIAddressError(DevCtlException):
    def __init__(self, pci_address: str):
        super().__init__("{} is not a PCI address".format(pci_address))
        self.pci_address = pci_address


class NoPCIAddressError(DevCtlException):
    def __init__(self, pci_address: str):
        super().__init__("No such PCI address: '{}'".format(pci_address))
        self.pci_address = pci_address


class NoMdevPCIAddressError(DevCtlException):
    def __init__(self, pci_address: str):
        super().__init__("No such MDEV PCI address: '{}'".format(pci_address))
        self.pci_address = pci_address


class NoMdevUUIDError(DevCtlException):
    def __init__(self, uuid: str):
        super().__init__("No such MDEV UUID: '{}'".format(uuid))
        self.uuid = uuid


class InvalidMdevFileFormat(DevCtlException):
    pass


class InvalidCommandOutput(DevCtlException):
    pass


def sysfs_pci_device_path(pci_address):
    return os.path.join(PCI_BUS_DEVICE_PATH, pci_address)


def sysfs_pci_driver_path(driver):
    return os.path.join(PCI_BUS_DRIVER_PATH, driver)


def sysfs_mdev_supported_types_path(pci_address):
    return os.path.join(MDEV_BUS_CLASS_PATH, pci_address, "mdev_supported_types")


def sysfs_mdev_type_path(pci_address, mdev_type_name):
    return os.path.join(sysfs_mdev_supported_types_path(pci_address), mdev_type_name)


def sysfs_mdev_path(pci_address, mdev_type_name, mdev_uuid):
    return os.path.join(
        sysfs_pci_device_path(pci_address), "mdev_supported_types", mdev_type_name, "devices", mdev_uuid
    )


def sysfs_mdev_remove_path(pci_address, mdev_type_name, mdev_uuid):
    return os.path.join(
        sysfs_pci_device_path(pci_address), "mdev_supported_types", mdev_type_name, "devices", mdev_uuid, "remove"
    )


RE_EXEC_MAIN_STATUS = re.compile(r"ExecMainStatus=(\d+)")

RE_DOMAIN_STATE = re.compile(r"^State:\s*([^\s].*)$", re.MULTILINE)

RE_PCI_ADDRESS = re.compile(r"([0-9a-fA-F]{4}):([0-9a-fA-F]{2}):([0-9a-fA-F]{2}).([0-9a-fA-F])")


class PCIAddress(namedtuple("PCIAddress", ["domain", "bus", "slot", "function"])):
    __slots__ = ()

    def __str__(self):
        return "{:04x}:{:02x}:{:02x}.{:01x}".format(self.domain, self.bus, self.slot, self.function)

    def __repr__(self):
        return "PCIAddress(domain=0x{:04x}, bus=0x{:02x}, slot=0x{:02x}, function=0x{:01x})".format(
            self.domain, self.bus, self.slot, self.function
        )

    def __format__(self, format_spec):
        return format(str(self), format_spec)

    @classmethod
    def parse(cls, pci_address: str) -> "PCIAddress":
        m = RE_PCI_ADDRESS.match(pci_address)
        if not m:
            raise InvalidPCIAddressError(pci_address)

        return cls(
            domain=int(m.group(1), 16), bus=int(m.group(2), 16), slot=int(m.group(3), 16), function=int(m.group(4), 16)
        )


PCIAddressFilterFunc = Callable[[PCIAddress], bool]
PCIAddressFilterCB = Optional[PCIAddressFilterFunc]


class PCIDevice(
    NamedTuple(
        "PCIDevice", [("pci_address", PCIAddress), ("driver", str), ("name", str), ("vendor", str), ("tags", dict)]
    )
):
    __slots__ = ()

    def get_tag(self, tag_name, default=None):
        return self.tags.get(tag_name, default)

    @classmethod
    def from_tags(cls, tags: dict):
        slot = tags.get("Slot")
        if slot:
            pci_address = PCIAddress.parse(slot)
        else:
            raise InvalidPCIAddressError('No "Slot" tag in the lspci output')
        name = tags.get("Device", "")
        vendor = tags.get("Vendor", "")

        try:
            driver_name = get_driver_of_pci_device(pci_address)
        except NoPCIAddressError:
            driver_name = "no device"
        except DeviceDriverPathNotFound:
            driver_name = "no driver"

        return cls(pci_address=pci_address, driver=driver_name, name=name, vendor=vendor, tags=tags)


PCIDeviceFilterFunc = Callable[[PCIDevice], bool]
PCIDeviceFilterCB = Optional[PCIDeviceFilterFunc]


class PCIDevices(object):
    def __init__(self, device_filter=None):
        self.device_filter = device_filter
        self.devices = []
        self.parse()

    def filter_devices(self, pci_address: Union[str, PCIAddress]):
        if not isinstance(pci_address, PCIAddress):
            pci_address = PCIAddress.parse(pci_address)
        return [dev for dev in self.devices if dev.pci_address == pci_address]

    def find_device(self, pci_address: Union[str, PCIAddress]) -> "Optional[PCIDevice]":
        if not isinstance(pci_address, PCIAddress):
            pci_address = PCIAddress.parse(pci_address)
        for dev in self.devices:
            if dev.pci_address == pci_address:
                return dev
        return None

    def get_tag(self, pci_address: Union[str, PCIAddress], tag_name, default=None):
        dev = self.find_device(pci_address)
        if dev:
            return dev.get_tag(tag_name, default)
        return default

    def parse(self):
        args = ["lspci", "-D", "-vmm"]
        if self.device_filter:
            args.extend(("-d", self.device_filter))
        output = subprocess.check_output(args).decode("utf-8")
        entry = None
        for line in output.splitlines():
            line = line.strip()
            if not line:
                if entry:
                    self.devices.append(PCIDevice.from_tags(entry))
                entry = None
                continue
            split_pos = line.find(":\t")
            if split_pos == -1:
                raise InvalidCommandOutput("Invalid line in lspci output: {}".format(line))
            tag, value = line.split(":\t", 2)
            if not entry:
                entry = {}
            entry[tag] = value
        if entry:
            self.devices.append(PCIDevice.from_tags(entry))


PCI_DEVICES: Optional[PCIDevices] = None


class UsedPCIDevice(NamedTuple("UsedPCIDevice", [("pci_device", PCIDevice), ("domain", str)])):
    __slots__ = ()


class UsedMdevDevice(
    NamedTuple("UsedMdevDevice", [("mdev_device", "MdevDevice"), ("pci_device", PCIDevice), ("domain", str)])
):
    __slots__ = ()


def get_service_exit_code(service_name):
    output = subprocess.check_output(["systemctl", "show", "-p", "ExecMainStatus", service_name]).decode("utf-8")
    m = RE_EXEC_MAIN_STATUS.match(output)
    if m:
        return int(m.group(1))
    raise DevCtlException('Invalid output from "systemctl show -p ExecMainStatus {}": {}'.format(service_name, output))


def restart_nvidia_services(delay=5, dry_run=False):
    for svc in ("nvidia-vgpud.service", "nvidia-vgpu-mgr.service"):
        LOG.info("restart-nvidia-services: Restartig service %s", svc)
        if not dry_run:
            subprocess.check_call(["systemctl", "restart", svc])
            time.sleep(delay)
            if get_service_exit_code(svc) != 0:
                LOG.error("restart-nvidia-services: Service %s failed", svc)
                return False
            else:
                LOG.info("restart-nvidia-services: Service %s successfully restarted", svc)
    return True


def load_driver(driver_name, dry_run=False):
    if not dry_run:
        subprocess.check_call(["modprobe", driver_name])
    if driver_name == "nvidia":
        if not dry_run:
            try:
                subprocess.check_call(["modprobe", "nvidia_vgpu_vfio"])
                if not os.path.exists(MDEV_BUS_CLASS_PATH):
                    restart_nvidia_services()
            except CalledProcessError:
                LOG.exception("Could not load nvidia_vgpu_vfio module")
                return False  # FIXME What if we don't use nvidia_vgpu_vfio module ?
    driver_path = sysfs_pci_driver_path(driver_name)
    if not dry_run:
        if not os.path.exists(driver_path):
            raise DeviceDriverPathNotFound(driver_path)
        else:
            LOG.info("Loaded %s driver", driver_name)
    return True


# https://stackoverflow.com/questions/9535954/printing-lists-as-tabular-data
def print_table(table: Sequence):
    longest_cols = [(max([len(str(row[i])) for row in table]) + 1) for i in range(len(table[0]))]
    row_format = "".join(["{:<" + str(longest_col) + "}" for longest_col in longest_cols])
    for row in table:
        print(row_format.format(*row))


def each_mdev_device_class_pci_address(path_waiter: PathWaiterCB = None):
    if path_waiter:
        path_waiter(MDEV_BUS_CLASS_PATH)
    for pci_address in sorted(os.listdir(MDEV_BUS_CLASS_PATH)):
        yield pci_address


def each_supported_mdev_type_and_path(pci_address, path_waiter: PathWaiterCB = None):
    path = os.path.join(MDEV_BUS_CLASS_PATH, pci_address, "mdev_supported_types")
    if path_waiter:
        path_waiter(path)
    for mdev_type in sorted(os.listdir(path)):
        yield mdev_type, os.path.join(path, mdev_type)


def each_mdev_device_uuid(path_waiter: PathWaiterCB = None):
    if path_waiter:
        path_waiter(MDEV_BUS_DEVICE_PATH)
    for mdev_uuid in sorted(os.listdir(MDEV_BUS_DEVICE_PATH)):
        yield mdev_uuid


def each_pci_device_address_and_path(vendor=None, path_waiter: PathWaiterCB = None):
    if vendor and not vendor.startswith("0x"):
        vendor = "0x" + vendor

    if path_waiter:
        path_waiter(PCI_BUS_DEVICE_PATH)

    for dev in sorted(os.listdir(PCI_BUS_DEVICE_PATH)):
        dev_path = os.path.join(PCI_BUS_DEVICE_PATH, dev)

        if path_waiter:
            path_waiter(dev_path)

        vendor_path = os.path.join(dev_path, "vendor")

        if path_waiter:
            path_waiter(vendor_path)

        if vendor and os.path.exists(vendor_path):
            with open(vendor_path) as f:
                device_vendor = f.read().rstrip("\n")
            if device_vendor != vendor:
                continue
        yield dev, dev_path


def unbind_driver_from_pci_devices(
    driver: str, devices: Sequence[str], path_waiter: PathWaiterCB = None, dry_run=False
):
    assert driver is not None, "driver should be not None"
    driver_path = "/sys/bus/pci/drivers/{}".format(driver)
    if path_waiter:
        path_waiter(driver_path)
    if not os.path.exists(driver_path):
        raise DeviceDriverPathNotFound(driver_path)
    driver_unbind_path = os.path.join(driver_path, "unbind")
    if path_waiter:
        path_waiter(driver_unbind_path)
    if not os.path.exists(driver_unbind_path):
        raise UnbindDriverPathNotFound(driver_unbind_path)
    for dev in devices:
        device_driver_path = os.path.join(driver_path, dev)
        if path_waiter:
            path_waiter(device_driver_path)
        if not os.path.exists(device_driver_path):
            raise DeviceDriverPathNotFound(device_driver_path)
        if not dry_run:
            with open(driver_unbind_path, "w") as f:
                LOG.info("Unbind driver %s from PCI device %s", driver, dev)
                print(dev, file=f)
        else:
            LOG.info("Dry run: Unbind driver %s from PCI device %s", driver, dev)


def get_driver_of_pci_device(
    pci_device_address, empty_driver_name_if_no_driver=False, path_waiter: PathWaiterCB = None
):
    if not pci_device_address:
        raise NoPCIAddressError(pci_device_address)
    device_path = sysfs_pci_device_path(str(pci_device_address))
    driver_path = os.path.join(device_path, "driver")
    if path_waiter:
        path_waiter(driver_path)
    if not os.path.exists(driver_path):
        if empty_driver_name_if_no_driver:
            return ""
        raise DeviceDriverPathNotFound(driver_path)
    driver_path = os.path.realpath(driver_path)
    driver_name = os.path.basename(driver_path)
    return driver_name


def unbind_pci_device_drivers(devices: Sequence[str], path_waiter: PathWaiterCB = None, dry_run=False):
    for device in devices:
        device_driver = get_driver_of_pci_device(device, empty_driver_name_if_no_driver=True, path_waiter=path_waiter)
        if device_driver:
            unbind_driver_from_pci_devices(device_driver, [device], path_waiter=path_waiter, dry_run=dry_run)


def bind_driver_to_pci_devices(
    driver_name: str, devices: Sequence[str], path_waiter: PathWaiterCB = None, dry_run=False
):
    assert driver_name is not None, "driver name should be not None"
    dry_run_prefix = "Dry run: " if dry_run else ""
    for dev in devices:
        current_driver_name = get_driver_of_pci_device(
            dev, empty_driver_name_if_no_driver=True, path_waiter=path_waiter
        )
        if current_driver_name == driver_name:
            LOG.info(
                "Do not change the driver on the device %s, because it already has driver %s",
                dev,
                driver_name,
            )
            continue
        if current_driver_name != "":
            # unbind driver first
            unbind_driver_from_pci_devices(current_driver_name, [dev], path_waiter=path_waiter, dry_run=dry_run)

        driver_override_path = "/sys/bus/pci/devices/{}/driver_override".format(dev)
        if path_waiter:
            path_waiter(driver_override_path)
        if not os.path.exists(driver_override_path):
            raise DriverOverridePathNotFound(driver_override_path)
        LOG.info(dry_run_prefix + "Override driver %s for PCI device %s", driver_name, dev)
        if not dry_run:
            with open(driver_override_path, "w") as f:
                print(driver_name, file=f)
        driver_path = sysfs_pci_driver_path(driver_name)
        if not os.path.exists(driver_path):
            LOG.info(dry_run_prefix + "Driver %s missing, try loading it first", driver_name)
            load_driver(driver_name)
        LOG.info(dry_run_prefix + "Bind driver %s to device %s", driver_name, dev)
        if not dry_run:
            driver_bind_path = "/sys/bus/pci/drivers/{}/bind".format(driver_name)
            if path_waiter:
                path_waiter(driver_bind_path)
            if not os.path.exists(driver_bind_path):
                raise BindDriverPathNotFound(driver_bind_path)
            try:
                with open(driver_bind_path, "w") as f:
                    print(dev, file=f)
            except OSError:
                # Sometimes 'OSError: [Errno 19] No such device' appears, we check if it can be ignored.
                current_driver_name = get_driver_of_pci_device(dev, empty_driver_name_if_no_driver=True)
                if current_driver_name != driver_name:
                    raise
    return True


class MdevType:
    def __init__(self, path, path_waiter: PathWaiterCB = None):
        self.path = path
        self.path_waiter = path_waiter
        self.realpath = os.path.realpath(path)
        path_head, self.type = os.path.split(self.realpath)
        path_head_1, mdev_supported_types_sym = os.path.split(path_head)
        if mdev_supported_types_sym != "mdev_supported_types":
            raise DevCtlException("Path {} does not end with 'mdev_supported_types".format(path_head))
        self.pci_address = os.path.basename(path_head_1)
        os.path.dirname(self.realpath)
        self.name = None
        self.description = None
        self.device_api = None
        self.available_instances: Optional[int] = None
        self.update()

    def create(self, uuid, dry_run=False):
        create_path = os.path.join(self.path, "create")
        if self.path_waiter:
            self.path_waiter(create_path)
        dry_run_prefix = "Dry run: " if dry_run else ""
        LOG.info(
            dry_run_prefix + "Create mdev device with UUID %s on PCI address %s and with type %s",
            uuid,
            self.pci_address,
            self.type,
        )
        if not dry_run:
            with open(create_path, "w") as f:
                print(uuid, file=f)
            self.update()
        return dry_run or os.path.exists(sysfs_mdev_path(self.pci_address, self.type, uuid))

    def remove(self, uuid, dry_run=False):
        remove_path = sysfs_mdev_remove_path(self.pci_address, self.type, uuid)
        if self.path_waiter:
            self.path_waiter(remove_path)
        dry_run_prefix = "Dry run: " if dry_run else ""
        LOG.info(
            dry_run_prefix + "Remove mdev device with UUID %s on PCI address %s and with type %s",
            uuid,
            self.pci_address,
            self.type,
        )
        if not dry_run:
            with open(remove_path, "w") as f:
                print("1", file=f)
            # Wait until the path of the remote mdev device disappears
            counter = 10
            while os.path.exists(self.path) and counter > 0:
                time.sleep(0.001)
                counter -= 1
            # Reset path to realpath since path is not valid anymore
            self.path = self.realpath
            self.update()
        return dry_run or not os.path.exists(sysfs_mdev_path(self.pci_address, self.type, uuid))

    def update(self):
        fields = (
            ("name", str),
            ("description", str),
            ("device_api", str),
            ("available_instances", int),
        )
        for field_name, field_type in fields:
            field_path = os.path.join(self.path, field_name)
            if self.path_waiter:
                self.path_waiter(field_path)
            with open(field_path, "r") as f:
                setattr(self, field_name, field_type(f.read().rstrip("\n")))

    def __repr__(self):
        return "MdevType(path={!r})".format(self.path)

    def __str__(self):
        return (
            "<MdevType path={!r} realpath={!r} type={!r} name={!r} description={!r} device_api={!r} "
            "available_instances={!r}>".format(
                self.path,
                self.realpath,
                self.type,
                self.name,
                self.description,
                self.device_api,
                self.available_instances,
            )
        )

    @classmethod
    def from_path(cls, path: str, path_waiter: PathWaiterCB = None) -> "MdevType":
        return cls(path, path_waiter=path_waiter)


MdevTypeFilterFunc = Callable[[MdevType], bool]
MdevTypeFilterCB = Optional[MdevTypeFilterFunc]


class MdevDeviceClass:
    def __init__(self, pci_address, path, path_waiter: PathWaiterCB = None):
        self.pci_address = pci_address  # PCI address
        self.path = path  # device path
        self.path_waiter = path_waiter
        self._supported_mdev_types: Optional[OrderedDict[str, MdevType]] = None  # maps mdev_type to MdevType

    def find_supported_mdev_type(self, name_or_type: str):
        mdev_type = self.supported_mdev_types.get(name_or_type)
        if mdev_type:
            return mdev_type
        for mdev_type in self.supported_mdev_types.values():
            if mdev_type.name == name_or_type:
                return mdev_type
        return None

    @property
    def supported_mdev_types(self) -> "OrderedDict[str, MdevType]":
        if self._supported_mdev_types is None:
            self._supported_mdev_types = OrderedDict()
            for mdev_type, mdev_type_path in each_supported_mdev_type_and_path(
                self.pci_address, path_waiter=self.path_waiter
            ):
                self._supported_mdev_types[mdev_type] = MdevType.from_path(mdev_type_path, path_waiter=self.path_waiter)
        return self._supported_mdev_types

    def __repr__(self):
        return "MdevDeviceClass(pci_address={!r}, path={!r})".format(self.pci_address, self.path)

    def __str__(self):
        return "<MdevDeviceClass pci_address={!r} path={!r} supported_mdev_types={!r}>".format(
            self.pci_address, self.path, list(self.supported_mdev_types.keys())
        )

    @classmethod
    def from_pci_address(cls, pci_address, path_waiter: PathWaiterCB = None):
        if path_waiter:
            path_waiter(MDEV_BUS_CLASS_PATH)
        if not os.path.exists(MDEV_BUS_CLASS_PATH):
            raise MdevBusPathNotFound(MDEV_BUS_CLASS_PATH)
        path = os.path.join(MDEV_BUS_CLASS_PATH, pci_address)
        if path_waiter:
            path_waiter(path)
        if not os.path.exists(path):
            raise NoMdevPCIAddressError(pci_address)
        return cls(pci_address=pci_address, path=path, path_waiter=path_waiter)

    @classmethod
    def from_pci_address_unchecked(cls, pci_address, path_waiter: PathWaiterCB = None):
        path = os.path.join(MDEV_BUS_CLASS_PATH, pci_address)
        if path_waiter:
            path_waiter(path)
        return cls(pci_address=pci_address, path=path, path_waiter=path_waiter)


class MdevNvidia:
    def __init__(self, path, vm_name, vgpu_params):
        self.path = path
        self.vm_name = vm_name
        self.vgpu_params = vgpu_params

    def __str__(self):
        return "mdev_nvidia path={} vm_name={} vgpu_params={}".format(self.path, self.vm_name, self.vgpu_params)

    def __repr__(self):
        return "MdevNvidia(path={!r}, vm_name={!r}, vgpu_params={!r})".format(self.path, self.vm_name, self.vgpu_params)

    @classmethod
    def from_path(cls, path):
        fields = ("vm_name", "vgpu_params")
        kwargs = {"path": path}
        for field in fields:
            with open(os.path.join(path, field)) as f:
                kwargs[field] = f.read().rstrip("\n")
        return cls(**kwargs)


class MdevDevice:
    def __init__(self, uuid, path):
        self.uuid = uuid  # mdev device UUID
        self.path = path  # device path
        self.realpath = os.path.realpath(path)
        self.pci_address = os.path.basename(os.path.dirname(self.realpath))
        self._mdev_type = None
        self._nvidia = None

    @property
    def mdev_type(self) -> MdevType:
        if self._mdev_type is None:
            self._mdev_type = MdevType.from_path(os.path.join(self.path, "mdev_type"))
        return self._mdev_type

    @property
    def nvidia(self) -> MdevNvidia:
        if self._nvidia is None:
            nvidia_path = os.path.join(self.path, "nvidia")
            if os.path.exists(nvidia_path):
                self._nvidia = MdevNvidia.from_path(nvidia_path)
        return self._nvidia

    @classmethod
    def from_uuid(cls, uuid):
        if not os.path.exists(MDEV_BUS_DEVICE_PATH):
            raise MdevBusPathNotFound(MDEV_BUS_DEVICE_PATH)
        device_path = os.path.join(MDEV_BUS_DEVICE_PATH, uuid)
        if not os.path.exists(device_path):
            raise NoMdevUUIDError(uuid)

        return cls(uuid=uuid, path=device_path)

    @classmethod
    def from_uuid_unchecked(cls, uuid):
        path = os.path.join(MDEV_BUS_DEVICE_PATH, uuid)
        return cls(uuid=uuid, path=path)


class Waiter:
    def __init__(self, check_func, message, num_trials=3, wait_delay=1):
        self.check_func = check_func
        self.message = message
        self.num_trials = num_trials
        self.wait_delay = wait_delay

    def wait(self):
        trial = 0
        result = False
        while not result:
            result = self.check_func()
            if result:
                break
            if self.num_trials > 0:
                trial += 1
                if trial > self.num_trials:
                    break
                LOG.info("[Trial %d / %d] %s", trial, self.num_trials, self.message)
            else:
                LOG.info("[Trying] %s", self.message)
            time.sleep(self.wait_delay)
        return result


class PathWaiter(Waiter):
    def __init__(self, path, num_trials=3, wait_delay=1):
        super().__init__(
            check_func=lambda: os.path.exists(path),
            message="Wait for path {}".format(path),
            num_trials=num_trials,
            wait_delay=wait_delay,
        )


class DevCtl:
    def __init__(
        self,
        wait_for_device=False,
        num_trials=3,
        wait_delay=1,
        virsh_connection="qemu:///system",
        dry_run=False,
        debug=False,
    ):
        self.wait_for_device = wait_for_device
        self.num_trials = num_trials
        self.wait_delay = wait_delay
        self.virsh_connection = virsh_connection
        self._virsh_list_all_cache = None
        self._virsh_dumpxml_cache = {}
        self.dry_run = dry_run
        self.debug = debug
        self._mdev_device_classes: Optional[
            OrderedDict[str, MdevDeviceClass]
        ] = None  # maps PCI address to MdevDeviceClass
        self._mdev_devices: Optional[OrderedDict[str, MdevDevice]] = None  # maps UUID to MdevDevice

    def wait_for_device_path(self, device_path):
        if self.wait_for_device:
            LOG.debug("wait_for_device_path(%r)", device_path)  # ??? DEBUG
            w = PathWaiter(device_path, num_trials=self.num_trials, wait_delay=self.wait_delay)
            result = w.wait()
            del w
        else:
            result = os.path.exists(device_path)
        return result

    @property
    def wait_for_device_enabled(self):
        return self.wait_for_device

    @property
    def mdev_device_classes(self) -> "OrderedDict[str, MdevDeviceClass]":
        if self._mdev_device_classes is None:
            self._mdev_device_classes = OrderedDict()
            for pci_address in each_mdev_device_class_pci_address(path_waiter=self.wait_for_device_path):
                self._mdev_device_classes[pci_address] = MdevDeviceClass.from_pci_address_unchecked(pci_address)
        return self._mdev_device_classes

    @property
    def mdev_devices(self) -> "OrderedDict[str, MdevDevice]":
        if self._mdev_devices is None:
            self._mdev_devices = OrderedDict()
            for mdev_uuid in each_mdev_device_uuid(path_waiter=self.wait_for_device_path):
                self._mdev_devices[mdev_uuid] = MdevDevice.from_uuid_unchecked(mdev_uuid)
        return self._mdev_devices

    def print_mdev_device_classes(
        self,
        pci_address_filter: PCIAddressFilterCB,
        mdev_type_filter: MdevTypeFilterCB,
        all_classes=False,
        output_all_columns=False,
    ):
        def column_filter(row):
            if output_all_columns:
                return row
            else:
                return row[0], row[1], row[2], row[3]

        mdev_types = [
            column_filter(
                (
                    "PCI_ADDRESS",
                    "MDEV_TYPE",
                    "MDEV_NAME",
                    "AVAILABLE_INSTANCES",
                    "DESCRIPTION",
                    "MDEV_DEVICE_CLASS_PATH",
                )
            )
        ]
        for mdev_device_class in self.mdev_device_classes.values():
            pci_address_obj = PCIAddress.parse(mdev_device_class.pci_address)
            if pci_address_filter and not pci_address_filter(pci_address_obj):
                continue

            for mdev_type in mdev_device_class.supported_mdev_types.values():
                if mdev_type_filter and not mdev_type_filter(mdev_type):
                    continue
                if mdev_type.available_instances is not None and mdev_type.available_instances > 0 or all_classes:
                    mdev_types.append(
                        column_filter(
                            (
                                mdev_device_class.pci_address,
                                mdev_type.type,
                                mdev_type.name,
                                mdev_type.available_instances,
                                mdev_type.description,
                                mdev_device_class.path,
                            )
                        )
                    )

        print_table(mdev_types)
        return True

    def print_mdev_devices(
        self, pci_address_filter: PCIAddressFilterCB, mdev_type_filter: MdevTypeFilterCB, output_all_columns=False
    ):
        global PCI_DEVICES
        assert PCI_DEVICES is not None

        def column_filter(row):
            if output_all_columns:
                return row
            else:
                return row[0], row[1], row[2], row[4], row[7]

        mdev_devices_tbl = [
            column_filter(
                (
                    "MDEV_DEVICE_UUID",
                    "PCI_ADDRESS",
                    "DEVICE",
                    "MDEV_TYPE",
                    "MDEV_NAME",
                    "AVAILABLE_INSTANCES",
                    "DESCRIPTION",
                    "VM_NAME",
                )
            )
        ]
        for mdev_device in self.mdev_devices.values():
            pci_address_obj = PCIAddress.parse(mdev_device.pci_address)
            if pci_address_filter and not pci_address_filter(pci_address_obj):
                continue
            if mdev_type_filter and not mdev_type_filter(mdev_device.mdev_type):
                continue

            pci_device = PCI_DEVICES.find_device(mdev_device.pci_address)

            if pci_device is not None:
                mdev_devices_tbl.append(
                    column_filter(
                        (
                            mdev_device.uuid,
                            mdev_device.pci_address,
                            pci_device.name,
                            mdev_device.mdev_type.type,
                            mdev_device.mdev_type.name,
                            mdev_device.mdev_type.available_instances,
                            mdev_device.mdev_type.description,
                            mdev_device.nvidia.vm_name if mdev_device.nvidia else "none",
                        )
                    )
                )

        print_table(mdev_devices_tbl)
        return True

    def print_pci_devices(
        self, pci_address_filter: PCIAddressFilterCB, output_format=TEXT_FORMAT, output_all_columns=False
    ):
        global PCI_DEVICES
        assert PCI_DEVICES is not None

        def column_filter(row):
            if output_all_columns:
                return row
            else:
                return row[0], row[1], row[2]

        pci_devices_tbl = [column_filter(("PCI_ADDRESS", "DEVICE", "DEVICE_DRIVER", "PCI_DEVICE_PATH"))]

        for pci_address, device_path in each_pci_device_address_and_path(
            vendor=NVIDIA_VENDOR, path_waiter=self.wait_for_device_path
        ):
            pci_address_obj = PCIAddress.parse(pci_address)
            if pci_address_filter and not pci_address_filter(pci_address_obj):
                continue
            try:
                driver_name = get_driver_of_pci_device(pci_address)
            except NoPCIAddressError:
                driver_name = "no device"
            except DeviceDriverPathNotFound:
                driver_name = "no driver"

            pci_device = PCI_DEVICES.find_device(pci_address)
            if pci_device is not None:
                pci_devices_tbl.append(column_filter((pci_address, pci_device.name, driver_name, device_path)))

        if output_format == TABLE_FORMAT:
            print_table(pci_devices_tbl)
        else:
            # text format
            print(" ".join([i[0] for i in pci_devices_tbl[1:]]))
        return True

    def save_config(self, output_file) -> bool:
        output_file.write("# NVIDIA Device Configuration\n")
        output_file.write("# This file is auto-generated by nvidia-dev-ctl.py save command\n")
        output_file.write("# pci_address\tdriver_name\tmdev_uuid\tmdev_type\n")

        mdev_devices_by_pci_address = defaultdict(list)

        try:
            for mdev_device in self.mdev_devices.values():
                mdev_devices_by_pci_address[mdev_device.pci_address].append(mdev_device)
        except FileNotFoundError as e:
            if not e.filename.startswith(MDEV_BUS_DEVICE_PATH):
                raise

        for pci_address, device_path in each_pci_device_address_and_path(
            vendor=NVIDIA_VENDOR, path_waiter=self.wait_for_device_path
        ):
            try:
                driver_name = get_driver_of_pci_device(pci_address)
            except NoPCIAddressError:
                continue
            except DeviceDriverPathNotFound:
                continue

            mdev_devices = mdev_devices_by_pci_address.get(pci_address)
            if mdev_devices:
                for mdev_device in mdev_devices:
                    output_file.write(
                        "{}\t{}\t{}\t{}\n".format(
                            pci_address,
                            driver_name,
                            mdev_device.uuid,
                            mdev_device.mdev_type.type,
                        )
                    )
                del mdev_devices_by_pci_address[pci_address]
            else:
                output_file.write("{}\t{}\n".format(pci_address, driver_name))

        for mdev_devices in mdev_devices_by_pci_address.values():
            for mdev_device in mdev_devices:
                LOG.warning(
                    "MDev device without driver: pci_address=%s mdev_uuid=%s mdev_type=%s",
                    mdev_device.pci_address,
                    mdev_device.uuid,
                    mdev_device.mdev_type.type,
                )
        return True

    def rebind_device_driver(self, pci_address: str, driver_name: str, dry_run=False):
        try:
            current_driver_name = get_driver_of_pci_device(pci_address, path_waiter=self.wait_for_device_path)
            if current_driver_name != driver_name:
                self.unbind_driver(current_driver_name, (pci_address,), dry_run=dry_run)
        except DeviceDriverPathNotFound:
            LOG.info("No driver is bound to the PCI device %s", pci_address)
        self.bind_driver(driver_name, (pci_address,), dry_run=dry_run)

    def restore_config(self, input_file, dry_run=False) -> bool:
        success = True
        for line in input_file:
            line = line.strip()
            comment_pos = line.find("#")
            if comment_pos != -1:
                line = line[:comment_pos]
            if line:
                device_config = line.split()
                pci_address, driver_name, mdev_uuid, mdev_type_name = (
                    None,
                    None,
                    None,
                    None,
                )
                if len(device_config) == 2:
                    pci_address, driver_name = device_config
                elif len(device_config) == 4:
                    pci_address, driver_name, mdev_uuid, mdev_type_name = device_config
                else:
                    raise InvalidMdevFileFormat(
                        "In device configuration file should be two (PCI address, driver name) or four components ("
                        "PCI address, driver name, MDEV UUID, MDEV type) separated "
                        "by spaces "
                    )

                self.rebind_device_driver(pci_address, driver_name, dry_run=dry_run)

                if mdev_uuid is not None and mdev_type_name is not None:
                    if not self._create_mdev_internal(pci_address, mdev_type_name, mdev_uuid, dry_run=dry_run):
                        success = False
        return success

    def bind_driver(self, driver, devices: Optional[Sequence[str]] = None, dry_run=False):
        assert driver is not None and driver, "driver should be not None and not empty"
        if not devices:
            return False

        return bind_driver_to_pci_devices(driver, devices, path_waiter=self.wait_for_device_path, dry_run=dry_run)

    def unbind_driver(
        self,
        driver=None,
        devices: Optional[Sequence[str]] = None,
        ignore_others=False,
        dry_run=False,
    ):
        if not devices:
            return False

        for device in devices:
            if driver:
                device_driver = driver
                if ignore_others:
                    current_device_driver = get_driver_of_pci_device(device, path_waiter=self.wait_for_device_path)
                    if current_device_driver != device_driver:
                        LOG.info(
                            "Ignore device %s because it has driver %s and not %s",
                            device,
                            current_device_driver,
                            device_driver,
                        )
                        continue
            else:
                device_driver = get_driver_of_pci_device(device, path_waiter=self.wait_for_device_path)
            unbind_driver_from_pci_devices(
                device_driver,
                [device],
                path_waiter=self.wait_for_device_path,
                dry_run=dry_run,
            )
        return True

    def fix_mdev(self):
        do_restart_services = False
        for svc in ("nvidia-vgpud.service", "nvidia-vgpu-mgr.service"):
            if get_service_exit_code(svc) != 0:
                do_restart_services = True
        if do_restart_services:
            restart_nvidia_services()

    def _create_mdev_internal(self, pci_address: str, mdev_type_name: str, mdev_uuid: str, dry_run=False) -> bool:
        assert pci_address, "PCI address is None or empty"
        assert mdev_type_name, "MDEV type name is None or empty"
        assert mdev_uuid, "MDEV UUID is None or empty"

        self.fix_mdev()

        LOG.info(
            "Create Mdev device with UUID %s and type %s on PCI device with address %s",
            mdev_uuid,
            mdev_type_name,
            pci_address,
        )

        mdev_device = self.mdev_devices.get(mdev_uuid)
        if mdev_device:
            LOG.warning(
                "Ignore Mdev device with UUID %s because it is already registered",
                mdev_uuid,
            )
            return True
        mdev_device_class = self.mdev_device_classes.get(pci_address)
        if not mdev_device_class:
            LOG.error("Mdev device class with PCI address %s does not exist", pci_address)
            return False
        LOG.info("Found device class %s", mdev_device_class)
        mdev_type = mdev_device_class.find_supported_mdev_type(mdev_type_name)
        if not mdev_type:
            LOG.error(
                "Mdev type with name %s does not exist in device class with PCI address %s and path %s",
                mdev_type_name,
                pci_address,
                mdev_device_class.path,
            )
            return False
        LOG.info("Found device type %s", mdev_type)
        if mdev_type.available_instances is None or mdev_type.available_instances <= 0:
            LOG.error(
                "Mdev type with name %s in device class with PCI address %s and path %s has no " "available instances",
                mdev_type_name,
                pci_address,
                mdev_device_class.path,
            )
            return False

        try:
            return mdev_type.create(mdev_uuid, dry_run=dry_run)
        except PermissionError:
            LOG.exception(
                "Could not create mdev device of type %s on device with PCI address %s and path %s, "
                "try to run this command as root",
                mdev_type_name,
                pci_address,
                mdev_device_class.path,
            )
            return False
        except OSError:
            LOG.exception(
                "Could not register mdev type %s with device class with PCI address %s and path %s",
                mdev_type_name,
                pci_address,
                mdev_device_class.path,
            )
            return False

    def create_mdev(
        self,
        pci_address: str,
        mdev_type_name: str,
        driver_name: str = "nvidia",
        mdev_uuid: Optional[str] = None,
        dry_run=False,
    ) -> Optional[str]:
        if not pci_address:
            LOG.error("No PCI address is specified")
            return None
        if not mdev_type_name:
            LOG.error("No mdev type name is specified")
            return None

        if not mdev_uuid:
            mdev_uuid = str(uuid.uuid4())

        self.rebind_device_driver(pci_address, driver_name, dry_run=dry_run)
        if self._create_mdev_internal(pci_address, mdev_type_name, mdev_uuid, dry_run=dry_run):
            return mdev_uuid
        else:
            return None

    def remove_mdev(self, mdev_uuid: str, dry_run=False) -> bool:
        if not mdev_uuid:
            LOG.error("No mdev UUID is specified")
            return False

        mdev_device = self.mdev_devices.get(mdev_uuid)
        if not mdev_device:
            LOG.error("No mdev device with the UUID %s", mdev_uuid)
            return False

        try:
            return mdev_device.mdev_type.remove(mdev_uuid, dry_run=dry_run)
        except PermissionError:
            LOG.exception(
                "Could not remove mdev device with UUID %s, type %s and PCI address %s, "
                "try to run this command as root",
                mdev_uuid,
                mdev_device.mdev_type.type,
                mdev_device.pci_address,
            )
            return False

    def run_virsh(self, args: Sequence[str]):
        virsh_command = ["virsh"]
        if self.virsh_connection:
            virsh_command.append("-c")
            virsh_command.append(self.virsh_connection)
        virsh_command.extend(args)
        LOG.debug("Run: %s", " ".join(virsh_command))
        output = subprocess.check_output(virsh_command).decode("utf-8")
        return output

    def list_all_domains(self, use_cache=False):
        if use_cache and self._virsh_list_all_cache:
            return self._virsh_list_all_cache
        output = self.run_virsh(("list", "--all", "--name"))
        result = [line for line in output.splitlines() if len(line) > 0]
        if use_cache:
            self._virsh_list_all_cache = result
        else:
            self._virsh_list_all_cache = None
        return result

    def dumpxml_of_domain(self, domain_name, use_cache=False):
        if use_cache:
            result = self._virsh_dumpxml_cache.get(domain_name, None)
            if result is not None:
                return result
        result = self.run_virsh(("dumpxml", "--domain", domain_name))
        if use_cache:
            self._virsh_dumpxml_cache[domain_name] = result
        else:
            self._virsh_dumpxml_cache.pop(domain_name, None)
        return result

    def get_domain_state(self, domain: str):
        output = self.run_virsh(["dominfo", domain])
        m = RE_DOMAIN_STATE.search(output)
        if not m:
            raise DevCtlException("Could not get state of the virsh domain {}".format(domain))
        domain_state = m.group(1)
        return domain_state

    def restart_domain(self, domain: str, virsh_trials=60, virsh_delay=1.0, dry_run=False):
        dry_run_prefix = "Dry run: " if dry_run else ""

        LOG.info(dry_run_prefix + "Shutdown domain %s", domain)

        if not dry_run:
            domain_running = True

            self.run_virsh(["shutdown", domain])
            counter = virsh_trials
            while counter > 0:
                domain_state = self.get_domain_state(domain)
                domain_running = domain_state == "running"
                LOG.info("Domain %s is in state %s", domain, domain_state)
                if not domain_running:
                    break
                LOG.info("Waiting for the domain %s to be shut off", domain)
                time.sleep(virsh_delay)
                counter -= 1

            if domain_running:
                raise DevCtlException("Could not stop domain %s", domain)

        LOG.info(dry_run_prefix + "Start domain %s", domain)

        if not dry_run:
            domain_running = False
            self.run_virsh(["start", domain])
            counter = virsh_trials
            while counter > 0:
                domain_state = self.get_domain_state(domain)
                domain_running = domain_state == "running"
                if domain_running:
                    LOG.info("Domain %s is in state %s", domain, domain_state)
                    break
                LOG.info("Waiting for the domain %s to be running", domain)
                time.sleep(virsh_delay)
                counter -= 1

            if not domain_running:
                raise DevCtlException("Could not start domain %s", domain)

    def get_used_pci_devices(
        self, pci_address_filter: PCIAddressFilterCB, use_cache=False
    ) -> "Sequence[UsedPCIDevice]":
        global PCI_DEVICES
        assert PCI_DEVICES is not None

        used_pci_devices: List[UsedPCIDevice] = []

        all_domains = self.list_all_domains(use_cache=use_cache)
        for domain in all_domains:
            xml = self.dumpxml_of_domain(domain, use_cache=use_cache)
            root = ET.fromstring(xml)
            for pci_hostdev in root.findall("./devices/hostdev[@type='pci']"):
                if pci_hostdev.attrib.get("mode") == "subsystem":
                    for address in pci_hostdev.findall("./source/address"):
                        pci_domain_str = address.attrib.get("domain")
                        pci_bus_str = address.attrib.get("bus")
                        pci_slot_str = address.attrib.get("slot")
                        pci_function_str = address.attrib.get("function")
                        if not (pci_domain_str and pci_bus_str and pci_slot_str and pci_function_str):
                            continue
                        pci_domain = int(pci_domain_str, 0)
                        pci_bus = int(pci_bus_str, 0)
                        pci_slot = int(pci_slot_str, 0)
                        pci_function = int(pci_function_str, 0)
                        pci_address_obj = PCIAddress(pci_domain, pci_bus, pci_slot, pci_function)

                        if pci_address_filter and not pci_address_filter(pci_address_obj):
                            continue

                        pci_device = PCI_DEVICES.find_device(pci_address_obj)
                        if pci_device is not None:
                            device = UsedPCIDevice(pci_device, domain)
                            used_pci_devices.append(device)
        return used_pci_devices

    def print_used_pci_devices(self, pci_address_filter: PCIAddressFilterCB, output_format=TEXT_FORMAT):
        used_pci_devices_tbl = [("PCI_ADDRESS", "DEVICE", "VM_NAME")]

        used_pci_devices = self.get_used_pci_devices(pci_address_filter=pci_address_filter)
        for used_pci_device in used_pci_devices:
            used_pci_devices_tbl.append(
                (str(used_pci_device.pci_device.pci_address), used_pci_device.pci_device.name, used_pci_device.domain)
            )
        if output_format == TABLE_FORMAT:
            print_table(used_pci_devices_tbl)
        else:
            # text format
            print(" ".join([i[0] for i in used_pci_devices_tbl[1:]]))
        return True

    def get_used_mdev_devices(
        self, pci_address_filter: PCIAddressFilterCB, mdev_type_filter: MdevTypeFilterCB, use_cache=False
    ) -> "Sequence[UsedMdevDevice]":
        global PCI_DEVICES
        assert PCI_DEVICES is not None

        used_mdev_devices = []

        all_domains = self.list_all_domains(use_cache=use_cache)
        for domain in all_domains:
            xml = self.dumpxml_of_domain(domain, use_cache=use_cache)
            root = ET.fromstring(xml)
            for pci_hostdev in root.findall("./devices/hostdev[@type='mdev']"):
                if (
                    pci_hostdev.attrib.get("mode") == "subsystem"
                    and pci_hostdev.attrib.get("model") == "vfio-pci"
                    and pci_hostdev.attrib.get("managed") == "no"
                    and pci_hostdev.attrib.get("display") == "off"
                ):
                    for address in pci_hostdev.findall("./source/address[@uuid]"):
                        mdev_uuid = address.attrib.get("uuid")
                        if not mdev_uuid:
                            continue
                        mdev_device = self.mdev_devices.get(mdev_uuid)
                        if mdev_device:
                            pci_address_obj = PCIAddress.parse(mdev_device.pci_address)
                            if pci_address_filter and not pci_address_filter(pci_address_obj):
                                continue
                            if mdev_type_filter and not mdev_type_filter(mdev_device.mdev_type):
                                continue

                            pci_device = PCI_DEVICES.find_device(mdev_device.pci_address)
                            if pci_device is not None:
                                device = UsedMdevDevice(mdev_device=mdev_device, pci_device=pci_device, domain=domain)
                                used_mdev_devices.append(device)
        return used_mdev_devices

    def print_used_mdev_devices(
        self,
        pci_address_filter: PCIAddressFilterCB,
        mdev_type_filter: MdevTypeFilterCB,
        output_format=TEXT_FORMAT,
        output_all_columns=False,
    ):
        def column_filter(row):
            if output_all_columns:
                return row
            else:
                return row[0], row[1], row[2], row[4], row[7]

        used_mdev_devices_tbl = [
            column_filter(
                (
                    "MDEV_DEVICE_UUID",
                    "PCI_ADDRESS",
                    "DEVICE",
                    "MDEV_TYPE",
                    "MDEV_NAME",
                    "AVAILABLE_INSTANCES",
                    "DESCRIPTION",
                    "VM_NAME",
                )
            )
        ]

        used_mdev_devices = self.get_used_mdev_devices(
            pci_address_filter=pci_address_filter, mdev_type_filter=mdev_type_filter
        )

        for used_mdev_device in used_mdev_devices:
            column = (
                used_mdev_device.mdev_device.uuid,
                used_mdev_device.mdev_device.pci_address,
                used_mdev_device.pci_device.name,
                used_mdev_device.mdev_device.mdev_type.type,
                used_mdev_device.mdev_device.mdev_type.name,
                used_mdev_device.mdev_device.mdev_type.available_instances,
                used_mdev_device.mdev_device.mdev_type.description,
                used_mdev_device.domain,
            )
            used_mdev_devices_tbl.append(column_filter(column))

        if output_format == TABLE_FORMAT:
            print_table(used_mdev_devices_tbl)
        else:
            # text format
            print(" ".join([i[0] for i in used_mdev_devices_tbl[1:]]))
        return True

    def attach_mdev(
        self,
        mdev_uuid: str,
        domain: str,
        hotplug=False,
        restart=False,
        dry_run=False,
    ):
        if restart and hotplug:
            LOG.error("restart and hotplug options cannot be used simultaneously")

        if not mdev_uuid:
            LOG.error("No mdev UUID is specified")
            return False

        if not domain:
            LOG.error("No domain is specified")
            return False

        dry_run_prefix = "Dry run: " if dry_run else ""

        domain_state = self.get_domain_state(domain)
        domain_running = domain_state == "running"

        try:
            dev_fname = None
            with tempfile.NamedTemporaryFile(suffix=".xml", mode="w+t", delete=False) as tmp_dev:
                dev_xml = """
<hostdev mode='subsystem' type='mdev' managed='no' model='vfio-pci'>
      <source>
            <address uuid='{}'/>
      </source>
</hostdev>
                """.format(
                    mdev_uuid
                )
                LOG.debug("XML device file: %s", dev_xml)
                tmp_dev.write(dev_xml)
                dev_fname = tmp_dev.name

            LOG.info(dry_run_prefix + "Attach mdev device %s to domain %s", mdev_uuid, domain)
            if not dry_run:

                if hotplug and domain_running:
                    cmd = ["attach-device", domain, "--file", dev_fname, "--persistent"]
                else:
                    cmd = ["attach-device", domain, "--file", dev_fname, "--config"]

                self.run_virsh(cmd)

        finally:
            if dev_fname:
                os.remove(dev_fname)

        if domain_running and restart and not hotplug:
            self.restart_domain(domain, dry_run=dry_run)
        return True

    def detach_mdev(
        self,
        mdev_uuid: str,
        domain: str,
        hotplug=False,
        restart=False,
        dry_run=False,
    ):
        if restart and hotplug:
            LOG.error("restart and hotplug options cannot be used simultaneously")
        if not mdev_uuid:
            LOG.error("No mdev UUID is specified")
            return False

        if not domain:
            LOG.error("No domain is specified")
            return False

        dry_run_prefix = "Dry run: " if dry_run else ""

        domain_state = self.get_domain_state(domain)
        domain_running = domain_state == "running"

        try:
            dev_fname = None
            with tempfile.NamedTemporaryFile(suffix=".xml", mode="w+t", delete=False) as tmp_dev:
                dev_xml = """
<hostdev mode='subsystem' type='mdev' managed='no' model='vfio-pci''>
      <source>
            <address uuid='{}'/>
      </source>
</hostdev>
                """.format(
                    mdev_uuid
                )
                LOG.debug("XML device file: %s", dev_xml)
                tmp_dev.write(dev_xml)
                dev_fname = tmp_dev.name

            LOG.info(dry_run_prefix + "Detach mdev device %s from domain %s", mdev_uuid, domain)
            if not dry_run:

                if hotplug and domain_running:
                    cmd = ["detach-device", domain, "--file", dev_fname, "--persistent"]
                else:
                    cmd = ["detach-device", domain, "--file", dev_fname, "--config"]

                self.run_virsh(cmd)

        finally:
            if dev_fname:
                os.remove(dev_fname)

        if domain_running and restart and not hotplug:
            self.restart_domain(domain, dry_run=dry_run)
        return True

    def attach_pci(
        self,
        pci_address: str,
        domain: str,
        hotplug=False,
        restart=False,
        dry_run=False,
    ):
        if restart and hotplug:
            LOG.error("restart and hotplug options cannot be used simultaneously")

        if not pci_address:
            LOG.error("No PCI address is specified")
            return False

        if not domain:
            LOG.error("No domain is specified")
            return False

        dry_run_prefix = "Dry run: " if dry_run else ""

        domain_state = self.get_domain_state(domain)
        domain_running = domain_state == "running"

        pci_address_obj = PCIAddress.parse(pci_address)

        if not self.bind_driver(driver="vfio-pci", devices=[pci_address], dry_run=dry_run):
            return False

        try:
            dev_fname = None
            with tempfile.NamedTemporaryFile(suffix=".xml", mode="w+t", delete=False) as tmp_dev:
                dev_xml = """
<hostdev mode='subsystem' type='pci' managed='yes'>
    <driver name='vfio'/>
    <source>
        <address domain='0x{:04x}' bus='0x{:02x}' slot='0x{:02x}' function='0x{:01x}'/>
    </source>
</hostdev>
                """.format(
                    pci_address_obj.domain, pci_address_obj.bus, pci_address_obj.slot, pci_address_obj.function
                )
                LOG.debug("XML device file: %s", dev_xml)
                tmp_dev.write(dev_xml)
                dev_fname = tmp_dev.name

            LOG.info(dry_run_prefix + "Attach PCI device %s to domain %s", pci_address, domain)
            if not dry_run:

                if hotplug and domain_running:
                    cmd = ["attach-device", domain, "--file", dev_fname, "--persistent"]
                else:
                    cmd = ["attach-device", domain, "--file", dev_fname, "--config"]

                self.run_virsh(cmd)

        finally:
            if dev_fname:
                os.remove(dev_fname)

        if domain_running and restart and not hotplug:
            self.restart_domain(domain, dry_run=dry_run)
        return True

    def detach_pci(
        self,
        pci_address: str,
        domain: str,
        hotplug=False,
        restart=False,
        dry_run=False,
    ):
        if restart and hotplug:
            LOG.error("restart and hotplug options cannot be used simultaneously")
        if not pci_address:
            LOG.error("No PCI address is specified")
            return False

        if not domain:
            LOG.error("No domain is specified")
            return False

        dry_run_prefix = "Dry run: " if dry_run else ""

        domain_state = self.get_domain_state(domain)
        domain_running = domain_state == "running"

        pci_address_obj = PCIAddress.parse(pci_address)

        try:
            dev_fname = None
            with tempfile.NamedTemporaryFile(suffix=".xml", mode="w+t", delete=False) as tmp_dev:
                dev_xml = """
<hostdev mode='subsystem' type='pci' managed='yes'>
    <driver name='vfio'/>
    <source>
        <address domain='0x{:04x}' bus='0x{:02x}' slot='0x{:02x}' function='0x{:01x}'/>
    </source>
</hostdev>
                """.format(
                    pci_address_obj.domain, pci_address_obj.bus, pci_address_obj.slot, pci_address_obj.function
                )
                LOG.debug("XML device file: %s", dev_xml)
                tmp_dev.write(dev_xml)
                dev_fname = tmp_dev.name

            LOG.info(dry_run_prefix + "Detach PCI device %s from domain %s", pci_address, domain)
            if not dry_run:

                if hotplug and domain_running:
                    cmd = ["detach-device", domain, "--file", dev_fname, "--persistent"]
                else:
                    cmd = ["detach-device", domain, "--file", dev_fname, "--config"]

                self.run_virsh(cmd)

        finally:
            if dev_fname:
                os.remove(dev_fname)

        if domain_running and restart and not hotplug:
            self.restart_domain(domain, dry_run=dry_run)
        return True

    def print_all_devices(
        self,
        pci_address_filter: PCIAddressFilterCB,
        mdev_type_filter: MdevTypeFilterCB,
        output_format=TEXT_FORMAT,
        output_all_columns=False,
    ):
        global PCI_DEVICES
        assert PCI_DEVICES is not None

        def column_filter(row):
            if output_all_columns:
                return row
            else:
                return row[0], row[1], row[2], row[3], row[4], row[8]

        devices_tbl_header = column_filter(
            (
                "PCI_ADDRESS",
                "DEVICE",
                "DEVICE_DRIVER",
                "MDEV_UUID",
                "MDEV_NAME",
                "MDEV_TYPE",
                "AVAILABLE_INSTANCES",
                "DESCRIPTION",
                "VM_NAME",
            )
        )

        devices_tbl = []

        pci_address_to_domain = defaultdict(set)
        for used_pci_device in self.get_used_pci_devices(pci_address_filter=pci_address_filter, use_cache=True):
            pci_address_to_domain[used_pci_device.pci_device.pci_address].add(used_pci_device.domain)

        mdev_uuid_to_domain = defaultdict(set)
        try:
            for used_mdev_device in self.get_used_mdev_devices(
                pci_address_filter=pci_address_filter, mdev_type_filter=mdev_type_filter, use_cache=True
            ):
                mdev_uuid_to_domain[used_mdev_device.mdev_device.uuid].add(used_mdev_device.domain)
        except FileNotFoundError as e:
            if not e.filename.startswith(MDEV_BUS_DEVICE_PATH):
                raise

        for pci_address, device_path in each_pci_device_address_and_path(
            vendor=NVIDIA_VENDOR, path_waiter=self.wait_for_device_path
        ):
            pci_address_obj = PCIAddress.parse(pci_address)

            if pci_address_filter and not pci_address_filter(pci_address_obj):
                continue

            pci_device = PCI_DEVICES.find_device(pci_address)
            if pci_device is not None:
                domains = pci_address_to_domain[pci_device.pci_address]
                if not domains:
                    domains = set([""])

                for domain in domains:
                    devices_tbl.append(
                        column_filter((pci_address, pci_device.name, pci_device.driver, "", "", "", "", "", domain))
                    )

        try:
            for mdev_device in self.mdev_devices.values():
                pci_address_obj = PCIAddress.parse(mdev_device.pci_address)

                if pci_address_filter and not pci_address_filter(pci_address_obj):
                    continue
                if mdev_type_filter and not mdev_type_filter(mdev_device.mdev_type):
                    continue

                pci_device = PCI_DEVICES.find_device(mdev_device.pci_address)

                if pci_device is not None:
                    domains = mdev_uuid_to_domain[mdev_device.uuid]

                    if not domains:
                        domains = set([mdev_device.nvidia.vm_name or ""])

                    for domain in domains:
                        devices_tbl.append(
                            column_filter(
                                (
                                    mdev_device.pci_address,
                                    pci_device.name,
                                    pci_device.driver,
                                    mdev_device.uuid,
                                    mdev_device.mdev_type.name,
                                    mdev_device.mdev_type.type,
                                    mdev_device.mdev_type.available_instances,
                                    mdev_device.mdev_type.description,
                                    domain,
                                )
                            )
                        )
        except FileNotFoundError as e:
            if not e.filename.startswith(MDEV_BUS_DEVICE_PATH):
                raise

        devices_tbl.sort(key=lambda entry: entry[0])

        devices_tbl = [devices_tbl_header] + devices_tbl

        if output_format == TABLE_FORMAT:
            print_table(devices_tbl)
        else:
            # text format
            print(" ".join([i[0] for i in devices_tbl[1:]]))

        self.validate_configuration(pci_address_filter=pci_address_filter, mdev_type_filter=mdev_type_filter)

        return True

    def validate_configuration(
        self,
        pci_address_filter: PCIAddressFilterCB,
        mdev_type_filter: MdevTypeFilterCB,
    ):
        global PCI_DEVICES
        assert PCI_DEVICES is not None

        result = True

        pci_address_to_domain = defaultdict(set)
        for used_pci_device in self.get_used_pci_devices(pci_address_filter=pci_address_filter, use_cache=True):
            pci_address_to_domain[used_pci_device.pci_device.pci_address].add(used_pci_device.domain)

        mdev_uuid_to_domain = defaultdict(set)
        try:
            for used_mdev_device in self.get_used_mdev_devices(
                pci_address_filter=pci_address_filter, mdev_type_filter=mdev_type_filter, use_cache=True
            ):
                mdev_uuid_to_domain[used_mdev_device.mdev_device.uuid].add(used_mdev_device.domain)
        except FileNotFoundError as e:
            if not e.filename.startswith(MDEV_BUS_DEVICE_PATH):
                raise

        for pci_address, device_path in each_pci_device_address_and_path(
            vendor=NVIDIA_VENDOR, path_waiter=self.wait_for_device_path
        ):
            pci_address_obj = PCIAddress.parse(pci_address)

            if pci_address_filter and not pci_address_filter(pci_address_obj):
                continue

            pci_device = PCI_DEVICES.find_device(pci_address)
            if pci_device is not None:
                domains = pci_address_to_domain[pci_device.pci_address]

                for domain in domains:
                    if domain and pci_device.driver != "vfio-pci":
                        LOG.warning(
                            "GPU %s with PCI address %s is used by VM %s, but has %s driver instead of vfio-pci",
                            pci_device.name,
                            pci_address,
                            domain,
                            pci_device.driver,
                        )
                        result = False

                if len(domains) > 1:
                    LOG.warning(
                        "GPU %s with PCI address %s is used by more than one VMs: %s",
                        pci_device.name,
                        pci_address,
                        ", ".join(domains),
                    )

        try:
            for mdev_device in self.mdev_devices.values():
                pci_address_obj = PCIAddress.parse(mdev_device.pci_address)
                if pci_address_filter and not pci_address_filter(pci_address_obj):
                    continue
                if mdev_type_filter and not mdev_type_filter(mdev_device.mdev_type):
                    continue

                pci_device = PCI_DEVICES.find_device(mdev_device.pci_address)
                if pci_device is not None:
                    domains = mdev_uuid_to_domain[mdev_device.uuid]

                    if not domains and mdev_device.nvidia:
                        domains = set([mdev_device.nvidia.vm_name])

                    for domain in domains:
                        if domain and pci_device.driver != "nvidia":
                            LOG.warning(
                                "GPU %s with PCI address %s has vGPU (MDEV) with UUID %s, but has %s driver instead of "
                                "nvidia",
                                pci_device.name,
                                mdev_device.pci_address,
                                mdev_device.uuid,
                                pci_device.driver,
                            )
                            result = False

                    if len(domains) > 1:
                        LOG.warning(
                            "GPU %s with PCI address %s has vGPU (MDEV) with UUID %s but is used by more than one VMs "
                            "%s",
                            pci_device.name,
                            mdev_device.pci_address,
                            mdev_device.uuid,
                            ", ".join(domains),
                        )
        except FileNotFoundError as e:
            if not e.filename.startswith(MDEV_BUS_DEVICE_PATH):
                raise

        return result


DEV_CTL: Optional[DevCtl] = None


def list_all(args):
    def pci_address_filter(pci_address: PCIAddress) -> bool:
        if not args.pci_addresses:
            return True
        return str(pci_address) in args.pci_addresses

    def mdev_type_filter(mdev_type: MdevType) -> bool:
        if not args.mdev_types:
            return True
        return mdev_type.type in args.mdev_types

    result = DEV_CTL.print_all_devices(
        pci_address_filter=pci_address_filter,
        mdev_type_filter=mdev_type_filter,
        output_format=args.output_format,
        output_all_columns=args.output_all,
    )
    return 0 if result else 1


def list_pci(args):
    def pci_address_filter(pci_address: PCIAddress) -> bool:
        if not args.pci_addresses:
            return True
        return str(pci_address) in args.pci_addresses

    result = DEV_CTL.print_pci_devices(
        pci_address_filter=pci_address_filter, output_format=args.output_format, output_all_columns=args.output_all
    )
    return 0 if result else 1


def list_mdev(args):
    result = False

    def pci_address_filter(pci_address: PCIAddress) -> bool:
        if not args.pci_addresses:
            return True
        return str(pci_address) in args.pci_addresses

    def mdev_type_filter(mdev_type: MdevType) -> bool:
        if not args.mdev_types:
            return True
        return mdev_type.type in args.mdev_types

    try:
        if args.classes or args.all_classes:
            result = DEV_CTL.print_mdev_device_classes(
                pci_address_filter=pci_address_filter,
                mdev_type_filter=mdev_type_filter,
                all_classes=args.all_classes,
                output_all_columns=args.output_all,
            )
        else:
            result = DEV_CTL.print_mdev_devices(
                pci_address_filter=pci_address_filter,
                mdev_type_filter=mdev_type_filter,
                output_all_columns=args.output_all,
            )
    except FileNotFoundError as e:
        if e.filename.startswith(MDEV_BUS_DEVICE_PATH):
            LOG.error("Could not access MDEV devices in sysfs: %s", e.filename)
        elif e.filename.startswith(MDEV_BUS_CLASS_PATH):
            LOG.error("Could not access MDEV device classes in sysfs: %s", e.filename)
        else:
            raise
    return 0 if result else 1


def list_used_pci(args):
    def pci_address_filter(pci_address: PCIAddress) -> bool:
        if not args.pci_addresses:
            return True
        return str(pci_address) in args.pci_addresses

    if DEV_CTL.print_used_pci_devices(pci_address_filter=pci_address_filter, output_format=args.output_format):
        return 0
    else:
        return 1


def list_used_mdev(args):
    def pci_address_filter(pci_address: PCIAddress) -> bool:
        if not args.pci_addresses:
            return True
        return str(pci_address) in args.pci_addresses

    def mdev_type_filter(mdev_type: MdevType) -> bool:
        if not args.mdev_types:
            return True
        return mdev_type.type in args.mdev_types

    if DEV_CTL.print_used_mdev_devices(
        pci_address_filter=pci_address_filter,
        mdev_type_filter=mdev_type_filter,
        output_format=args.output_format,
        output_all_columns=args.output_all,
    ):
        return 0
    else:
        return 1


def save_config(args):
    if DEV_CTL.save_config(output_file=args.output_file):
        return 0
    else:
        return 1


def restore_config(args):
    if DEV_CTL.restore_config(input_file=args.input_file, dry_run=args.dry_run):
        return 0
    else:
        return 1


def bind_driver(args):
    if DEV_CTL.bind_driver(driver=args.driver, devices=args.devices, dry_run=args.dry_run):
        return 0
    else:
        return 1


def unbind_driver(args):
    if DEV_CTL.unbind_driver(
        driver=args.driver,
        devices=args.devices,
        ignore_others=args.ignore_others,
        dry_run=args.dry_run,
    ):
        return 0
    else:
        return 1


def create_mdev(args):
    mdev_uuid = DEV_CTL.create_mdev(
        pci_address=args.pci_address,
        mdev_type_name=args.mdev_type,
        mdev_uuid=args.mdev_uuid,
        dry_run=args.dry_run,
    )
    if mdev_uuid:
        print(mdev_uuid)
        return 0
    else:
        return 1


def remove_mdev(args):
    if DEV_CTL.remove_mdev(mdev_uuid=args.mdev_uuid, dry_run=args.dry_run):
        return 0
    else:
        return 1


def attach_mdev(args):
    if DEV_CTL.attach_mdev(
        mdev_uuid=args.mdev_uuid,
        domain=args.domain,
        hotplug=args.hotplug,
        restart=args.restart,
        dry_run=args.dry_run,
    ):
        return 0
    else:
        return 1


def detach_mdev(args):
    if DEV_CTL.detach_mdev(
        mdev_uuid=args.mdev_uuid,
        domain=args.domain,
        hotplug=args.hotplug,
        restart=args.restart,
        dry_run=args.dry_run,
    ):
        return 0
    else:
        return 1


def attach_pci(args):
    if DEV_CTL.attach_pci(
        pci_address=args.pci_address,
        domain=args.domain,
        hotplug=args.hotplug,
        restart=args.restart,
        dry_run=args.dry_run,
    ):
        return 0
    else:
        return 1


def detach_pci(args):
    if DEV_CTL.detach_pci(
        pci_address=args.pci_address,
        domain=args.domain,
        hotplug=args.hotplug,
        restart=args.restart,
        dry_run=args.dry_run,
    ):
        return 0
    else:
        return 1


def restart_services(args):
    if restart_nvidia_services(dry_run=args.dry_run):
        return 0
    else:
        return 1


def main():
    global DEV_CTL, PCI_DEVICES

    default_virsh_connection = None
    # Smart logic to get default libvirt connection
    user_groups = [grp.getgrgid(g).gr_name for g in os.getgroups()]
    if (
        not os.getenv("LIBVIRT_DEFAULT_URI", None)
        and not os.getenv("VIRSH_DEFAULT_CONNECT_URI", None)
        and ("libvirtd" in user_groups or "libvirt" in user_groups or "kvm" in user_groups)
    ):
        default_virsh_connection = "qemu:///system"

    parser = argparse.ArgumentParser(
        description="NVIDIA Device Control", formatter_class=argparse.ArgumentDefaultsHelpFormatter
    )
    parser.add_argument("--debug", help="debug mode", action="store_true")
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "-l",
        "--log",
        dest="loglevel",
        default="WARNING",
        help="log level (use one of CRITICAL,ERROR,WARNING,INFO,DEBUG)",
    )
    parser.add_argument(
        "-c", "--connection", metavar="URL", help="virsh connection URL", default=default_virsh_connection
    )
    parser.add_argument("-w", "--wait", help="wait until mdev bus is available", action="store_true")
    parser.add_argument(
        "--trials",
        type=int,
        default=3,
        metavar="N",
        help="number of trials if waiting for device",
    )
    parser.add_argument(
        "--delay",
        type=int,
        default=1,
        metavar="SECONDS",
        help="delay time in seconds between trials if waiting for device",
    )

    def register_list_pci_args(argparser):
        argparser.add_argument(
            "-p",
            "--pci-address",
            help="show only devices with specified pci addresses",
            action="append",
            dest="pci_addresses",
        )
        argparser.add_argument(
            "-o",
            "--output",
            type=str,
            help="output format",
            choices=["table", "text"],
            default="table",
            dest="output_format",
        )
        argparser.add_argument("-O", "--output-all", help="output all columns", action="store_true")
        argparser.set_defaults(func=list_pci)

    def register_list_mdev_args(argparser):
        argparser.add_argument("-c", "--classes", help="print mdev device classes", action="store_true")
        argparser.add_argument(
            "-p",
            "--pci-address",
            help="show only devices with specified pci addresses",
            action="append",
            dest="pci_addresses",
        )
        argparser.add_argument(
            "-m",
            "--mdev-type",
            help="show only devices with specified mdev types",
            action="append",
            dest="mdev_types",
        )
        argparser.add_argument("-a", "--all-classes", help="print all mdev device classes", action="store_true")
        argparser.add_argument("-O", "--output-all", help="output all columns", action="store_true")
        argparser.set_defaults(func=list_mdev)

    def register_list_all_args(argparser):
        argparser.add_argument(
            "-p",
            "--pci-address",
            help="show only devices with specified pci addresses",
            action="append",
            dest="pci_addresses",
        )
        argparser.add_argument(
            "-m",
            "--mdev-type",
            help="show only devices with specified mdev types",
            action="append",
            dest="mdev_types",
        )
        argparser.add_argument(
            "-o",
            "--output",
            type=str,
            help="output format",
            choices=["table", "text"],
            default="table",
            dest="output_format",
        )
        argparser.add_argument("-O", "--output-all", help="output all columns", action="store_true")
        argparser.set_defaults(func=list_all)

    parser.set_defaults(subcommand="list-all")
    register_list_all_args(parser)

    subparsers = parser.add_subparsers(title="subcommands", dest="subcommand", metavar="")

    list_all_p = subparsers.add_parser("list-all", help="list all NVIDIA devices")
    register_list_all_args(list_all_p)

    list_pci_p = subparsers.add_parser("list-pci", help="list NVIDIA PCI devices")
    register_list_pci_args(list_pci_p)

    list_mdev_p = subparsers.add_parser("list-mdev", help="list registered mdev devices")
    register_list_mdev_args(list_mdev_p)

    list_used_pci_p = subparsers.add_parser("list-used-pci", help="list used NVIDIA PCI devices")
    list_used_pci_p.add_argument(
        "-p",
        "--pci-address",
        help="show only devices with specified pci addresses",
        action="append",
        dest="pci_addresses",
    )
    list_used_pci_p.add_argument(
        "-o",
        "--output",
        type=str,
        help="output format",
        choices=["table", "text"],
        default="table",
        dest="output_format",
    )
    list_used_pci_p.set_defaults(func=list_used_pci)

    list_used_mdev_p = subparsers.add_parser("list-used-mdev", help="list used mdev devices")
    list_used_mdev_p.add_argument(
        "-p",
        "--pci-address",
        help="show only devices with specified pci addresses",
        action="append",
        dest="pci_addresses",
    )
    list_used_mdev_p.add_argument(
        "-m",
        "--mdev-type",
        help="show only devices with specified mdev types",
        action="append",
        dest="mdev_types",
    )
    list_used_mdev_p.add_argument(
        "-o",
        "--output",
        type=str,
        help="output format",
        choices=["table", "text"],
        default="table",
        dest="output_format",
    )
    list_used_mdev_p.add_argument("-O", "--output-all", help="output all columns", action="store_true")
    list_used_mdev_p.set_defaults(func=list_used_mdev)

    create_mdev_p = subparsers.add_parser("create-mdev", help="create new mdev device")
    create_mdev_p.add_argument(
        "pci_address",
        metavar="PCI_ADDRESS",
        help="PCI address of the NVIDIA device where to create new mdev device",
    )
    create_mdev_p.add_argument("mdev_type", metavar="MDEV_TYPE_OR_NAME", help="mdev device type or type name")
    create_mdev_p.add_argument(
        "-u",
        "--uuid",
        metavar="UUID",
        dest="mdev_uuid",
        help="UUID of the mdev device, if not specified a new will be automatically generated",
    )
    create_mdev_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    create_mdev_p.set_defaults(func=create_mdev)

    remove_mdev_p = subparsers.add_parser("remove-mdev", help="remove mdev device")
    remove_mdev_p.add_argument("mdev_uuid", metavar="UUID", help="UUID of the mdev device to remove")
    remove_mdev_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    remove_mdev_p.set_defaults(func=remove_mdev)

    save_p = subparsers.add_parser("save", help="dump registered mdev devices")
    save_p.add_argument(
        "-o",
        "--output",
        metavar="FILE",
        help="output mdev devices to file",
        type=argparse.FileType("w"),
        default=sys.stdout,
        dest="output_file",
    )
    save_p.set_defaults(func=save_config)

    restore_p = subparsers.add_parser("restore", help="restore registered mdev devices")
    restore_p.add_argument(
        "-i",
        "--input",
        metavar="FILE",
        help="load mdev devices from file",
        type=argparse.FileType("r"),
        default=sys.stdin,
        dest="input_file",
    )
    restore_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    restore_p.set_defaults(func=restore_config)

    bind_driver_p = subparsers.add_parser("bind-driver", help="bind driver to devices")
    bind_driver_p.add_argument("driver", metavar="DRIVER", help="bind driver to devices")
    bind_driver_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    bind_driver_p.add_argument("devices", metavar="PCI_ADDRESS", type=str, nargs="+", help="device PCI address")
    bind_driver_p.set_defaults(func=bind_driver)

    unbind_driver_p = subparsers.add_parser("unbind-driver", help="unbind drivers from devices")
    unbind_driver_p.add_argument(
        "-d",
        "--driver",
        metavar="DRIVER",
        help="unbind driver from devices (if not specified unbind any bound driver)",
    )
    unbind_driver_p.add_argument(
        "-i",
        "--ignore-others",
        help="unbind only specified driver and ignore others",
        action="store_true",
    )
    unbind_driver_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    unbind_driver_p.add_argument("devices", metavar="PCI_ADDRESS", type=str, nargs="+", help="device PCI address")
    unbind_driver_p.set_defaults(func=unbind_driver)

    restart_services_p = subparsers.add_parser("restart-services", help="restart NVIDIA services")
    restart_services_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    restart_services_p.set_defaults(func=restart_services)

    attach_mdev_p = subparsers.add_parser("attach-mdev", help="attach mdev device to virsh domain (virtual machine)")
    attach_mdev_p.add_argument("mdev_uuid", metavar="UUID", help="UUID of the mdev device to remove")
    attach_mdev_p.add_argument("domain", metavar="DOMAIN", help="domain name, id or uuid")
    attach_mdev_p.add_argument(
        "--hotplug", help="affect the running domain and keep changes after reboot", action="store_true"
    )
    attach_mdev_p.add_argument(
        "--restart", help="shutdown and reboot the domain after the changes are made", action="store_true"
    )
    attach_mdev_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    attach_mdev_p.set_defaults(func=attach_mdev)

    detach_mdev_p = subparsers.add_parser("detach-mdev", help="detach mdev device from virsh domain (virtual machine)")
    detach_mdev_p.add_argument("mdev_uuid", metavar="UUID", help="UUID of the mdev device to remove")
    detach_mdev_p.add_argument("domain", metavar="DOMAIN", help="domain name, id or uuid")
    detach_mdev_p.add_argument(
        "--hotplug", help="affect the running domain and keep changes after reboot", action="store_true"
    )
    detach_mdev_p.add_argument(
        "--restart", help="shutdown and reboot the domain after the changes are made", action="store_true"
    )
    detach_mdev_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    detach_mdev_p.set_defaults(func=detach_mdev)

    attach_pci_p = subparsers.add_parser("attach-pci", help="attach pci device to virsh domain (virtual machine)")
    attach_pci_p.add_argument("pci_address", metavar="PCI_ADDRESS", help="PCI address of the NVIDIA device to attach")
    attach_pci_p.add_argument("domain", metavar="DOMAIN", help="domain name, id or uuid")
    attach_pci_p.add_argument(
        "--hotplug", help="affect the running domain and keep changes after reboot", action="store_true"
    )
    attach_pci_p.add_argument(
        "--restart", help="shutdown and reboot the domain after the changes are made", action="store_true"
    )
    attach_pci_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    attach_pci_p.set_defaults(func=attach_pci)

    detach_pci_p = subparsers.add_parser("detach-pci", help="detach pci device from virsh domain (virtual machine)")
    detach_pci_p.add_argument("pci_address", metavar="PCI_ADDRESS", help="PCI address of the NVIDIA device to attach")
    detach_pci_p.add_argument("domain", metavar="DOMAIN", help="domain name, id or uuid")
    detach_pci_p.add_argument(
        "--hotplug", help="affect the running domain and keep changes after reboot", action="store_true"
    )
    detach_pci_p.add_argument(
        "--restart", help="shutdown and reboot the domain after the changes are made", action="store_true"
    )
    detach_pci_p.add_argument(
        "-n",
        "--dry-run",
        help="Do everything except actually make changes",
        action="store_true",
    )
    detach_pci_p.set_defaults(func=detach_pci)

    args = parser.parse_args()

    numeric_level = getattr(logging, args.loglevel.upper(), None)
    if not isinstance(numeric_level, int):
        print(
            "Invalid log level: {}, use one of CRITICAL, ERROR, WARNING, INFO, DEBUG".format(args.loglevel),
            file=sys.stderr,
        )
        return 1

    debug_mode = numeric_level == logging.DEBUG

    if debug_mode:
        logging.basicConfig(
            format="%(asctime)s %(levelname)s %(pathname)s:%(lineno)s: %(message)s",
            level=numeric_level,
        )
    else:
        logging.basicConfig(format="%(asctime)s %(levelname)s %(message)s", level=numeric_level)

    if default_virsh_connection:
        LOG.info("Selected default URI for the virsh connection: %s", default_virsh_connection)
    if args.connection != default_virsh_connection:
        LOG.info("The user set the URI of the virsh connection to: %s", args.connection)

    args = parser.parse_args()

    PCI_DEVICES = PCIDevices()

    try:
        DEV_CTL = DevCtl(
            wait_for_device=args.wait,
            num_trials=args.trials,
            wait_delay=args.delay,
            virsh_connection=args.connection,
            debug=debug_mode,
        )
    except DevCtlException:
        logging.exception("Cloud not create DevCtl")
        return 1

    try:
        result = args.func(args)
    except DevCtlException:
        LOG.exception("Could not execute %s command", args.subcommand)
        return 1
    except PermissionError:
        LOG.exception(
            "Could not execute %s command, try to run this command as root",
            args.subcommand,
        )
        return 1

    if result is None:
        result = 0
    return result


if __name__ == "__main__":
    sys.exit(main())
