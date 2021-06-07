Arclight Dashboard is a web-based front end for libvirt based KVM virtual machines.



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

Now download the latest version of Arclight Dashboard to the web root directory.
sudo wget https://github.com/Chatnaut/Arclight/archive/refs/heads/version2.zip

Extract the downloaded package.
unzip version2.zip

Rename the extracted directory
sudo mv version2 arclight

Change the ownership of the arclight directory to the web server user (www-data).
sudo chown -R www-data:www-data /var/www/html/arclight
Creating a database

We will need a MySQL database for Arclight Dashboard to work with. To log into MySQL use the following command:
sudo mysql -u root

Once logged in, create a new database. I will name it arclight.
CREATE DATABASE arclight;

Now create a user for Arclight Dashboard to use. You could use the root user and password, but that is never advised. I will create a new user named arclight. Be sure to change the password value.
CREATE USER 'arclight'@'localhost' IDENTIFIED BY 'password';

Change the permissions of the new user to have full access to the database tables.
GRANT ALL PRIVILEGES ON arclight.* to 'arclight'@'localhost';

The new privileges should be applied, but sometimes you will need to flush the privileges so that they can be reloaded into the MySQL database. To do this use the following command:
FLUSH PRIVILEGES;

To exit MySQL, type quit or use the EXIT; statement.
EXIT;
Connecting to Arclight Dashboard

You will need to restart your server before you can use the hypervisor. This way the server restarts with all the necessary hypervisor software loaded and the user groups applied.
Restart Mysql  #service mysql restart
Restart Apache #/etc/init.d/apache2 restart

Once rebooted, use a web browser to navigate to your server’s IP address or domain name. Add /arclight to the end of the URL. For example: http://192.168.1.2/arclight


******************************************************************************************************************************************

Installation of PHPmyadmin to see Databases:

    1.Open a terminal window on your Ubuntu Server.
    2.Issue the command sudo apt-get install phpmyadmin php-mbstring php-gettext -y.
    3.When prompted, type your sudo password.
    4.Allow the installation to complete.

Make sure to select apache2. Done.

If you can't access phpmyadmin in browser apply these fixes:
#sudo ln -s /usr/share/phpmyadmin/ /var/www/phpmyadmin
Copy the apache.conf file from /etc/phpmyadmin to /etc/apache2/sites-available and to /etc/apache2/sites-enabled using file manager as root.
Then ran sudo service apache2 restart and everything was just fine.

[OPTIONAL]
In order to fix this problem, go back to the terminal window on your server and log into MySQL with the command:

sudo mysql -u root -p

Once at the MySQL prompt, you need to grant the proper permissions for the phpmyadmin user with the commands:

GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost';
FLUSH PRIVILEGES;
EXIT

Log out of the phpMyAdmin GUI and log back in (still using the phpmyadmin user). You should now have full privileges for MySQL with that user.

If you're concerned about security you could create an entirely new MySQL admin user like so:

CREATE USER USERNAME IDENTIFIED by 'PASSWORD';
GRANT ALL PRIVILEGES ON *.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
EXIT
