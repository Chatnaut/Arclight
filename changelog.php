<?php header('Location: index.php'); ?>
<hr>
<h4>Changelog</h4>
[1.0.0] - 02.Dec.2021
*Official Stable release of the Arclight (Tested with Ubuntu 18.04 LTS on IBM Cloud bare metal https://cloud.ibm.com/gen1/infrastructure/provision/bm)
*Added Modules to the dashboard
*Added GPU Pass-through and vGPU (requires Nvidia vGPU License) with Hotswapping support 
*Save and Restore GPU configuration support
*Rewriting of the theme, using bootstrap dashboard example as base theme
*Redesigned the initial setup page of Arclight to a single configuration page
*Fixed CentOS host issue with Windows Server 2016 guest giving a BSOD because of CPU settings
*Rewriting of the theme, using bootstrap dashboard example as base theme
*Redesigned the initial setup page of Arclight to a single configuration page
*Minimized the number of pages by using bootstrap modals for configuration tasks
*Modified sidebar menu, structured for future use of multiple host machines (clustering)
*Added events log that will list the last three events that occurred for a virtual machine. Will need to initially logout and login to start recording events
*Improved how virtual machines are created by adding better error notifications, breaking the process into several steps of creation to handle errors 
*Modified XML stucture of a new Virtual Machine to no longer define CPU features, rather allowing for default libvirt configuration on cpus
*Added support for the macvtap bridge creation and removed direct connection to network interfaces. By creating a macvtap network, the virtual machine can have access to the same network as the host
*Added support for creating virtual machines and network adapters from XML directly
*Fixed CSS for dropdown select options for Chrome on Windows platform
*Made the Console window in domain-single.php clickable to tab with console
*Stripped default cpu options from domain-create.php. Some of the options caused vm to now boot up on certain host hardware configurations. Now relies on default libvirt settings.
*Added support for Arclight on RedHat/CentOS
*Improved XML Definitions of VMs
*Fixed login redirect link on /pages/vnc.php to direct to correct url
*Improved appearance of radio buttons in the dark-theme
*Improved updating process. Now instead of git pull, fetching the origin and reseting it. This will override any changes to local files.
*Added popovers to display additional information. Used in /pages/config/settings.php
*Fixed bug on Host page that shows incorrect CPU and Memory stats
*Added new option for SSL Key file in settings. Allowing uses to set key different than cert.
*Added settings page. Users can now change location of the SSL certificate for noVNC connection
*Added user preferences to change theme. Added a dark theme
*The default theme now has a black navigation menu. The entire panel theme is now based on the MIT Licensed Material Dark theme from creative-tim.com
*Modified user sidebar navigation to include just Host, Virtual Machines, Storage, and Networking, simplifying the menu
*Creating a new vm, storage pool, storage volume, and network fall on their respective pages
*Improved layout of the noVNC connection on domain-single.php
*Hid the XML on the domain-single.php page until user wants to edit it. Preventing accidental changes to guest
*Improved layout of the Host page Information
*Increased the number of error notifications that exist
*Changes to the HTML/CSS theme have improved scrollbar apperance and better use of web page realestate
*The noVNC connection is loaded from an authenticated web page.
*The tokens for the noVNC connection are now 100 character random strings, which change everytime a VM page is loaded (domain-single.php)
*The console preview on the domain-single.php is now a live noVNC connection to the machine rather than a static image
*removed unnessary code from pages/footer.php Started adding support for mulitple languages.
*Changed the location of the noVNC default certificate to /etc/ssl/self.pem
