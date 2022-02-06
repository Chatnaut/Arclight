#!/bin/bash

# https://cubiclenate.com/2019/10/31/power-cycling-pcie-devices-from-the-command-line/
# https://nvidia.custhelp.com/app/answers/detail/a_id/3751/~/useful-nvidia-smi-queries

rtrim() {
    echo -n "${1%"${1##*[![:space:]]}"}"
}

trim() {
    local var="$1"
    var="${var#"${var%%[![:space:]]*}"}"   # remove leading whitespace characters
    var="${var%"${var##*[![:space:]]}"}"   # remove trailing whitespace characters
    echo -n "$var"
}

# https://stackoverflow.com/questions/1527049/how-can-i-join-elements-of-an-array-in-bash
join-by() {
    local d=$1; shift; local f=$1; shift; printf %s "$f" "${@/#/$d}"
}

get-nvidia-pci-devices() {
    local dev vendor
    for dev in /sys/bus/pci/devices/*; do
        if [[ -e "$dev/vendor" ]]; then
            vendor=$(trim "$(< "$dev/vendor")")
            if [[ "$vendor" = "0x10de" ]]; then
                printf "%s " "$(basename -- "$dev")"
            fi
        fi
    done
    printf "\n"
}

# $1      - driver
# $2 ...  - devices
unbind-driver-from-devices() {
    local driver dev
    driver=$1
    shift
    if [[ ! -e "/sys/bus/pci/drivers/$driver" ]]; then
        echo >&2 "unbind-driver-from-devices: No driver interface /sys/bus/pci/drivers/$driver"
        return 1
    fi
    if [[ ! -e "/sys/bus/pci/drivers/$driver/unbind" ]]; then
        echo >&2 "unbind-driver-from-devices: No unbind interface: /sys/bus/pci/drivers/$driver/unbind"
        return 1
    fi
    for dev in "$@"; do
        if [[ -e "/sys/bus/pci/drivers/$driver/$dev" ]]; then
            echo >&2 "unbind-driver-from-devices: Unbind driver $driver from PCI device $dev"
            echo "$dev" > "/sys/bus/pci/drivers/$driver/unbind"
        else
            echo >&2 "unbind-driver-from-devices: Driver $driver is not loaded for device $dev"
        fi
    done
}

# $1 ...  - devices
unbind-device-drivers() {
    local dev driver
    for dev in "$@"; do
        if driver=$(get-driver-of-device "$dev"); then
            unbind-driver-from-devices "$driver" "$dev"
        fi
    done
}

# $1 ... - devices
remove-devices() {
    local dev
    for dev in "$@"; do
        if [[ -e "/sys/bus/pci/devices/$dev" && -e "/sys/bus/pci/devices/$dev/remove" ]]; then
            unbind-device-drivers "$dev"
            if driver=$(get-driver-of-device "$dev"); then
                echo >&2 "remove-devices: Could not remove driver $driver from device $dev"
                continue
            fi
            echo "1" > "/sys/bus/pci/devices/$dev/remove"
            echo >&2 "remove-devices: Removed device $dev"
        else
            echo >&2 "remove-devices: No path /sys/bus/pci/devices/$dev or /sys/bus/pci/devices/$dev/remove"
            return 1
        fi
    done
}

# $1 ... - devices
reset-devices() {
    local dev
    for dev in "$@"; do
        if [[ -e "/sys/bus/pci/devices/$dev" && -e "/sys/bus/pci/devices/$dev/reset" ]]; then
            unbind-device-drivers "$dev"
            if driver=$(get-driver-of-device "$dev"); then
                echo >&2 "remove-devices: Could not remove driver $driver from device $dev"
                continue
            fi
            echo "1" > "/sys/bus/pci/devices/$dev/reset"
            echo >&2 "remove-devices: Reset device $dev"
        else
            echo >&2 "remove-devices: No path /sys/bus/pci/devices/$dev or /sys/bus/pci/devices/$dev/reset"
            return 1
        fi
    done
}

# $1 - device
get-driver-of-device() {
    local dev driver_path
    dev=$1
    if [[ -z "$dev" ]]; then
        echo >&2 "get-driver-of-device: no device specified"
        return 1
    fi
    if [[ ! -e "/sys/bus/pci/devices/$dev/driver" ]]; then
        echo >&2 "get-driver-of-device: path /sys/bus/pci/devices/$dev/driver does not exist"
        return 1
    fi
    driver_path=$(readlink -f "/sys/bus/pci/devices/$dev/driver")
    basename -- "$driver_path"
}

# $1     - driver
# $2 ... - PCI device ID
bind-driver-to-devices() {
    local driver dev driver_override_path
    driver=$1
    shift
    for dev in "$@"; do
        driver_override_path=/sys/bus/pci/devices/$dev/driver_override
        if [[ ! -e "$driver_override_path" ]]; then
            echo >&2 "Missing driver_override interface: $driver_override_path"
            continue
        fi
        echo >&2 "bind-driver-to-devices: Bind driver $driver to device $dev"
        echo "$driver" > "$driver_override_path"
        if [[ ! -e "/sys/bus/pci/drivers/$driver" ]]; then
            echo >&2 "Driver $driver missing, try loading it first"
            modprobe "$driver"
            if [[ "$driver" = "nvidia" ]]; then
                modprobe nvidia_vgpu_vfio
            fi
        fi
        if [[ ! -e "/sys/bus/pci/drivers/$driver" ]]; then
            echo >&2 "Driver $driver missing, no path /sys/bus/pci/drivers/$driver"
            return 1
        fi
        { echo "$dev" > "/sys/bus/pci/drivers/$driver/bind" || true; } 2>/dev/null;
    done
    # if [[ "$driver" = "nvidia" ]]; then
    #    systemctl restart nvidia-vgpud.service
    #    systemctl restart nvidia-vgpu-mgr.service
    # fi
}

#  rmmod nvidia_vgpu_vfio
#  rmmod nvidia
#  rmmod mdev
#  rmmod vfio_mdev
#  rmmod mdev

get-service-exit-code() {
    systemctl show -p ExecMainStatus "$1" | sed 's/ExecMainStatus=//g'
}

check-nvidia-mdev() {
    local gpus g
    if [[ ! -e "/sys/class/mdev_bus" ]]; then
        echo >&2 "check-nvidia-mdev: Missing mdev_bus path /sys/class/mdev_bus"
        return 1
    fi
    if [[ $# -gt 0 ]]; then
        gpus=$*
    else
        gpus=$(get-nvidia-pci-devices)
    fi
    for g in $gpus; do
        driver=$(get-driver-of-device "$g")
        if [[ "$driver" = "nvidia" && ! -e "/sys/class/mdev_bus/$g" ]]; then
            echo >&2 "check-nvidia-mdev: Missing mdev_bus for device: $g"
            return 1
        fi
    done
    return 0
}

restart-nvidia-services() {
    local svc
    for svc in nvidia-vgpud.service nvidia-vgpu-mgr.service; do
        echo >&2 "restart-nvidia-services: Restartig service $svc"
        systemctl restart "$svc"
        sleep 5
        if [[ "$(get-service-exit-code "$svc")" != "0" ]]; then
            echo >&2 "restart-nvidia-services: Service $svc failed"
            return 1
        else
            echo >&2 "restart-nvidia-services: Service $svc successfully restarted"
        fi
    done
    return 0
}

is-driver-loaded() {
    # disabling pipefail required because grep will stop after first match
    # https://stackoverflow.com/questions/19120263/why-exit-code-141-with-grep-q
    set +o pipefail
    if lsmod | grep -Eq "^$1 "; then
        set -o pipefail
        return 0
    fi
    return 1
}

# $1 pci address
is-gpu-ok-adhoc() {
    local driver
    driver=$(get-driver-of-device "$1")
    if [[ "$driver" != "nvidia" ]]; then
        return 1
    fi
    # disabling pipefail required because grep will stop after first match
    # https://stackoverflow.com/questions/19120263/why-exit-code-141-with-grep-q
    set +o pipefail
    if sudo lspci -n -s "$1" -vv | grep -q "Latency Tolerance Reporting"; then
        set -o pipefail
        return 1
    fi
    return 0
}

# $1 pci address
is-gpu-ok() {
    local driver
    # disabling pipefail required because grep will stop after first match
    # https://stackoverflow.com/questions/19120263/why-exit-code-141-with-grep-q
    driver=$(get-driver-of-device "$1")
    if [[ "$driver" != "nvidia" ]]; then
        return 1
    fi
    nvidia-smi --query-gpu=pci.bus_id,vbios_version -i "$1" --format=csv,noheader &>/dev/null
}

get-all-failed-nvidia-pci-devices() {
    dmesg -T | sed -E -e  '/RmInitAdapter failed!/!d; s/^.*NVRM: GPU ([[:alnum:]]{4}:[[:alnum:]]{2}:[[:alnum:]]{2}.[[:alnum:]]).*$/\1/g' | sort -u
}

LAST_FAILED_DEVICES_FILE=/tmp/last-failed-nvidia-devices-$(id -un).txt
rm -f "$LAST_FAILED_DEVICES_FILE"

# shellcheck disable=SC2120
get-last-failed-nvidia-pci-devices() {
    local failed_devices skip_num_failed_devices
    declare -a failed_devices_array last_failed_devices_array
    failed_devices=$(dmesg | sed -E -e  '/RmInitAdapter failed!/!d; s/^.*NVRM: GPU ([[:alnum:]]{4}:[[:alnum:]]{2}:[[:alnum:]]{2}.[[:alnum:]]).*$/\1/g')
    IFS=$'\n' read -rd '' -a failed_devices_array <<<"$failed_devices"
    if [[ -e "$LAST_FAILED_DEVICES_FILE" ]]; then
        IFS=$'\n' read -rd '' -a last_failed_devices_array < "$LAST_FAILED_DEVICES_FILE"
        skip_num_failed_devices=${#last_failed_devices_array[@]}
    else
        skip_num_failed_devices=0
    fi
    if [[ "$1" != "--forget-last" ]]; then
        printf "%s" "$failed_devices" > "$LAST_FAILED_DEVICES_FILE"
    fi
    if [[ -n "$skip_num_failed_devices" && "$skip_num_failed_devices" -gt 0 ]]; then
        failed_devices=$(join-by $'\n' "${failed_devices_array[@]:$skip_num_failed_devices}")
        # failed_devices=$(sed "1,${SKIP_NUM_FAILED_DEVICES}d" <<<"$failed_devices")
        # IFS=$'\n' read -rd '' -a failed_array <<<"$failed"
        # num_failed=${#failed_array[@]}
    fi
    if [[ -n "$failed_devices" ]]; then
        sort -u <<<"$failed_devices" | tr '\n' ' '
    fi
}

is-nvidia-mdev-ok() {
    local do_fix gpus driver
    if ! is-driver-loaded nvidia; then
        echo >&2 "is-nvidia-mdev-ok: Module nvidia is not loaded, nothing to check"
        return 0
    else
        echo >&2 "is-nvidia-mdev-ok: Module nvidia is loaded, check if everything is ok"
    fi
    if [[ $# -gt 0 ]]; then
        gpus=$*
    else
        gpus=$(get-nvidia-pci-devices)
    fi
    if [[ -z "$gpus" ]]; then
        echo >&2 "is-nvidia-mdev-ok: Fatal error: no NVIDIA PCI devices specified or detected"
        return 2
    fi
    # shellcheck disable=SC2086
    if check-nvidia-mdev $gpus; then
        echo >&2 "is-nvidia-mdev-ok: All mdev_bus devices present, nothing to fix"
        return 0
    else
        do_fix=true
        echo >&2 "is-nvidia-mdev-ok: Some mdev_bus devices missing"
    fi
    #for gpu in $gpus; do
    #    driver=$(get-driver-of-device "$gpu")
    #    if [[ "$driver" = "nvidia" ]] && ! is-gpu-ok "$gpu"; then
    #        echo >&2 "is-nvidia-mdev-ok: GPU $gpu reports failure"
    #        do_fix=true
    #    fi
    #done
    set +o pipefail
    if dmesg | grep -q "RmInitAdapter failed"; then
        set -o pipefail
        do_fix=true
        echo >&2 "is-nvidia-mdev-ok: 'RmInitAdapter failed' error detected"
    fi
    set -o pipefail
    if [[ -z "$do_fix" ]]; then
        echo >&2 "is-nvidia-mdev-ok: No problems detected, nothing to fix"
        return 0
    fi
    return 1
}

unload-nvidia-drivers() {
    local d
    for d in nvidia_vgpu_vfio vfio_mdev mdev nvidia; do
        if is-driver-loaded "$d"; then
            echo >&2 "fix-nvidia-mdev: Unload driver $d"
            if ! modprobe -r "$d"; then
                echo >&2 "fix-nvidia-mdev: Failed unloading driver $d"
                return 1
            fi
        else
            echo >&2 "fix-nvidia-mdev: Driver $d is not loaded"
        fi
    done
    return 0
}

rescan-pci-bus() {
    echo "1" > /sys/bus/pci/rescan
}

# $1 ... - PCI device IDs
fix-nvidia-mdev() {
    local do_fix gpus d g
    if [[ $# -gt 0 ]]; then
        gpus=$*
    else
        gpus=$(get-nvidia-pci-devices)
    fi
    if [[ -z "$gpus" ]]; then
        echo >&2 "fix-nvidia-mdev: Fatal error: no NVIDIA PCI devices specified or detected"
        return 1
    fi
    # shellcheck disable=SC2086
    if is-nvidia-mdev-ok $gpus; then
        return 0
    fi
    echo >&2 "fix-nvidia-mdev: Unbinding NVIDIA PCI devices: $gpus"
    # shellcheck disable=SC2086
    unbind-driver-from-devices nvidia $gpus
    echo >&2 "fix-nvidia-mdev: Unload NVIDIA drivers"
    if ! unload-nvidia-drivers; then
        return 1
    fi
    echo >&2 "fix-nvidia-mdev: nvidia drivers"
    lsmod | grep nv
    echo >&2 "fix-nvidia-mdev: pause"
    sleep 5
    echo >&2 "fix-nvidia-mdev: reload drivers"
    if ! modprobe nvidia_vgpu_vfio; then
        echo >&2 "fix-nvidia-mdev: Failed loading nvidia_vgpu_vfio"
        return 1
    fi
    # (set -x; modprobe -r nvidia_vgpu_vfio nvidia vfio_mdev mdev;)
    #for d in nvidia_vgpu_vfio nvidia vfio_mdev mdev; do echo "Unload driver $d"; rmmod "$d" || true; done

    #for d in nvidia nvidia_vgpu_vfio; do
    #   echo "Loading driver: $d"
    #    modprobe "$d"
    #done
    # (set -x; modprobe nvidia; modprobe nvidia_vgpu_vfio;)
    echo >&2 "fix-nvidia-mdev: restart NVIDIA mdev services"
    if ! restart-nvidia-services; then
        return 1
    fi
    sleep 10
    # shellcheck disable=SC2086
    is-nvidia-mdev-ok $gpus
}

fix-nvidia-mdev-2() {
    local gpus
    # shellcheck disable=SC2086
    if is-nvidia-mdev-ok $gpus; then
        return 0
    fi
    gpus=$(get-nvidia-pci-devices)
    # shellcheck disable=SC2086
    unbind-device-drivers $gpus
    unload-nvidia-drivers
    # shellcheck disable=SC2086
    remove-devices $gpus
    unload-nvidia-drivers
    echo >&2 "fix-nvidia-mdev-2: rescan PCI bus"
    echo "1" > /sys/bus/pci/rescan
    echo >&2 "fix-nvidia-mdev-2: pause"
    sleep 5
    echo >&2 "fix-nvidia-mdev-2: restart NVIDIA mdev services"
    if ! restart-nvidia-services; then
        return 1
    fi
    sleep 10
    # shellcheck disable=SC2086
    check-nvidia-mdev $gpus
}

fix-nvidia-mdev-3() {
    local gpus failed_devices
    gpus=$(get-nvidia-pci-devices)
    # shellcheck disable=SC2086
    rebind-devices-to-nvidia $gpus
    # shellcheck disable=SC2086
    if is-nvidia-mdev-ok $gpus; then
        return 0
    fi
    failed_devices=$(get-last-failed-nvidia-pci-devices)
    echo >&2 "fix-nvidia-mdev-3: failed NVIDIA devices: $failed_devices"
    echo >&2 "fix-nvidia-mdev-3: run nvidia-smi for driver initialization"
    nvidia-smi
    failed_devices=$(get-last-failed-nvidia-pci-devices)
    if [[ -n "$failed_devices" ]]; then
        echo >&2 "fix-nvidia-mdev-3: failed NVIDIA devices after nvidia-smi: $failed_devices"
        # we have an error and need to fix
        echo >&2 "fix-nvidia-mdev-3: reset failed NVIDIA devices: $failed_devices"
        # shellcheck disable=SC2086
        reset-devices $failed_devices
        echo >&2 "fix-nvidia-mdev-3: rebind failed NVIDIA devices: $failed_devices"
        # shellcheck disable=SC2086
        rebind-devices-to-nvidia $failed_devices
        echo >&2 "fix-nvidia-mdev-3: run nvidia-smi for driver initialization"
        nvidia-smi
        failed_devices=$(get-last-failed-nvidia-pci-devices)
        if [[ -n "$failed_devices" ]]; then
            echo >&2 "fix-nvidia-mdev-3: failed NVIDIA devices after nvidia-smi: $failed_devices"
        else
            echo >&2 "fix-nvidia-mdev-3: no failed NVIDIA devices after nvidia-smi !"
        fi
        echo >&2 "fix-nvidia-mdev-3: waiting for 15 seconds..."
        sleep 15
        failed_devices=$(get-last-failed-nvidia-pci-devices)
        if [[ -n "$failed_devices" ]]; then
            echo >&2 "fix-nvidia-mdev-3: failed NVIDIA devices after waiting: $failed_devices"
        else
            echo >&2 "fix-nvidia-mdev-3: no failed NVIDIA devices after waiting !"
        fi
    fi

    echo >&2 "fix-nvidia-mdev-3: restart NVIDIA services"
    if ! restart-nvidia-services; then
        return 1
    fi
    for ((i = 1 ; i <= 30; i++)); do
        # shellcheck disable=SC2086
        if is-nvidia-mdev-ok $gpus; then
            return 0
        fi
        sleep 2
    done
    # shellcheck disable=SC2086
    is-nvidia-mdev-ok $gpus
}

print-drivers() {
    local gpus dev driver
    if [[ $# -gt 0 ]]; then
        gpus=$*
    else
        gpus=$(get-nvidia-pci-devices)
    fi
    for dev in $gpus; do
        if ! driver=$(get-driver-of-device "$dev" 2>/dev/null); then
            driver="no driver"
        fi
        echo "$dev   $driver"
    done
}

rebind-devices-to-nvidia() {
    local gpus dev driver
    if [[ $# -gt 0 ]]; then
        gpus=$*
    else
        gpus=$(get-nvidia-pci-devices)
    fi

    for dev in $gpus; do
        driver=$(get-driver-of-device "$dev" 2>/dev/null)
        if [[ "$driver" != "nvidia" && -n "$driver" ]]; then
            unbind-driver-from-devices "$driver" "$dev"
            sleep 1
        fi
        bind-driver-to-devices nvidia "$dev"
    done
}

# # Unbind PCI passtrough
# GPUS=$(get-nvidia-pci-devices)
# unbind-driver-from-devices vfio-pci $GPUS
# bind-driver-to-devices nvidia $GPUS
#
#
# # After reboot and
# # dmesg  | grep MDEV -> empty
# # dmesg | grep RmInitAdapter -> non empty
# unbind-driver-from-devices nvidia $GPUS
# for d in nvidia_vgpu_vfio nvidia vfio_mdev mdev; do echo "Unload driver $d"; rmmod "$d" || true; done
# modprobe nvidia
# modprobe nvidia_vgpu_vfio
# systemctl restart nvidia-vgpud.service
# systemctl restart nvidia-vgpu-mgr.service

# Sometimes nvidia driver is in use and you can't unload it -> nothing possible with the safe method
# Twice I needed to reset the server because it was not booting
# If GPU has an "RmInitAdapter failed!" error I can fix other devices but not the one with the error with the safe method
# Successful tests hard method
# Test x3 ok
# Test #4 failed :
# Jul 31 21:57:45 asr-telaviv nvidia-vgpud[4470]: error: failed to attach device: 59
# Jul 31 21:57:45 asr-telaviv nvidia-vgpud[4470]: error: failed to read pGPU information: 9
# Jul 31 21:57:45 asr-telaviv nvidia-vgpud[4470]: error: failed to send vGPU configuration info to RM: 9

enable-pci-passtrough() {
    local pci_ids
    # Comma delimited list of PCI device IDs
    pci_ids=$(lspci -nn | sed -E '/[Nn][Vv][Ii][Dd][Ii][Aa]/!d;s/.*\[([[:xdigit:]]{4}:[[:xdigit:]]{4})\].*/\1,/' | sort -u | tr -d '\n' | sed 's/,$//';)
    echo "options vfio-pci ids=$pci_ids" > /etc/modprobe.d/vfio.conf
    echo 'vfio-pci' > /etc/modules-load.d/vfio-pci.conf
}

disable-pci-passtrough() {
    rm -f /etc/modprobe.d/vfio.conf /etc/modules-load.d/vfio-pci.conf
}
