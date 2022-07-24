<?php header('Location: index.php'); ?>
<hr>
<h4>Changelog</h4>
[2.0.0] - 01.Aug.2022
*Official Stable release of the Arclight:
Tested with Ubuntu 18.04 LTS on IBM Cloud bare metal https://cloud.ibm.com/gen1/infrastructure/provision/bm)
Tested with Ubuntu 20.04 LTS on AWS C5n Bare Metal Instance

*Now support Ubuntu major versions like 18.04 and 20.04
*Added Bare Metal provisioning support
*Users can now ssh host and other remote servers securely with arclight's in-built SSH client
*Added Modules to the dashboard
*Implementation of Arclight's own api which is used to communicate with the Arclight server and the database
*Added new signup and signin page for user validation and authentication.
*Added all-in-one certificate management for the Arclight server
*Fixed issue where noVNC is unable to get certificate paths other than in /etc/ssl/self.pem
*Added custom tailwind module for future use
*Arclight is now using MongoDB as its database which is more scalable and faster than MySQL
*Added startup bash script to automatically install all the dependencies and linux distro packages required for Arclight to work
*Redesigned the initial setup page of Arclight to a single configuration page with less options
*Minimized the number of pages by using single page for all the settings
*Modified sidebar menu, structured for future use of multiple host machines (clustering)
*Added scrollable events log that will list all the events that occurred for a virtual machine.
*Improved performance of windows instances.
*fixed Windows 10 CPU bug where it ignores extra CPU cores.
*Added Arc API health checker.
*Improved how virtual machines are created by adding better error notifications, breaking the process into several steps of creation to handle errors better.
*Improved updating process. Now instead of git pull, fetching the origin and reseting it. This will override any changes to local files.
*Modified user sidebar navigation to include just Host, Virtual Machines, Storage, and Networking, simplifying the menu
*Creating a new vm, storage pool, storage volume, and network fall on their respective pages
*Improved layout of the noVNC connection on domain-single.php
*Hid the XML on the domain-single.php page until user wants to edit it. Preventing accidental changes to guest
*Improved layout of the Host page with additional Information such as uptime and libvirt version
*Improved layout of the instance provisioning modal with extra Advanced CPU options
*Automactically notify users of any updates to Arclight with a notification bar at the top of the page.
*Increased the number of error notifications that exist
*Changes to the HTML/CSS theme have improved scrollbar apperance and better use of web page realestate
*The noVNC connection is loaded from an authenticated web page.
*The tokens for the noVNC connection are now 100 character random strings, which change everytime a VM page is loaded (domain-single.php)
*The console preview on the domain-single.php is now a live noVNC connection to the machine rather than a static image
*

