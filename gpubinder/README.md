# nvidia-dev-ctl.py

Control tool for NVIDIA GPU and vGPU devices on GNU/Linux OS.

## Introduction

The `nvidia-dev-ctl.py` tool is designed to simplify the runtime configuration of NVIDIA virtual and physical GPU devices along with KVM-only virtualization (i.e. through the virsh command.) on GNU/Linux OS. It is based on the official [NVIDIA vGPU user manual](https://docs.nvidia.com/grid/latest/grid-vgpu-user-guide/index.html) and attempts to reduce many of the manual steps described in the documentation, like [creating an NVIDIA vGPU on Red Hat Enterprise Linux KVM](https://docs.nvidia.com/grid/latest/grid-vgpu-user-guide/index.html#creating-vgpu-device-red-hat-el-kvm) using multiple shell commands.

The following operations are provided by the tool via subcommands:

  * [list-pci](#list-pci-command) - list all NVIDIA PCI devices detected by the NVIDIA driver
  * [list-mdev](#list-mdev-command) - list all registered NVIDIA mediated devices (mdev) i.e. virtual GPUs (vGPUs)
  * [list-used-pci](#list-used-pci-command) - list all NVIDIA PCI devices used by virtual machines
  * [list-used-mdev](#list-used-mdev-command) - list all NVIDIA vGPUs (mdevs) used by virtual machines
  * [create-mdev](#create-mdev-command) - create new NVIDIA mdev device
  * [remove-mdev](#remove-mdev-command) - remove existing NVIDIA mdev device
  * [save](#save-command) - dump current NVIDIA device configuration for later loading using the restore` command.
  * [restore](#restore-command) - restore the NVIDIA device configuration dumped with the 'save' command.
  * [bind-driver](#bind-driver-command) - bind driver to devices
  * [unbind-driver](#unbind-driver-command) - unbind drivers from devices
  * [restart-services](#restart-services-command) - restart NVIDIA services
  * [attach-mdev](#attach-mdev-command) - attach NVIDIA mdev device to virsh domain (virtual machine)
  * [detach-mdev](#detach-mdev-command) - detach NVIDIA mdev device from virsh domain (virtual machine)
  * [attach-pci](#attach-pci-command) - attach NVIDIA GPU device to virsh domain (virtual machine)
  * [detach-pci](#detach-pci-command) - detach NVIDIA GPU device from virsh domain (virtual machine)

Running `nvidia-dev-ctl.py` with the `--help` option will output the help information below. Details about subcommands can be output by using the `--help` option with the appropriate subcommand, e.g. `nvidia-dev-ctl.py save --help`:

```
usage: nvidia-dev-ctl.py [-h] [-l LOGLEVEL] [-c URL] [-w] [--trials N]
                         [--delay SECONDS] [-p PCI_ADDRESSES]
                         [-o {table,text}] [-O]
                         ...

optional arguments:
  -h, --help            show this help message and exit
  -l LOGLEVEL, --log LOGLEVEL
                        log level (use one of
                        CRITICAL,ERROR,WARNING,INFO,DEBUG)
  -c URL, --connection URL
                        virsh connection URL
  -w, --wait            wait until mdev bus is available
  --trials N            number of trials if waiting for device
  --delay SECONDS       delay time in seconds between trials if waiting for
                        device
  -p PCI_ADDRESSES, --pci-address PCI_ADDRESSES
                        show only devices with specified pci addresses
  -o {table,text}, --output {table,text}
                        output format
  -O, --output-all      output all columns

subcommands:

    list-pci            list NVIDIA PCI devices
    list-mdev           list registered mdev devices
    list-used-pci       list used NVIDIA PCI devices
    list-used-mdev      list used mdev devices
    create-mdev         create new mdev device
    remove-mdev         remove mdev device
    save                dump registered mdev devices
    restore             restore registered mdev devices
    bind-driver         bind driver to devices
    unbind-driver       unbind drivers from devices
    restart-services    restart NVIDIA services
    attach-mdev         attach mdev device to virsh domain (virtual machine)
    detach-mdev         detach mdev device from virsh domain (virtual machine)
    attach-pci          attach pci device to virsh domain (virtual machine)
    detach-pci          detach pci device from virsh domain (virtual machine)
```

## list-pci command

`list-pci` outputs currently available NVIDIA devices on the PCI bus. The `-o` or `--output` option controls the mode of output. In the default table mode, the PCI device address, the name of the device driver loaded and the [sysfs](https://man7.org/linux/man-pages/man5/sysfs.5.html) device path are output:

```
$ nvidia-dev-ctl.py list-pci -o table
PCI_ADDRESS  DEVICE_DRIVER PCI_DEVICE_PATH
0000:43:00.0 nvidia        /sys/bus/pci/devices/0000:43:00.0
0000:44:00.0 nvidia        /sys/bus/pci/devices/0000:44:00.0
0000:45:00.0 nvidia        /sys/bus/pci/devices/0000:45:00.0
0000:46:00.0 nvidia        /sys/bus/pci/devices/0000:46:00.0
0000:47:00.0 nvidia        /sys/bus/pci/devices/0000:47:00.0
0000:83:00.0 vfio-pci      /sys/bus/pci/devices/0000:83:00.0
0000:84:00.0 vfio-pci      /sys/bus/pci/devices/0000:84:00.0
0000:85:00.0 vfio-pci      /sys/bus/pci/devices/0000:85:00.0
0000:86:00.0 vfio-pci      /sys/bus/pci/devices/0000:86:00.0
0000:87:00.0 vfio-pci      /sys/bus/pci/devices/0000:87:00.0
```

The output can be restricted to the PCI addresses specified by the `-p` or `--pci-address` option:

```
$ nvidia-dev-ctl.py list-pci -o table -p 0000:43:00.0 -p 0000:83:00.0
PCI_ADDRESS  DEVICE_DRIVER PCI_DEVICE_PATH
0000:43:00.0 nvidia        /sys/bus/pci/devices/0000:43:00.0
0000:83:00.0 vfio-pci      /sys/bus/pci/devices/0000:83:00.0
```

In text mode only PCI addresses are output:

```
$ nvidia-dev-ctl.py list-pci -o text
0000:43:00.0 0000:44:00.0 0000:45:00.0 0000:46:00.0 0000:47:00.0 0000:83:00.0 0000:84:00.0 0000:85:00.0 0000:86:00.0 0000:87:00.0
```

All `list-pci` command options:

```
usage: nvidia-dev-ctl.py list-pci [-h] [-p PCI_ADDRESSES] [-o {table,text}]

optional arguments:
  -h, --help            show this help message and exit
  -p PCI_ADDRESSES, --pci-address PCI_ADDRESSES
                        show only devices with specified pci addresses
  -o {table,text}, --output {table,text}
                        output format
```

## list-mdev command

`list-mdev` outputs currently registered NVIDIA vGPU devices. By default, the device UUID, PCI device address, device type, device type name and virtual machine name, if vGPU is bound to the running virtual machine, are output:

```
$ nvidia-dev-ctl.py list-mdev
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME           VM_NAME
45217cc2-a076-4d40-835f-a740d8d905df 0000:46:00.0 nvidia-313 GRID V100D-16C test-vm-5-node-01
5569c457-a0eb-448a-93b4-a15c5322243f 0000:47:00.0 nvidia-312 GRID V100D-8C
80129abf-2c41-4fa7-8280-84866112f31c 0000:43:00.0 nvidia-313 GRID V100D-16C test-vm-1-node-01
882f2c44-0f5c-4ed9-8bce-2a4a523519c4 0000:46:00.0 nvidia-313 GRID V100D-16C test-vm-6-node-01
f9a58b28-009c-447d-b18f-29979e7ff857 0000:43:00.0 nvidia-313 GRID V100D-16C test-vm-4-node-01
```

The output can be limited to the PCI-addresses specified by the `-p` or `--pci-address` option in the same way as in the `list-pci` command. Use `-m` or `--mdev-type` to restrict output to devices of the specified vGPU type:

```
$ nvidia-dev-ctl.py list-mdev --mdev-type nvidia-312
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME          VM_NAME
5569c457-a0eb-448a-93b4-a15c5322243f 0000:47:00.0 nvidia-312 GRID V100D-8C
```

If the `-O` or `--output-all` option is specified, the number of vGPU instances still available and the vGPU device description are also output.

```
$ nvidia-dev-ctl.py list-mdev --output-all
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME           AVAILABLE_INSTANCES DESCRIPTION                                                                              VM_NAME
45217cc2-a076-4d40-835f-a740d8d905df 0000:46:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 test-vm-5-node-01
80129abf-2c41-4fa7-8280-84866112f31c 0000:43:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 test-vm-1-node-01
882f2c44-0f5c-4ed9-8bce-2a4a523519c4 0000:46:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 test-vm-6-node-01
f9a58b28-009c-447d-b18f-29979e7ff857 0000:43:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 test-vm-4-node-01
```

If the `-c` or `--classes` option is specified, a list of supported NVIDIA vGPU device classes is output for each PCI device. Only the device types output from the list can be created on the current hardware:

```
$ nvidia-dev-ctl.py list-mdev --classes -p 0000:47:00.0
PCI_ADDRESS  MDEV_TYPE  NAME           AVAILABLE_INSTANCES
0000:47:00.0 nvidia-180 GRID V100D-1Q  32
0000:47:00.0 nvidia-181 GRID V100D-2Q  16
0000:47:00.0 nvidia-182 GRID V100D-4Q  8
0000:47:00.0 nvidia-183 GRID V100D-8Q  4
0000:47:00.0 nvidia-184 GRID V100D-16Q 2
0000:47:00.0 nvidia-185 GRID V100D-32Q 1
0000:47:00.0 nvidia-186 GRID V100D-1A  32
0000:47:00.0 nvidia-187 GRID V100D-2A  16
0000:47:00.0 nvidia-188 GRID V100D-4A  8
0000:47:00.0 nvidia-189 GRID V100D-8A  4
0000:47:00.0 nvidia-190 GRID V100D-16A 2
0000:47:00.0 nvidia-191 GRID V100D-32A 1
0000:47:00.0 nvidia-192 GRID V100D-1B  32
0000:47:00.0 nvidia-193 GRID V100D-2B  16
0000:47:00.0 nvidia-218 GRID V100D-2B4 16
0000:47:00.0 nvidia-249 GRID V100D-1B4 32
0000:47:00.0 nvidia-311 GRID V100D-4C  8
0000:47:00.0 nvidia-312 GRID V100D-8C  4
0000:47:00.0 nvidia-313 GRID V100D-16C 2
0000:47:00.0 nvidia-314 GRID V100D-32C 1
```

Also in this case the option `-O` or `--output-all` will output more detailed information.

All `list-mdev` command options:

```
usage: nvidia-dev-ctl.py list-mdev [-h] [-c] [-p PCI_ADDRESSES]
                                   [-m MDEV_TYPES] [-O]

optional arguments:
  -h, --help            show this help message and exit
  -c, --classes         print mdev device classes
  -p PCI_ADDRESSES, --pci-address PCI_ADDRESSES
                        show only devices with specified pci addresses
  -m MDEV_TYPES, --mdev-type MDEV_TYPES
                        show only devices with specified mdev types
  -O, --output-all      output all columns
```

## list-used-pci command

`list-used-pci` outputs NVIDIA devices on the PCI bus that are used by libvirt virtual machines (domains). The `-o` or `--output` option controls the mode of output. In the default table mode, the PCI device address and the name of the virtual machine are output:

```
$ nvidia-dev-ctl.py list-used-pci -o table
PCI_ADDRESS  VM_NAME
0000:43:00.0 test-vm-1-node-01
0000:86:00.0 test-vm-3-node-01
```

The output can be restricted to the PCI addresses specified by the `-p` or `--pci-address` option:

```
$ nvidia-dev-ctl.py list-used-pci -o table -p 0000:43:00.0
PCI_ADDRESS  VM_NAME
0000:43:00.0 test-vm-1-node-01
```

In text mode only PCI addresses are output:

```
$ nvidia-dev-ctl.py list-used-pci -o text
0000:43:00.0 0000:86:00.0
```

All `list-used-pci` command options:

```
usage: nvidia-dev-ctl.py list-used-pci [-h] [-p PCI_ADDRESSES]
                                       [-o {table,text}]

optional arguments:
  -h, --help            show this help message and exit
  -p PCI_ADDRESSES, --pci-address PCI_ADDRESSES
                        show only devices with specified pci addresses
  -o {table,text}, --output {table,text}
                        output format
```

## list-used-mdev command

`list-used-mdev` outputs NVIDIA vGPU devices that are used by libvirt virtual machines (domains). By default, the device UUID, PCI device address, device type, device type name and virtual machine name are output:

```
$ nvidia-dev-ctl.py -c qemu:///system list-used-mdev
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME           VM_NAME
80129abf-2c41-4fa7-8280-84866112f31c 0000:43:00.0 nvidia-313 GRID V100D-16C foresight-vm-1-node-01
45217cc2-a076-4d40-835f-a740d8d905df 0000:46:00.0 nvidia-313 GRID V100D-16C foresight-vm-5-node-01
f9a58b28-009c-447d-b18f-29979e7ff857 0000:43:00.0 nvidia-313 GRID V100D-16C foresight-vm-4-node-01
882f2c44-0f5c-4ed9-8bce-2a4a523519c4 0000:46:00.0 nvidia-313 GRID V100D-16C foresight-vm-6-node-01
eb4fe85a-05d0-4f80-97e9-c099388387ba 0000:47:00.0 nvidia-312 GRID V100D-8C  foresight-vm-6-node-01
```

The output can be limited to the PCI-addresses specified by the `-p` or `--pci-address` option in the same way as in the `list-used-pci` command. Use `-m` or `--mdev-type` to restrict output to devices of the specified vGPU type:

```
$ nvidia-dev-ctl.py -c qemu:///system list-used-mdev -m nvidia-312
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME          VM_NAME
eb4fe85a-05d0-4f80-97e9-c099388387ba 0000:47:00.0 nvidia-312 GRID V100D-8C foresight-vm-6-node-01
```

If the `-O` or `--output-all` option is specified, the number of vGPU instances still available and the vGPU device description are also output.

```
$ nvidia-dev-ctl.py -c qemu:///system list-used-mdev --output-all
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME           AVAILABLE_INSTANCES DESCRIPTION                                                                              VM_NAME
80129abf-2c41-4fa7-8280-84866112f31c 0000:43:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 foresight-vm-1-node-01
45217cc2-a076-4d40-835f-a740d8d905df 0000:46:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 foresight-vm-5-node-01
f9a58b28-009c-447d-b18f-29979e7ff857 0000:43:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 foresight-vm-4-node-01
882f2c44-0f5c-4ed9-8bce-2a4a523519c4 0000:46:00.0 nvidia-313 GRID V100D-16C 0                   num_heads=1, frl_config=60, framebuffer=16384M, max_resolution=4096x2160, max_instance=2 foresight-vm-6-node-01
eb4fe85a-05d0-4f80-97e9-c099388387ba 0000:47:00.0 nvidia-312 GRID V100D-8C  3                   num_heads=1, frl_config=60, framebuffer=8192M, max_resolution=4096x2160, max_instance=4  foresight-vm-6-node-01
```

All `list-used-mdev` command options:

```
usage: nvidia-dev-ctl.py list-used-mdev [-h] [-p PCI_ADDRESSES]
                                        [-m MDEV_TYPES] [-o {table,text}] [-O]

optional arguments:
  -h, --help            show this help message and exit
  -p PCI_ADDRESSES, --pci-address PCI_ADDRESSES
                        show only devices with specified pci addresses
  -m MDEV_TYPES, --mdev-type MDEV_TYPES
                        show only devices with specified mdev types
  -o {table,text}, --output {table,text}
                        output format
  -O, --output-all      output all columns
```

## create-mdev command

The `create-mdev` command is used to create a new vGPU by specifying the PCI address and MDEV type and optionally the UUID of the new vGPU. If no UUID is specified, it will be generated automatically. The UUID is printed if the operation was successful. In the following example we create a vGPU of type `nvidia-312` (named `GRID V100D-8C`) on PCI device `0000:47:00` :

```
$ sudo nvidia-dev-ctl.py -l INFO create-mdev 0000:47:00.0 nvidia-312
2020-10-14 19:15:10,396 INFO Do not change the driver on the device 0000:47:00.0, because it already has driver nvidia
2020-10-14 19:15:10,408 INFO Create Mdev device with UUID 5569c457-a0eb-448a-93b4-a15c5322243f and type nvidia-312 on PCI device with address 0000:47:00.0
2020-10-14 19:15:10,409 INFO Found device class <MdevDeviceClass pci_address='0000:47:00.0' path='/sys/class/mdev_bus/0000:47:00.0' supported_mdev_types=['nvidia-180', 'nvidia-181', 'nvidia-182', 'nvidia-183', 'nvidia-184', 'nvidia-185', 'nvidia-186', 'nvidia-187', 'nvidia-188', 'nvidia-189', 'nvidia-190', 'nvidia-191', 'nvidia-192', 'nvidia-193', 'nvidia-218', 'nvidia-249', 'nvidia-311', 'nvidia-312', 'nvidia-313', 'nvidia-314']>
2020-10-14 19:15:10,414 INFO Found device type <MdevType path='/sys/class/mdev_bus/0000:47:00.0/mdev_supported_types/nvidia-312' realpath='/sys/devices/pci0000:40/0000:40:01.1/0000:41:00.0/0000:42:14.0/0000:47:00.0/mdev_supported_types/nvidia-312' type='nvidia-312' name='GRID V100D-8C' description='num_heads=1, frl_config=60, framebuffer=8192M, max_resolution=4096x2160, max_instance=4' device_api='vfio-pci' available_instances=4>
2020-10-14 19:15:10,414 INFO Create mdev device with UUID 5569c457-a0eb-448a-93b4-a15c5322243f on PCI address 0000:47:00.0 and with type nvidia-312
5569c457-a0eb-448a-93b4-a15c5322243f
```

All log information is output to stderr and UUID to stdout. After successful execution of this command we should see a new mdev device using the `list-mdev` command:

```
$ nvidia-dev-ctl.py list-mdev -p 0000:47:00.0
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME          VM_NAME
5569c457-a0eb-448a-93b4-a15c5322243f 0000:47:00.0 nvidia-312 GRID V100D-8C
```

With the `-n` or `--dry` option, the command does not make any changes.

All `create-mdev` command options:

```
usage: nvidia-dev-ctl.py create-mdev [-h] [-u UUID] [-n]
                                     PCI_ADDRESS MDEV_TYPE_OR_NAME

positional arguments:
  PCI_ADDRESS           PCI address of the NVIDIA device where to create new
                        mdev device
  MDEV_TYPE_OR_NAME     mdev device type or type name

optional arguments:
  -h, --help            show this help message and exit
  -u UUID, --uuid UUID  UUID of the mdev device, if not specified a new will
                        be automatically generated
  -n, --dry-run         Do everything except actually make changes
```

## remove-mdev command

The `remove-mdev` command does the opposite of the `create-mdev` command by deleting the mdev device specified with its UUID:

```
$ sudo nvidia-dev-ctl.py -l INFO remove-mdev  5569c457-a0eb-448a-93b4-a15c5322243f
2020-10-14 22:02:54,093 INFO Remove mdev device with UUID 5569c457-a0eb-448a-93b4-a15c5322243f on PCI address 0000:47:00.0 and with type nvidia-312
```

All `remove-mdev` command options:

```
usage: nvidia-dev-ctl.py remove-mdev [-h] [-n] UUID

positional arguments:
  UUID           UUID of the mdev device to remove

optional arguments:
  -h, --help     show this help message and exit
  -n, --dry-run  Do everything except actually make changes
```

## save command

The `save` command outputs the current configuration of NVIDIA devices, including drivers bound to the device and registered vGPU devices, in text format that can be restored with the `restore` command. This makes it easy to restore the device configuration after a reboot, for example.

Suppose we have the following configuration, which is displayed with the commands `list-pci` and `list-mdev`:

```
$ nvidia-dev-ctl.py list-pci
PCI_ADDRESS  DEVICE_DRIVER PCI_DEVICE_PATH
0000:43:00.0 nvidia        /sys/bus/pci/devices/0000:43:00.0
0000:44:00.0 nvidia        /sys/bus/pci/devices/0000:44:00.0
0000:45:00.0 nvidia        /sys/bus/pci/devices/0000:45:00.0
0000:46:00.0 nvidia        /sys/bus/pci/devices/0000:46:00.0
0000:47:00.0 nvidia        /sys/bus/pci/devices/0000:47:00.0
0000:83:00.0 vfio-pci      /sys/bus/pci/devices/0000:83:00.0
0000:84:00.0 vfio-pci      /sys/bus/pci/devices/0000:84:00.0
0000:85:00.0 vfio-pci      /sys/bus/pci/devices/0000:85:00.0
0000:86:00.0 vfio-pci      /sys/bus/pci/devices/0000:86:00.0
0000:87:00.0 vfio-pci      /sys/bus/pci/devices/0000:87:00.0
$ nvidia-dev-ctl.py list-mdev
MDEV_DEVICE_UUID                     PCI_ADDRESS  TYPE       NAME           VM_NAME
45217cc2-a076-4d40-835f-a740d8d905df 0000:46:00.0 nvidia-313 GRID V100D-16C test-vm-5-node-01
80129abf-2c41-4fa7-8280-84866112f31c 0000:43:00.0 nvidia-313 GRID V100D-16C test-vm-1-node-01
882f2c44-0f5c-4ed9-8bce-2a4a523519c4 0000:46:00.0 nvidia-313 GRID V100D-16C test-vm-6-node-01
eb4fe85a-05d0-4f80-97e9-c099388387ba 0000:47:00.0 nvidia-312 GRID V100D-8C
f9a58b28-009c-447d-b18f-29979e7ff857 0000:43:00.0 nvidia-313 GRID V100D-16C test-vm-4-node-01
```

In this case the `save` command will produce the following output:

```
$ nvidia-dev-ctl.py save
# NVIDIA Device Configuration
# This file is auto-generated by nvidia-dev-ctl.py save command
# pci_address	driver_name	mdev_uuid	mdev_type
0000:43:00.0	nvidia	80129abf-2c41-4fa7-8280-84866112f31c	nvidia-313
0000:43:00.0	nvidia	f9a58b28-009c-447d-b18f-29979e7ff857	nvidia-313
0000:44:00.0	nvidia
0000:45:00.0	nvidia
0000:46:00.0	nvidia	45217cc2-a076-4d40-835f-a740d8d905df	nvidia-313
0000:46:00.0	nvidia	882f2c44-0f5c-4ed9-8bce-2a4a523519c4	nvidia-313
0000:47:00.0	nvidia	eb4fe85a-05d0-4f80-97e9-c099388387ba	nvidia-312
0000:83:00.0	vfio-pci
0000:84:00.0	vfio-pci
0000:85:00.0	vfio-pci
0000:86:00.0	vfio-pci
0000:87:00.0	vfio-pci
```

All `save` command options:

```
usage: nvidia-dev-ctl.py save [-h] [-o FILE]

optional arguments:
  -h, --help            show this help message and exit
  -o FILE, --output FILE
                        output mdev devices to file
```

## restore command

The `restore` command restores the configuration saved by the `save` command. Please note that `restore` only changes the state described in the configuration file. All devices that are not listed in the configuration file remain unchanged.

Save and restore configuration:

```
$ nvidia-dev-ctl.py save > nvidia-devices.conf
$ nvidia-dev-ctl.py restore < nvidia-devices.conf
```

Alternatively:

```
$ nvidia-dev-ctl.py save -o nvidia-devices.conf
$ nvidia-dev-ctl.py restore -i nvidia-devices.conf
```

All `restore` command options:

```
usage: nvidia-dev-ctl.py restore [-h] [-i FILE] [-n]

optional arguments:
  -h, --help            show this help message and exit
  -i FILE, --input FILE
                        load mdev devices from file
  -n, --dry-run         Do everything except actually make changes
```

## bind-driver command

The `bind-driver` command allows you to bind drivers to one or multiple NVIDIA devices when no driver is loaded. This is useful if you want to switch the GPU from vGPU to PCI pass-through mode or vice versa by replacing the `nvidia` driver with the `vfio-pci` driver. If a driver is bound to the device, `bind-driver` will first unbind the driver. The command requires the name of the driver to be bound and one or more PCI addresses of the devices to which it should be bound:

```
$ nvidia-dev-ctl.py list-pci -p 0000:83:00.0
PCI_ADDRESS  DEVICE_DRIVER PCI_DEVICE_PATH
0000:83:00.0 vfio-pci      /sys/bus/pci/devices/0000:83:00.0

$ sudo nvidia-dev-ctl.py -l INFO bind-driver nvidia 0000:83:00.0
2020-10-15 09:28:27,063 INFO Unbind driver vfio-pci from PCI device 0000:83:00.0
2020-10-15 09:28:27,064 INFO Override driver nvidia for PCI device 0000:83:00.0
2020-10-15 09:28:27,064 INFO Bind driver nvidia to device 0000:83:00.0

$ nvidia-dev-ctl.py list-pci -p 0000:83:00.0
PCI_ADDRESS  DEVICE_DRIVER PCI_DEVICE_PATH
0000:83:00.0 nvidia        /sys/bus/pci/devices/0000:83:00.0
```

All `bind-driver` command options:

```
usage: nvidia-dev-ctl.py bind-driver [-h] [-n]
                                     DRIVER PCI_ADDRESS [PCI_ADDRESS ...]

positional arguments:
  DRIVER         bind driver to devices
  PCI_ADDRESS    device PCI address

optional arguments:
  -h, --help     show this help message and exit
  -n, --dry-run  Do everything except actually make changes
```

## unbind-driver command

The `unbind-driver` command does the opposite to the `bind-driver` by unbinding the driver from the device.
Without specifying the name of the driver with the `-d` or `--driver` option, the command will unbind any driver bound to the device from the specified devices:

```
$ sudo nvidia-dev-ctl.py -l INFO unbind-driver 0000:83:00.0
2020-10-15 09:35:39,374 INFO Unbind driver vfio-pci from PCI device 0000:83:00.0

$ nvidia-dev-ctl.py list-pci -p 0000:83:00.0
PCI_ADDRESS  DEVICE_DRIVER PCI_DEVICE_PATH
0000:83:00.0 no driver     /sys/bus/pci/devices/0000:83:00.0
```


All `unbind-driver` command options:

```
usage: nvidia-dev-ctl.py unbind-driver [-h] [-d DRIVER] [-i] [-n]
                                       PCI_ADDRESS [PCI_ADDRESS ...]

positional arguments:
  PCI_ADDRESS           device PCI address

optional arguments:
  -h, --help            show this help message and exit
  -d DRIVER, --driver DRIVER
                        unbind driver from devices (if not specified unbind
                        any bound driver)
  -i, --ignore-others   unbind only specified driver and ignore others
  -n, --dry-run         Do everything except actually make changes
```

## restart-services command

The `restart-services` command will restart `nvidia-vgpud` and `nvidia-vgpu-mgr` NVIDIA vGPU services.

All `restart-services` command options:

```
usage: nvidia-dev-ctl.py restart-services [-h] [-n]

optional arguments:
  -h, --help     show this help message and exit
  -n, --dry-run  Do everything except actually make changes
```

## attach-mdev command

The `attach-mdev` command attaches the vGPU device specified by UUID to the libvirt domain (i.e., the virtual machine).
By default, the change is only applied to the configuration and only takes effect after a restart.
If the `--restart` option is specified, the domain is restarted after the change.
If the `--hotplug` option is specified, the device is immediately available within the domain without restart.
The `--restart` and `--hotplug` options cannot be used together. To display actions to be performed without actually making changes,
use the `--n` or `--run-dry` option.

All `attach-mdev` command options:

```
usage: nvidia-dev-ctl.py attach-mdev [-h] [--virsh-trials N]
                                     [--virsh-delay SECONDS] [-c URL]
                                     [--hotplug] [--restart] [-n]
                                     UUID DOMAIN

positional arguments:
  UUID                  UUID of the mdev device to remove
  DOMAIN                domain name, id or uuid

optional arguments:
  -h, --help            show this help message and exit
  --virsh-trials N      number of trials if waiting for virsh
  --virsh-delay SECONDS
                        delay time in seconds between trials if waiting for
                        virsh
  -c URL, --connection URL
                        virsh connection URL
  --hotplug             affect the running domain and keep changes after
                        reboot
  --restart             shutdown and reboot the domain after the changes are
                        made
  -n, --dry-run         Do everything except actually make changes
```

## detach-mdev command

The `detach-mdev` command does the opposite of the `attach-mdev` command by detaching the vGPU device specified with
its UUID from the domain (i.e., the virtual machine).
By default, the change is only applied to the configuration and only takes effect after a restart.
If the `--restart` option is specified, the domain is restarted after the change.
If the `--hotplug` option is specified, the device is immediately available within the domain without restart.
The `--restart` and `--hotplug` options cannot be used together. To display actions to be performed without actually making changes,
use the `--n` or `--run-dry` option.

All `detach-mdev` command options:

```
usage: nvidia-dev-ctl.py detach-mdev [-h] [--virsh-trials N]
                                     [--virsh-delay SECONDS] [-c URL]
                                     [--hotplug] [--restart] [-n]
                                     UUID DOMAIN

positional arguments:
  UUID                  UUID of the mdev device to remove
  DOMAIN                domain name, id or uuid

optional arguments:
  -h, --help            show this help message and exit
  --virsh-trials N      number of trials if waiting for virsh
  --virsh-delay SECONDS
                        delay time in seconds between trials if waiting for
                        virsh
  -c URL, --connection URL
                        virsh connection URL
  --hotplug             affect the running domain and keep changes after
                        reboot
  --restart             shutdown and reboot the domain after the changes are
                        made
  -n, --dry-run         Do everything except actually make changes
```

## attach-pci command

The `attach-pci` command attaches the PCI device specified by its address to the libvirt domain (i.e., the virtual machine).
By default, the change is only applied to the configuration and only takes effect after a restart.
If the `--restart` option is specified, the domain is restarted after the change.
If the `--hotplug` option is specified, the device is immediately available within the domain without restart.
The `--restart` and `--hotplug` options cannot be used together. To display actions to be performed without actually making changes,
use the `--n` or `--run-dry` option.

All `attach-pci` command options:

```
usage: nvidia-dev-ctl.py attach-pci [-h] [--virsh-trials N]
                                    [--virsh-delay SECONDS] [-c URL]
                                    [--hotplug] [--restart] [-n]
                                    PCI_ADDRESS DOMAIN

positional arguments:
  PCI_ADDRESS           PCI address of the NVIDIA device to attach
  DOMAIN                domain name, id or uuid

optional arguments:
  -h, --help            show this help message and exit
  --virsh-trials N      number of trials if waiting for virsh
  --virsh-delay SECONDS
                        delay time in seconds between trials if waiting for
                        virsh
  -c URL, --connection URL
                        virsh connection URL
  --hotplug             affect the running domain and keep changes after
                        reboot
  --restart             shutdown and reboot the domain after the changes are
                        made
  -n, --dry-run         Do everything except actually make changes
```

## detach-pci command

The `detach-pci` command does the opposite of the `attach-pci` command by detaching the PCI device specified with
its address from the domain (i.e., the virtual machine).
By default, the change is only applied to the configuration and only takes effect after a restart.
If the `--restart` option is specified, the domain is restarted after the change.
If the `--hotplug` option is specified, the device is immediately available within the domain without restart.
The `--restart` and `--hotplug` options cannot be used together. To display actions to be performed without actually making changes,
use the `--n` or `--run-dry` option.

All `detach-pci` command options:

```
usage: nvidia-dev-ctl.py detach-pci [-h] [--virsh-trials N]
                                    [--virsh-delay SECONDS] [-c URL]
                                    [--hotplug] [--restart] [-n]
                                    PCI_ADDRESS DOMAIN

positional arguments:
  PCI_ADDRESS           PCI address of the NVIDIA device to attach
  DOMAIN                domain name, id or uuid

optional arguments:
  -h, --help            show this help message and exit
  --virsh-trials N      number of trials if waiting for virsh
  --virsh-delay SECONDS
                        delay time in seconds between trials if waiting for
                        virsh
  -c URL, --connection URL
                        virsh connection URL
  --hotplug             affect the running domain and keep changes after
                        reboot
  --restart             shutdown and reboot the domain after the changes are
                        made
  -n, --dry-run         Do everything except actually make changes
```
