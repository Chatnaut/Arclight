<p align="center">
  <a href="https://www.chatnaut.com">
    <img alt="Chatnaut_Cloud_Solutions" src="https://i.imgur.com/W3zKRyu.png" width="300" />
  </a>
</p>

<h3 align="center">
  Build, automate and self-host internal tools in minutes
</h3>
<p align="center">
  Budibase is an open-source low-code platform, helping developers and IT professionals build, automate, and ship internal tools on their own infrastructure in minutes.
</p>

<h3 align="center">
 🤖 🎨 🚀
</h3>

<p align="center">
  <img alt="Budibase design ui" src="https://i.imgur.com/5BnXPsN.png">
</p>

<p align="center">
  <a href="https://github.com/Chatnaut/Arclight/releases">
    <img alt="GitHub all releases" src="https://img.shields.io/github/downloads/Budibase/budibase/total">
  </a>
  <a href="https://github.com/Chatnaut/Arclight/releases">
    <img alt="GitHub release (latest by date)" src="https://img.shields.io/github/v/release/Chatnaut/Arclight">
  </a>
  <a href="https://twitter.com/intent/follow?screen_name=chatnaut">
    <img src="https://img.shields.io/twitter/follow/chatnaut?style=social" alt="Follow @chatnaut" />
  </a>
  <img src="https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg" alt="Code of conduct" />
  <a href="https://codecov.io/gh/Budibase/budibase">
    <img src="https://codecov.io/gh/Budibase/budibase/graph/badge.svg?token=E8W2ZFXQOH"/>
  </a>
