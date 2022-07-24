#!/bin/bash
#colors
green='\033[0;32m'
red='\033[0;31m'
clear='\033[0m'
bg_red='\033[0;41m'
bg_green='\033[0;42m'

echo -e "\n"
cat << "EOF"
.--------------------------------------------.
|   ___              __ _        __   __     |
|  / _ |  ____ ____ / /(_)___ _ / /  / /_    |
| / __ | / __// __// // // _ `// _ \/ __/    |
|/_/ |_|/_/   \__//_//_/ \_, //_//_/\__/     |
|                       /___/                |
'--------------------------------------------'
EOF
#create ascii arclight logo in terminal


#Check that your CPU supports hardware virtualization
# if [ $(cat /proc/cpuinfo | grep -c "vmx") -eq 0 ]
# then
#     echo -e "Arclight ERROR: ${bg_red} Your CPU does not support hardware virtualization ${clear}"
#     exit 1
# fi

set -eu -o pipefail # fail on error and report it, debug all lines

sudo -n true
test $? -eq 0 || exit 1 "You should have sudo privilege to run this script"

echo -e "${green}Installing pre-requisites${clear}"
while read -r p; do sudo apt-get install -y "$p"; done < <(
    cat <<"EOF"
    curl
    wget
    
    qemu-kvm
    libvirt-daemon-system
    libvirt-clients
    bridge-utils
    xauth
    zip unzip

    apache2 
    lsb-core
    php
    libapache2-mod-php
EOF
)
#install the following packages according to the linux distro
if [ "$(lsb_release -a | grep -c 20.04)" -eq 2 ]; then
    echo -e "${green}Working on MongoDB Database${clear}"
    apt install php-dev php-pear -y
    apt-get install mongodb
    sudo apt install -y php-dev
    pecl install mongodb
    echo -e "\n; MongoDB PHP driver\nextension=mongodb.so" | sudo tee -a /etc/php/7.4/apache2/php.ini
    echo -e "${green}Installing packages for Ubuntu 20.04${clear}"
    while read -r p; do sudo apt-get install -y "$p"; done < <(
        cat <<"EOF"
    python3
    python3-pip
EOF
    )

elif [ "$(lsb_release -a | grep -c 18.04)" -eq 2 ]; then
    echo -e "${green}Installing packages for Ubuntu 18.04${clear}"
    while read -r p; do sudo apt-get install -y "$p"; done < <(
        cat <<"EOF"
    mongodb
    php-mongodb
    python
    python-pip
EOF
    )
elif [ "$(lsb_release -a | grep -c 22.04)" -eq 2 ]; then
    echo -e "${green}Working on MongoDB Database${clear}"
    sudo apt-get update -y
    wget http://archive.ubuntu.com/ubuntu/pool/main/o/openssl/libssl1.1_1.1.0g-2ubuntu4_amd64.deb
    sudo dpkg -i libssl1.1_1.1.0g-2ubuntu4_amd64.deb
    wget -qO - https://www.mongodb.org/static/pgp/server-4.4.asc | sudo apt-key add -
    echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/4.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-4.4.list
    sudo apt-get update -y
    sudo apt-get install -y mongodb-org
    sudo apt install -y php-dev
    pecl install mongodb
    echo -e "\n; MongoDB PHP driver\nextension=mongodb.so" | sudo tee -a /etc/php/8.1/apache2/php.ini
    echo -e "${green}Installing packages for for Ubuntu 22.04${clear}"
    while read -r p; do sudo apt-get install -y "$p"; done < <(
        cat <<"EOF"
    python3
    python3-pip
EOF
    )
else
    echo -e "${red}Arclight ERROR: ${bg_red}Arclight is not supported on this Linux distribution${clear}"
    exit 1
fi

pip install webssh

curl -sL https://deb.nodesource.com/setup_16.x | sudo -E bash -
apt install nodejs

#Configuring files and permissions
echo -e "${green}Configuring files and permissions...${clear}"
#Add user to libvirt group
# adduser www-data libvirtd
cd /var/www/html
echo -e "${green}Getting the latest version of arclight...${clear}"
sleep 4
wget https://github.com/Chatnaut/Arclight/archive/refs/tags/v1.0.0.tar.gz
echo -e "${green}Extracting the archive...${clear}"
tar -xvzf v1.0.0.tar.gz
mv Arclight-1.0.0 arclight
# chown -R www-data:www-data /var/www/html

#Setup PM2 process manager to keep your app running
# npm i pm2 -g
# npm install
# pm2 start app.js

# To make sure app starts when reboot
# pm2 startup

echo "You're good now :)"

echo -e "\n"
cat <<"EOF"

       
.--------------.    
|   Finished!  |         |          /\  ._ _ | o  _  |_ _|_ 
'--------------'         |         /--\ | (_ | | (_| | | |_ 
      ^      (\_/)       |                        _|            
      '----- (O.o)       |  You can now access the web interface at:
             (> <)       |  http://ip-address-of-machine/

EOF
#reboot the server to apply changes
echo "The hypervisor needs to be rebooted in order to load the necessary packages. Do you want to reboot now? (y/n)"
read reboot
if [ $reboot = "y" ]; then
    echo -e "${green}Rebooting the server...${clear}"
    sleep 6
    reboot
else
    echo -e "${green}Restarting only the required services in order to apply changes...${clear}"
    sleep 3
    service apache2 restart
    if [ "$(lsb_release -a | grep -c 20.04)" -eq 2 ]; then
        service mongodb restart
    elif [ "$(lsb_release -a | grep -c 18.04)" -eq 2 ]; then
        service mongodb restart
    elif [ "$(lsb_release -a | grep -c 22.04)" -eq 2 ]; then
        service mongod start
    fi

    echo "Bye!"
    exit 0
fi