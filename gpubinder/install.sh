#!/bin/bash

set -eo pipefail

THIS_DIR=$( (cd "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P) )

set -x
sudo cp "$THIS_DIR/nvidia-dev-ctl.service" /etc/systemd/system  # /usr/lib/systemd/system/
sudo cp "$THIS_DIR/nvidia-dev-ctl.py" /usr/bin/
[ ! -e "$THIS_DIR/nvidia-mdev-devices.conf" ] && touch "$THIS_DIR/nvidia-mdev-devices.conf"
sudo cp "$THIS_DIR/nvidia-mdev-devices.conf" /etc/nvidia-mdev-devices.conf
sudo systemctl daemon-reload
sudo systemctl enable nvidia-dev-ctl.service