</p>


  * [Getting-Started](#Getting-Started)
  * [list-mdev](#list-mdev-command)
  * [list-used-pci](#list-used-pci-command)
  * [list-used-mdev](#list-used-mdev-command)
  * [create-mdev](#create-mdev-command)
  * [remove-mdev](#remove-mdev-command)
  * [save](#save-command)
  * [restore](#restore-command)

## Getting Started
<!-- Installation of Arclight Web Console FOR UBUNTU 18.04****************************************************************** -->

Arclight Dashboard is a web-based front end for libvirt based KVM virtual machines.

Before installing software, run the sudo apt update command to make sure you are installing from the latest repository information.
Installing the necessary packages

On the Ubuntu server, install the QEMU + KVM hypervisor  using the following command:
```
sudo apt install qemu-kvm libvirt-bin
```

Install the web server, database, and necessary PHP packages to your server. Use the following command:
```
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-xml php-libvirt-php
```
The built-in VNC connection requires python. To install it use the following command:
```
sudo apt install python
```
Configuring files and permissions

To use VNC to connect into your virtual machines, you will need to edit the /etc/libvirt/qemu.conf file. Be sure to allow listening on IP address 0.0.0.0 by uncommenting the line #vnc_listen = “0.0.0.0” and saving the file.
```
sudo nano /etc/libvirt/qemu.conf
```
The web server user account on Ubuntu is called www-data. This account will need to have permissions to work with libvirt. The group is called libvirtd in Ubuntu 16.04 and libvirt in Ubuntu 18.04.  To do this, add the www-data user to the necessary group.
#sudo adduser www-data libvirt

Change your directory location to the root directory of your web server. The default location is /var/www/html/ in Ubuntu.
```
cd /var/www/html
```
Now download the latest version of Arclight Dashboard to the web root directory.
```
sudo wget https://github.com/Chatnaut/Arclight/archive/refs/heads/version2.zip
```
Extract the downloaded package.
```
sudo tar -xzf arclight.tar.gz
```
Rename the extracted directory
```
sudo mv version2 arclight
```
Change the ownership of the arclight directory to the web server user (www-data).
```
sudo chown -R www-data:www-data /var/www/html/arclight
```
Creating a database

We will need a MySQL database for Arclight Dashboard to work with. To log into MySQL use the following command:
```
sudo mysql -u root
```
Once logged in, create a new database. I will name it arclight.
```
CREATE DATABASE arclight;
```
Now create a user for Arclight Dashboard to use. You could use the root user and password, but that is never advised. I will create a new user named arclight. Be sure to change the password value.
```
CREATE USER 'arclight'@'localhost' IDENTIFIED BY 'password';
```
Change the permissions of the new user to have full access to the database tables.
```
GRANT ALL PRIVILEGES ON arclight.* to 'arclight'@'localhost';
```
The new privileges should be applied, but sometimes you will need to flush the privileges so that they can be reloaded into the MySQL database. To do this use the following command:
```
FLUSH PRIVILEGES;
```
To exit MySQL, type quit or use the EXIT; statement.
```
EXIT;
```
Connecting to Arclight Dashboard

You will need to restart your server before you can use the hypervisor. This way the server restarts with all the necessary hypervisor software loaded and the user groups applied.
Restart Mysql  ```service mysql restart```
Restart Apache ```/etc/init.d/apache2 restart```
               ```sudo service apache2 restart```

Once rebooted, use a web browser to navigate to your server’s IP address or domain name. Add /arclight to the end of the URL. For example: http://192.168.1.2/arclight



<!-- Installation of Arclight Web Console FOR CENTOS 7 minimal****************************************************************** -->

This guide follows a fresh installation of the CentOS 7 minimal server. Before installing packages be sure to update repository information using the following command:
yum update -y
Installing the necessary packages

On the CentOS server, install the QEMU + KVM hypervisor  using the following command:
yum install qemu-kvm libvirt -y

The PHP Libvirt extension is located in the Enterprise Linux repository. To setup this repository use the following command:
yum install epel-release -y

Install the web server, database, and necessary PHP packages to your server. Use the following command:
yum install httpd mariadb-server mariadb php php-mysql php-xml php-libvirt -y

You will need to start and enable the Apache web server and Maria database. To do this use the following commands:
systemctl start mariadb
systemctl enable mariadb
systemctl start httpd
systemctl enable httpd
Configuring files and permissions

To use VNC to connect into your virtual machines, you will need to edit the /etc/libvirt/qemu.conf file. Be sure to allow listening on IP address 0.0.0.0 by uncommenting the line #vnc_listen = “0.0.0.0” and saving the file.(If nano is not installed you can install it with yum install nano, or just simply use vi instead of nano).
nano /etc/libvirt/qemu.conf

The web server user account on CentOS is called apache. This account will need to have permissions to work with libvirt. We can do this by adding the apache user to the libvirt group.  To do this, use the following command:
usermod -a -G libvirt apache

Change your directory location to the root directory of your web server. The default location is /var/www/html/ in Ubuntu.
cd /var/www/html

The minimal installation of CentOS does not come with wget to download files. You will also need git to perform software updates. Install the, using the following command:
yum install wget git -y

Now download the latest version of Arclight Dashboard to the web root directory.
wget https://github.com/arclight/arclight/archive/v19.01.03.tar.gz

Extract the downloaded package.
sudo tar -xzf v19.01.03.tar.gz

Rename the extracted directory
sudo mv arclight-19.01.03 arclight

Change the ownership of the arclight directory to the web server user (www-data).
chown -R apache:apache /var/www/html/arclight

In order for PHP to be able to save configuration files we will need to run the following command:
chcon -t httpd_sys_rw_content_t /var/www/html/arclight/ -R

The CentOS firewall will block incoming http and https traffic. Also the VNC connection uses port 6080. To allow the web traffic use the following commands:
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --permanent --add-port=6080/tcp
systemctl restart firewalld

SeLinux will block the qemu connection through the web browser. Modify the /etc/sysconfig/selinux file. The default value of the SELINUX=enforcing. Change it to SELINUX=permissive.
nano /etc/sysconfig/selinux
Creating a database

We will need a MySQL database for Arclight Dashboard to work with. To log into MySQL use the following command:
mysql -u root

You will be prompted for your the password that was setup for the root user on MySQL. Once logged in, create a new database. I will name it arclight.
CREATE DATABASE arclight;

Now create a user for Arclight Dashboard to use. You could use the root user and password, but that is never advised. I will create a new user named vmdashbaord. Be sure to change the password.
CREATE USER 'arclight'@'localhost' IDENTIFIED BY 'password';

Change the permissions of the new user to have full access to the database tables.
GRANT ALL PRIVILEGES ON arclight.* to 'arclight'@'localhost';

The new privileges should be applied, but sometimes you will need to flush the privileges so that they can be reloaded into the MySQL database. To do this use the following command:
FLUSH PRIVILEGES;

To exit MySQL, type quit or use the EXIT; statement.
EXIT;
Connecting to Arclight Dashboard

You will need to restart your server before you can use the Arclight Dashboard software. This way the server restarts with all the necessary hypervisor software loaded and the user groups applied.
sudo reboot

Once rebooted, use a web browser to navigate to your server’s IP address or domain name. Add /arclight to the end of the URL. For example: http://192.168.1.2/arclight


<!-- Installation of PHPmyadmin to see Databases:********************************************************** -->

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



<!-- <!-- Defining Storage Pools via the Terminal*********************************************************************************** -->

Using arclight, you can define Libvirt storage pools in the /var, /mnt, and /media directories. This was done to prevent full access to the operating system from the Web interface. If you need to define a storage pool outside of these limitations, you can use the terminal using Libvirt to register a storage pool. In this example we will define the /home/ubuntu/ directory as a storage pool.

Define the storage pool using the pool-define-as command from virsh. We will pass in the type of storage devices which is a directory, name which we will call myHomePool, and the filepath to the storage pool.

```
virsh pool-define-as --type dir --name myHomePool --target /home/ubuntu
```
The storage pool will now show up in arclight. If you wish to view it in the terminal you can use the following command

```
virsh pool-list --all
```
The storage pool myHomePool will not be running, you can start it using arclight, or in the terminal you can use the following command to start the storage pool. Optionally you can use pool-autostart to automatically start the pool upon the system boot and use pool-autostart –disable to remove it.

```
virsh pool-start myHomePool
```
If you choose to stop the storage pool from running, you can do this in arclight or by using the pool-destroy option.

```
virsh pool-destroy myHomePool
```
Lastly if you decide to remove the storage pool you can undefine it. This will leave the directory intact on the operating system, just removing it from the list of storage pools. Again, this can be done in arclight or by using the pool-undefine option in the terminal.

```
virsh pool-undefine myHomePool
```


<!-- ISO images for KVM machines**************************************************************************************** -->

When getting started with KVM virtual machines, one common question is how do I get ISO image files used to install the operating systems in the virtual machines. The default location that Libvirt uses as a storage pool for KVM virtual machines is the /var/lib/libvirt/images/ directory. You will need to download the ISO files using a command such as wget. Find the URL of the ISO from from the vendor, for example http://releases.ubuntu.com/18.04.1/ubuntu-18.04.1-live-server-amd64.iso.

You will need to switch your user account to the root user:
Navigate to the /var/lib/libvirt/images/ directory:

```
cd /var/lib/libvirt/images/
```
Use wget to download the file:

``` 
wget http://releases.ubuntu.com/18.04.1/ubuntu-18.04.1-live-server-amd64.iso
```

The ISO file will now show up in arclight.


<!-- Encrypting arclight with Let’s Encrypt****************************************************************************** -->

As a security recommendation, it is always a good practice to encrypt the data sent across the Internet. You can encrypt both your arclight connection as well as the VNC console connection to your virtual machines.

With the Apache web server on Ubuntu you can enable HTTPS traffic using the following command:
```
sudo a2enmod ssl
```
If you are using a domain name, you can use a Certificate Authority such as Let’s Encrypt to create a free validated SSL certificate. To get started we will need to create an Apache site configuration file for your domain. I will using the domain server1.arclight.org for this example. The new config file should end with the .conf extension and be located in the /etc/apache2/sites-available/ directory. To create a new file for your domain use the following command, and be sure to change the domain name:
```
sudo nano /etc/apache2/sites-available/server1.arclight.org.conf
```
We will just be adding just the minimum information in the configuration file. The first line below <VirtualHost *:80> tells Apache that this configuration file will be used for HTTP traffic. When we configure Let’s Encrypt, the HTTPS  connection (port 443) will be configured automatically.  The second line ServerName server1.arclight.org tells Apache what domain name it should be listening for to apply this configuration. The third line DocumentRoot /var/www/html/arclight/ indicates the root location of the web site files and that should be the filepath for your files.

<VirtualHost *:80>
ServerName server1.arclight.org
DocumentRoot /var/www/html/arclight/
</VirtualHost>

Once you add the above information to the configuration file and save it, we will then need to enable the configuration file in Apache using the a2ensite command. To do that run the following command, be sure to use your domain name:
```
sudo a2ensite server1.arclight.org
```
When Apache is only used for the arclight it would be a good idea to disable the default configuration file that comes with the install of Apache. To do that use the command:
```
sudo a2dissite 000-default.conf
```
You will need to restart/reload the Apache web server to apply the configuration changes. Use the following command:
```
sudo systemctl reload apache2
```
To automate the Let’s Encrypt certificate using Apache we will need to install the python-certbot-apache package. Use the following command:
```
sudo apt install python-certbot-apache
```
To create the SSL Certificate and Apache configuration file run the following command, changing your domain name. You will be asked for an email address and you will be given an option to either redirect all traffic to the HTTPS protocol or not.
```
sudo certbot --apache -d server1.arclight.org
```
Now login to your Arclight Dashboard. Go to the settings page and add the location of the Let’s Encrypt certificate file and key file and submit your changes. Below is the location created for server1.arclight.org

Certificate file: /etc/letsencrypt/live/server1.arclight.org/fullchain.pem
Key file: /etc/letsencrypt/live/server1.arclight.org/privkey.pem

The permissions for the certificates are tied to the root user. There will need to be a permission change on the /etc/letsencrypt/live folder as well as /etc/letsencrypt/archive. We can change the permission to 755 (rwxr-xr-x) to allow the VMDashoard to be able to read the information. Run the following commands:
```
sudo chmod 755 /etc/letsencrypt/live
```
```
sudo chmod 755 /etc/letsencrypt/archive
```
You can either decide to restart your server or restart the python process tied to noVNC to apply the certificate and key files. If you decide to restart the service you should be able to determine which process id (PID) is using port 6080. Use the following command:
```
sudo netstat -tulpn | grep 6080
```
Then after determining the PID number, kill the process. For example, if it was PID 1386, I would use the command:
```
sudo kill 1386
```
Now logout and login to the arclight to restart the VNC connection and the new certificate should be applied.



<!-- Encrypting arclight with a self-signed cert******************************************************************************* -->

As a security recommendation, it is always a good practice to encrypt your the data sent across the Internet. You can encrypt both your arclight connection as well as the VNC connection to your virtual machines.

With the Apache web server on Ubuntu you can enable https traffic using the following command:
```
sudo a2enmod ssl
```
Ubuntu has a configuration already setup to be used with a self-signed certificate. It can be activated by using the following command:
```
sudo a2ensite default-ssl.conf
```
You will need to restart/reload the Apache web server to apply the SSL connection. Use the following command:
```
sudo systemctl restart apache2
```
The VNC connection will default to using the protocol of you web connection. If you wish to use https with VNC you will need to create a certificate. By default, the noVNC app that comes with arclight looks for a cert called self.pem in the /etc/ssl/ directory.

To create the certificate for the VNC connection navigate to the /etc/ssl/ directory.
```
cd /etc/ssl/
```
Create the certificate by using the following command:
```
sudo openssl req -x509 -days 365 -new -nodes -out self.pem -keyout self.pem
```
Now change the permissions of the self.pem file
```
sudo chmod 755 self.pem
```
If you have already used arclight, you will need to kill the existing VNC process. To determine the process to kill use netstat and determine the process number that is listening on port 6080.
```
sudo netstat -tulpn | grep 6080
```
Now kill the process. For example if the process was numbered 29226, you would kill it using the command:
```
sudo kill 29226
```
Now when you log into arclight, the VNC software will use the self-signed cert. Because it is self-signed your browser will not trust it. To trust the certification visit your URL:6080 and click the Advanced button on the screen. For example, if I were using 192.168.1.2 to view the web interface I would use https://192.168.1.2:6080.
