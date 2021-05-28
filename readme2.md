

Before installing software, run the sudo apt update command to make sure you are installing from the latest repository information.
Installing the necessary packages

On the Ubuntu server, install the QEMU + KVM hypervisor  using the following command:
sudo apt install qemu-kvm libvirt-bin

Install the web server, database, and necessary PHP packages to your server. Use the following command:
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-xml php-libvirt-php

The built-in VNC connection requires python. To install it use the following command:
sudo apt install python
Configuring files and permissions

To use VNC to connect into your virtual machines, you will need to edit the /etc/libvirt/qemu.conf file. Be sure to allow listening on IP address 0.0.0.0 by uncommenting the line #vnc_listen = “0.0.0.0” and saving the file.
sudo nano /etc/libvirt/qemu.conf

The web server user account on Ubuntu is called www-data. This account will need to have permissions to work with libvirt. The group is called libvirtd in Ubuntu 16.04 and libvirt in Ubuntu 18.04.  To do this, add the www-data user to the necessary group.
sudo adduser www-data libvirt

Change your directory location to the root directory of your web server. The default location is /var/www/html/ in Ubuntu.
cd /var/www/html

Now download the latest version of VM Dashboard to the web root directory.
sudo wget https://github.com/VMDashboard/vmdashboard/archive/v19.01.03.tar.gz

Extract the downloaded package.
sudo tar -xzf v19.01.03.tar.gz

Rename the extracted directory
sudo mv vmdashboard-19.01.03 vmdashboard

Change the ownership of the vmdashboard directory to the web server user (www-data).
sudo chown -R www-data:www-data /var/www/html/vmdashboard
Creating a database

We will need a MySQL database for VM Dashboard to work with. To log into MySQL use the following command:
sudo mysql -u root

Once logged in, create a new database. I will name it vmdashboard.
CREATE DATABASE vmdashboard;

Now create a user for VM Dashboard to use. You could use the root user and password, but that is never advised. I will create a new user named vmdashboard. Be sure to change the password value.
CREATE USER 'vmdashboard'@'localhost' IDENTIFIED BY 'password';

Change the permissions of the new user to have full access to the database tables.
GRANT ALL PRIVILEGES ON vmdashboard.* to 'vmdashboard'@'localhost';

The new privileges should be applied, but sometimes you will need to flush the privileges so that they can be reloaded into the MySQL database. To do this use the following command:
FLUSH PRIVILEGES;

To exit MySQL, type quit or use the EXIT; statement.
EXIT;
Connecting to VM Dashboard

You will need to restart your server before you can use the VM Dashboard software. This way the server restarts with all the necessary hypervisor software loaded and the user groups applied.
sudo reboot

Once rebooted, use a web browser to navigate to your server’s IP address or domain name. Add /vmdashboard to the end of the URL. For example: http://192.168.1.2/vmdashboard
