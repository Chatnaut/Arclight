<?php
    // If the SESSION has not been started, start it now
    if (!isset($_SESSION)) {
        session_start();
    }

    // If there is no username, then we need to send them to the login
    if (!isset($_SESSION['username'])){
    header('Location: ../login.php');
    }

    // This function is used to prevent any problems with user form input
    function clean_input($data) {
        $data = trim($data); //remove spaces at the beginning and end of string
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = str_replace(' ','',$data); //remove any spaces within the string
        $data = filter_var($data, FILTER_SANITIZE_STRING);
        return $data;
    }
    $userid = $_SESSION['userid'];
    $secure = $_GET['authorize'];

    $authentication = "SELECT authkey from arclight_vm WHERE userid = '$userid'";
    $showdata = mysqli_query($conn,$authentication);
    // We are now going to grab any GET/POST data and put in in SESSION data, then clear it.
    // This will prevent duplicatig actions when page is reloaded.
    if($showdata == $secure){
      
      if (isset($_GET['action']) || isset($_GET['dev']) || isset($_GET['mac']) || isset($_GET['snapshot'])) {
      // if($_SESSION['userid'] = $userid))


        $_SESSION['action'] = $_GET['action'];
        $_SESSION['dev'] = $_GET['dev'];
        $_SESSION['mac'] = $_GET['mac'];
        $_SESSION['snapshot'] = $_GET['snapshot'];
        $_SESSION['xmldesc'] = $_POST['xmldesc'];

        $_SESSION['source_file'] = $_POST['source_file']; //Used in both add-storage and add-iso

        $_SESSION['new_volume_name'] = clean_input($_POST['new_volume_name']); //the new name of the volume disk image
        $_SESSION['new_volume_size'] = $_POST['new_volume_size']; //number used for volume size
        $_SESSION['new_unit'] = $_POST['new_unit']; //determines MiB or GiB
        $_SESSION['new_volume_size'] = $_POST['new_volume_size'];
        $_SESSION['new_driver_type'] = $_POST['new_driver_type']; //qcow2 or raw
        $_SESSION['new_target_bus'] = $_POST['new_target_bus']; //virtio, ide, sata, or scsi - used when adding disk to domain
        $_SESSION['existing_driver_type'] = $_POST['existing_driver_type']; //qcow2 or raw
        $_SESSION['existing_target_bus'] = $_POST['existing_target_bus']; //virtio, ide, sata, or scsi

        $_SESSION['mac_address'] = $_POST['mac_address'];
        $_SESSION['source_network'] = $_POST['source_network'];
        $_SESSION['model_type'] = $_POST['model_type'];

        header("Location: ".$_SERVER['PHP_SELF']."?uuid=".$_GET['uuid']);
        exit;
    }
  }
  

    // Add the header information
    require('../header.php');

    function RandomString($length) {
        $keys = array_merge(range(0,9), range('a', 'z'));
        $key = "";
        for($i=0; $i < $length; $i++) {
            $key .= $keys[mt_rand(0, count($keys) - 1)];
        }
        return $key;
    }

    //Set variables
    $randomString = RandomString(100);
    $uuid = $_GET['uuid'];
    $authorize = $_GET['authorize'];
    $domName = $lv->domain_get_name_by_uuid($uuid);
    $dom = $lv->get_domain_object($domName);
    $protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
    $url = $protocol . $_SERVER['HTTP_HOST'];
    $page = basename($_SERVER['PHP_SELF']);
    $action = $_SESSION['action'];
    $domXML = new SimpleXMLElement($lv->domain_get_xml($domName));
    $autostart = ($lv->domain_get_autostart($dom)) ? "yes" : "no";
    $userid = $_SESSION['userid'];
    $currenttime = date("Y-m-d H:i:s");
    $domain_uuid = $_GET['uuid'];
    $hostXML = new SimpleXMLElement($lv->get_node_device_xml("computer", false));
    $host_uuid = $hostXML->capability->hardware->uuid;


    // Domain Actions
    if ($action == 'domain-start') {
    $notification = $lv->domain_start($domName) ? "" : 'Error while starting domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "guest powered on";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-pause') {
    $notification = $lv->domain_suspend($domName) ? "" : 'Error while pausing domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "guest paused";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-resume') {
    $notification = $lv->domain_resume($domName) ? "" : 'Error while resuming domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "guest resumed";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-stop') {
    $notification = $lv->domain_shutdown($domName) ? "" : 'Error while stopping domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "shutdown command sent";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-destroy') {
    $notification = $lv->domain_destroy($domName) ? "" : 'Error while destroying domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "guest powered off";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-delete') {
    $notification = $lv->domain_undefine($domName) ? "" : 'Error while deleting domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "guest deleted";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql = "DELETE FROM arclight_vm WHERE userid = '$userid' AND uuid = '$uuid'";
    $sql_action = $conn->query($sql);
    if (!$lv->domain_get_name_by_uuid($uuid))
        header('Location: domain-list-user.php');
    }


    //Disk Actions
    if ($action == 'domain-disk-remove') {
      $dev = $_SESSION['dev'];
      $path = $domXML->xpath('//disk'); //Used to determine the number of disk devices
      for ($i = 0; $i < sizeof($path); $i++) {
          if ($domXML->devices->disk[$i]->target[dev] == $dev)
          unset($domXML->devices->disk[$i]);
          $newXML = $domXML->asXML();
          $newXML = str_replace('<?xml version="1.0"?>', '', $newXML);
          $notification = $lv->domain_change_xml($domName, $newXML) ? "" : 'Cannot remove disk: '.$lv->get_last_error();
          $description = ($notification) ? $notification : "removed storage volume";
          $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
          $sql_action = $conn->query($sql);
      }
    }

    if ($action == 'add-storage-volume') {
        $disk_type = "file";
        $disk_device = "disk";
        $driver_name = "qemu"; //not used
        $source_file = $_SESSION['source_file']; //determines if none, new, or existing disk is added

        if ($source_file == "new") {
            $pool = "default"; //using the default storage pool to store images
            $volume_image_name = clean_input($_SESSION['new_volume_name']); //the new name of the volume disk image
            $volume_capacity = $_SESSION['new_volume_size']; //number used for volume size
            $unit = $_SESSION['new_unit']; //determines MiB or GiB
            $volume_size = $_SESSION['new_volume_size'];
            $driver_type = $_SESSION['new_driver_type']; //qcow2 or raw
            //Create the new disk now
            $new_disk = $lv->storagevolume_create($pool, $volume_image_name, $volume_capacity.$unit, $volume_size.$unit, $driver_type);
            $target_bus = $_SESSION['new_target_bus']; //virtio, ide, sata, or scsi - used when adding disk to domain
      
        } elseif ($source_file == "none") {
            //
        } else {
            $driver_type = $_SESSION['existing_driver_type']; //qcow2 or raw
            $target_bus = $_SESSION['existing_target_bus']; //virtio, ide, sata, or scsi
        }
      
        $target_dev = ""; //changed to an autoincremting option below.
    
        //If $target_bus type is virtio then we need to determine highest assigned value of drive, ex. vda, vdb, vdc...
        if ($target_bus == "virtio"){
            $virtio_array = array();
            for ($i = 'a'; $i < 'z'; $i++)
                $virtio_array[] = "vd" . $i;
        
            $tmp = libvirt_domain_get_disk_devices($dom);
            $result = array_intersect($virtio_array,$tmp);
            if (count($result) > 0) {
                $highestresult = max($result);
                $target_dev = ++$highestresult;
            } else {
                $target_dev = "vda";
            }
        }
      
        //If $target_bus type is ide then we need to determine highest assigned value of drive, ex. hda, hdb, hdc...
        if ($target_bus == "ide"){
            $ide_array = array();
            for ($i = 'a'; $i < 'z'; $i++)
                $ide_array[] = "hd" . $i;
        
            $tmp = libvirt_domain_get_disk_devices($dom);
            $result = array_intersect($ide_array,$tmp);
            if (count($result) > 0 ) {
                $highestresult = max($result);
                $target_dev = ++$highestresult;
            } else {
                $target_dev = "hda";
            }
        }
      
        //If $target_bus type is scsi or sata then we need to determine highest assigned value of drive, ex. sda, sdb, sdc...
        if ($target_bus == "sata" || $target_bus == "scsi"){
            $sd_array = array();
            for ($i = 'a'; $i < 'z'; $i++)
                $sd_array[] = "sd" . $i;
        
            $tmp = libvirt_domain_get_disk_devices($dom);
            $result = array_intersect($sd_array,$tmp);
            if (count($result) > 0 ) {
                $highestresult = max($result);
                $target_dev = ++$highestresult;
            } else {
                $target_dev = "sda";
            }
        }
      
        //add the new disk to domain if selected
        if ($source_file == "new") {
            $img = libvirt_storagevolume_get_path($new_disk);
            $dev = $target_dev;
            $typ = $target_bus;
            $driver = $driver_type;
            $notification = $lv->domain_disk_add($dom, $img, $dev, $typ, $driver) ? "" : "Cannot add volume to the guest: ".$lv->get_last_error();
            $description = ($notification) ? $notification : "added storage volume";
            $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
            $sql_action = $conn->query($sql);
        }
    
        //add an existing disk to domain if selected
        if ($source_file != "new") {
            $notification = $lv->domain_disk_add($dom, $source_file, $target_dev, $target_bus, $driver_type) ? "" : "Cannot add volume to the guest: ".$lv->get_last_error();
            $description = ($notification) ? $notification : "added storage volume";
            $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
            $sql_action = $conn->query($sql);
        }
    }

    //Optical Storage Actions
    if ($action == 'add-optical-storage') {
      $disk_type = "file";
      $disk_device = "cdrom";
      $driver_name = "qemu"; //not used
      $source_file = $_SESSION['source_file']; //determines if none, new, or existing disk is added
      $driver_type = "raw";
      $target_bus = "ide"; //change to all user to select type in the future

      $target_dev = ""; //changed to an autoincremting option below.

      //If $target_bus type is ide then we need to determine highest assigned value of drive, ex. hda, hdb, hdc...
      if ($target_bus == "ide"){
        $ide_array = array();
        for ($i = 'a'; $i < 'z'; $i++)
          $ide_array[] = "hd" . $i;

        $tmp = libvirt_domain_get_disk_devices($dom);
        $result = array_intersect($ide_array,$tmp);
        if (count($result) > 0 ) {
          $highestresult = max($result);
          $target_dev = ++$highestresult;
        } else {
          $target_dev = "hda";
        }
      }

      //If $target_bus type is scsi or sata then we need to determine highest assigned value of drive, ex. sda, sdb, sdc...
      if ($target_bus == "sata" || $target_bus == "scsi"){
        $sd_array = array();
        for ($i = 'a'; $i < 'z'; $i++)
          $sd_array[] = "sd" . $i;

        $tmp = libvirt_domain_get_disk_devices($dom);
        $result = array_intersect($sd_array,$tmp);
        if (count($result) > 0 ) {
          $highestresult = max($result);
          $target_dev = ++$highestresult;
        } else {
          $target_dev = "sda";
        }
      }

      //add a new cdrom XML
      $disk = $domXML->devices->addChild('disk');
      $disk->addAttribute('type','file');
      $disk->addAttribute('device','cdrom');

      $driver = $disk->addChild('driver');
      $driver->addAttribute('name','qemu');
      $driver->addAttribute('type','raw');

      $source = $disk->addChild('source');
      $source->addAttribute('file',$source_file);

      $target = $disk->addChild('target');
      $target->addAttribute('dev',$target_dev);
      $target->addAttribute('bus',$target_bus);

      $newXML = $domXML->asXML();
      $newXML = str_replace('<?xml version="1.0"?>', '', $newXML);

      $notification = $lv->domain_change_xml($domName, $newXML) ? "" : "Cannot add ISO to the guest: ".$lv->get_last_error();
      $description = ($notification) ? $notification : "added optical storage";
      $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
      $sql_action = $conn->query($sql);
        
    }


    //Network Actions
    if ($action == 'domain-nic-remove') {
      $mac = base64_decode($_SESSION['mac']);
      //Using XML to remove network, $notification = $lv->domain_nic_remove($domName, $mac) was not working correctly
      $path = $domXML->xpath('//interface');
      for ($i = 0; $i < sizeof($path); $i++) {
          if ($domXML->devices->interface[$i]->mac[address] == $mac)
          unset($domXML->devices->interface[$i]);
          $newXML = $domXML->asXML();
          $newXML = str_replace('<?xml version="1.0"?>', '', $newXML);
          $notification = $lv->domain_change_xml($domName, $newXML) ? "" : 'Cannot remove network interface: '.$lv->get_last_error();
          $description = ($notification) ? $notification : "removed network interface";
          $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
          $sql_action = $conn->query($sql);
      }
    }

    if ($action == "add-network-adapter") {
      $mac_address = $_SESSION['mac_address'];
      $source_network = $_SESSION['source_network'];
      $model_type = $_SESSION['model_type'];
      
      $notification = $lv->domain_nic_add($domName, $mac_address, $source_network, $model_type) ? "" : "Cannot add network to the guest: ".$lv->get_last_error();
      $description = ($notification) ? $notification : "added network interface";
      $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
      $sql_action = $conn->query($sql);
    
    }


    //Snapshot Actions
    if ($action == 'domain-snapshot-create') {
    $notification = $lv->domain_snapshot_create($domName) ? "Snapshot for $domName successfully created" : 'Error while taking snapshot of domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "created snapshot";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-snapshot-delete') {
    $snapshot = $_SESSION['snapshot'];
    $notification = $lv->domain_snapshot_delete($domName, $snapshot) ? "" : 'Error while deleting snapshot of domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "deleted snapshot";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-snapshot-revert') {
    $snapshot = $_SESSION['snapshot'];
    $notification = $lv->domain_snapshot_revert($domName, $snapshot) ? "Snapshot $snapshot for $domName successfully applied" : 'Error while reverting snapshot of domain: '.$lv->get_last_error();
    $description = ($notification) ? $notification : "reverted to previous snapshot";
    $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
    $sql_action = $conn->query($sql);
    }

    if ($action == 'domain-snapshot-xml') {
    $snapshot = $_SESSION['snapshot'];
    $snapshotxml = $lv->domain_snapshot_get_xml($domName, $snapshot);
    //Parsing the snapshot XML file - in Ubuntu requires the php-xml package
    $xml = simplexml_load_string($snapshotxml);
    //Alternative way to parse
    //$xml = new SimpleXMLElement($snapshotxml);
    }


    //Domain XML Changes
    if ($action == 'domain-edit') {
    $xml = $_SESSION['xmldesc'];
        $notification = $lv->domain_change_xml($domName, $xml) ? "XML for $domName has been updated" : 'Error changing domain XML: '.$lv->get_last_error();
        $domName = $lv->domain_get_name_by_uuid($uuid); //If the name is changed in XML will need to get it again
        $description = $notification;
        $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
        $sql_action = $conn->query($sql);

    }


    //Domain AutoStart Change
    if ($action == 'domain-set-autostart') {
        $val = ($autostart == "yes") ? null : 1; // null disables autostart, 1 enables it
        $notification = $lv->domain_set_autostart($dom, $val) ? "" : 'Error changing domain autostart: '.$lv->get_last_error();
        $autostart = ($lv->domain_get_autostart($dom)) ? "yes" : "no"; //Check status again to display status in general informaion
        $description = ($notification) ? $notification : "autostart value changed";
        $sql = "INSERT INTO arclight_events (description, host_uuid, domain_uuid, userid, date) VALUES (\"$description\", '$host_uuid', '$domain_uuid', '$userid', '$currenttime')";
        $sql_action = $conn->query($sql);
    }


    //get info, mem, cpu, state, id, arch, and vnc after actions to reflect any changes to domain
    //Didn't use $info = $lv->domain_get_info($dom); because of caches state.
    $info = libvirt_domain_get_info($dom);
    $mem = number_format($info['memory'] / 1048576, 2, '.', ' ').' GB';
    $cpu = $info['nrVirtCpu'];
    $state = $lv->domain_state_translate($info['state']);
    $id = $lv->domain_get_id($dom);
    $arch = $lv->domain_get_arch($dom);
    $vnc = $lv->domain_get_vnc_port($dom);

    if (!$id)
    $id = 'N/A';
    if ($vnc <= 0)
        $vnc = 'N/A';

    require('../navbar.php');


    // Setting up VNC connection information. tokens.list needs to have www-data ownership or 777 permissions
    $liststring = "";
    $phpinfo = '<?php header(\'Location: index.php\'); ?>' . "\n";
    $listarray = $lv->get_domains();
    foreach ($listarray as $listname) {
    $listdom = $lv->get_domain_object($listname);
    $listinfo = libvirt_domain_get_info($listdom);
    //Don't use $lv->domain_get_info($listdom) because the state is cached and caused delayed state s$
    $liststate = $lv->domain_state_translate($listinfo['state']);
    //Will only generate string for current VM
    if ($liststate == "running" && $uuid == libvirt_domain_get_uuid_string($listdom)) {
        //$listdomuuid = libvirt_domain_get_uuid_string($listdom);
        $listvnc = $lv->domain_get_vnc_port($listdom);
        //$liststring = $liststring . $listdomuuid . ": " . "localhost:" . $listvnc . "\n";
        $liststring = $randomString . ": " . "localhost:" . $listvnc . "\n";
    }
    }
    $filestring = $phpinfo . $liststring;
    $listfile = "../../tokens.php";
    $list = file_put_contents($listfile, $filestring);


    //Remove session variables so that if page reloads it will not perform actions again
    unset($_SESSION['action']);
    unset($_SESSION['dev']);
    unset($_SESSION['mac']);
    unset($_SESSION['snapshot']);
    unset($_SESSION['xmldesc']);

    unset($_SESSION['source_file']);

    unset($_SESSION['new_volume_name']);
    unset($_SESSION['new_volume_size']);
    unset($_SESSION['new_unit']);
    unset($_SESSION['new_volume_size']);
    unset($_SESSION['new_driver_type']);
    unset($_SESSION['new_target_bus']);
    unset($_SESSION['existing_target_bus']);

    unset($_SESSION['mac_address']);
    unset($_SESSION['source_network']);
    unset($_SESSION['model_type']);

?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
            <h3 class="h3"><?php echo htmlentities($domName); ?></h3>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group mr-2">
                <?php  if ($state == "running") { ?>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.open('../vnc.php?token=<?php echo $randomString; ?>','_blank')">Console</button>
                        <?php } ?>

                        <?php if ($state == "shutoff") { ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='?action=domain-start&amp;uuid=<?php echo $uuid; ?>'">Power On</button>
                        <?php } ?>

                        <?php  if ($state == "running") { ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='?action=domain-stop&amp;uuid=<?php echo $uuid; ?>'">Shutdown</button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='?action=domain-pause&amp;uuid=<?php echo $uuid; ?>'">Pause</button>
                        <?php } ?>

                        <?php  if ($state == "paused") { ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='?action=domain-resume&amp;uuid=<?php echo $uuid; ?>'">Resume</button>
                        <?php } ?>

                        <?php  if ($state != "shutoff") { ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='?action=domain-destroy&amp;uuid=<?php echo $uuid; ?>'">Poweroff</button>
                        <?php } ?>

                        <?php  if ($state == "shutoff") { ?>
                            <button class="btn btn-sm btn-outline-secondary" data-href="?action=domain-delete&amp;uuid=<?php echo $uuid; ?>" data-filename="<?php echo $domName ?>" data-toggle="modal" data-target="#confirm-delete-modal" href="#confirm-delete-modal"> Delete</button>
                        <?php } ?>
                </div>
            </div>
            </div>

            <div class="content">

                <div class="row">

                    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12  ">                  
                        <?php /*
                            if ($state == "running") {
                            //Lets get the vnc console preview of the running domain
                            ?>
                            <div style="position:relative; width: 295px; height: 221px; margin-left:0; margin-right:auto;">
                                <iframe src="<?php echo $url; ?>:6080/vnc_screen.html?view_only=true&path=&scale=true&token=<?php echo $randomString ?>" style="width: 100%; height: 100%; border: none;"></iframe>
                                <a href="../vnc.php?token=<?php echo $randomString; ?>" target="_blank"  style="position:absolute; top:0; left:0; display:inline-block; width:100%; height:100%; z-index:99;"></a>
                            </div>
                            <?php
                            } else if ($state == "paused") {
                            echo "<img src='../../assets/img/paused.png' width='295px' height='221px' >";
                            } else {
                            echo "<img src='../../assets/img/shutdown.png' width='295px' height='221px' >";
                            } */
                        ?>
                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                            <div class="card-body">
                        <?php
                            if ($state == "running") {
                            //Lets get the vnc console preview of the running domain
                            ?>
                            <div class="embed-responsive embed-responsive-4by3">
                                <iframe  scrolling="no" class="embed-responsive-item" src="<?php echo $url; ?>:6080/vnc_screen.html?view_only=true&path=&scale=true&token=<?php echo $randomString ?>"></iframe>
                                <a href="../vnc.php?token=<?php echo $randomString; ?>" target="_blank"  style="width:100%; height:100%; top:0; left:0; position:absolute; display:inline-block; z-index:99;"></a>
                            </div>
                            <?php
                            } else if ($state == "paused") {
                            echo "<img src='../../assets/img/paused.png' width='100%' >";
                            } else {
                            echo "<img src='../../assets/img/shutdown.png' width='100%' >";
                            }
                        ?>
                        </div>
                        </div>

                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                            <div class="card-body">
                                <?php
                                    echo "<strong>Type: </strong>" . $lv->get_domain_type($domName) . "<br />";
                                    echo "<strong>Emulator: </strong>" . $lv->get_domain_emulator($domName) . "<br />";
                                    echo "<strong>Memory: </strong>" . $mem . "<br />";
                                    echo "<strong>vCPUs: </strong>" . $cpu . "<br />";
                                    echo "<strong>State: </strong>" . $state . "<br />";
                                    echo "<strong>Architecture: </strong>" . $arch . "<br />";
                                    echo "<strong>ID: </strong>" . $id . "<br />";
                                    echo "<strong>VNC Port: </strong>" . $vnc . "<br />";
                                    echo "<strong>AutoStart: </strong> <a href=\"?action=domain-set-autostart&amp;uuid=" . $uuid . "\" target=\"_self\" >" . $autostart . "</a> <br />";
                                    echo "<strong>XML: </strong>";
                                    if ($state == "shutoff") {
                                        echo "<a href=\"#xml-modal\" data-toggle=\"modal\" data-target=\"#xml-modal\"> Edit</a>";
                                    } else {
                                        echo "<a href=\"#xml-modal\" data-toggle=\"modal\" data-target=\"#xml-modal\"> View</a>";
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                          <div class="card-header">
                            <span class="card-title">Event Log</span>
                          </div>
                          <div class="card-body">
                                <?php
                                echo "<div class='table-responsive'>" .
                                "<table class='table'>" .
                                    "<thead>" .
                                        "<tr>" .
                                            "<th>Time</th>" .
                                            "<th>Event</th>" .
                                        "</tr>" .
                                    "</thead>" .
                                    "<tbody>";

                                $sql = "SELECT * FROM arclight_events WHERE domain_uuid = '$uuid' ORDER BY eventid DESC LIMIT 3";
                                $result = $conn->query($sql);
                              
                                foreach($result as $row){
                                  echo "<tr>";
                                  echo "<td>" . $row['date'] . "</td>";
                                  echo "<td>" . $row['description'] . "</td>";
                                  echo "</tr>";
                                }
                               
                                echo "</tbody></table></div>"; 
                                ?>
                            </div>
                        </div>

                        

                    </div> <!-- End Column -->
                

                    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                            <div class="card-header">
                                <span class="card-title">Storage Volumes</span>
                                <?php 
                                    if ($state == "shutoff"){
                                        echo "<span style=\"float: right\">";
                                        echo "<i data-feather=\"plus\"> </i>";
                                        echo " <a href=\"#add-storage-volume-modal\" data-toggle=\"modal\" data-target=\"#add-storage-volume-modal\">Add Storage Volume</a>";
                                        echo "<br /> <br />";
                                        echo "</span>";
                                    } 
                                ?>
                            </div>
                            <div class="card-body">
                                <?php
                                    /* Disk information */
                                    $tmp = $lv->get_disk_stats($domName);
                                    if (!empty($tmp)) {
                                    echo "<div class='table-responsive'>" .
                                        "<table class='table'>" .
                                            "<thead>" .
                                                "<tr>" .
                                                    "<th>Volume</th>" .
                                                    "<th>Driver</th>" .
                                                    "<th>Device</th>" .
                                                    "<th>Disk capacity</th>" .
                                                    "<th>Disk allocation</th>" .
                                                    "<th>Physical size</th>" .
                                                    "<th>Actions</th>" .
                                                "</tr>" .
                                            "</thead>" .
                                            "<tbody>";

                                    for ($i = 0; $i < sizeof($tmp); $i++) {
                                        $capacity = $lv->format_size($tmp[$i]['capacity'], 2);
                                        $allocation = $lv->format_size($tmp[$i]['allocation'], 2);
                                        $physical = $lv->format_size($tmp[$i]['physical'], 2);
                                        $dev = (array_key_exists('file', $tmp[$i])) ? $tmp[$i]['file'] : $tmp[$i]['partition'];
                                        $device = $tmp[$i]['device'];
                                        echo "<tr>" .
                                        "<td>".htmlentities(basename($dev))."</td>" .
                                            "<td>{$tmp[$i]['type']}</td>" .
                                            "<td>{$tmp[$i]['device']}</td>" .
                                            "<td>$capacity</td>" .
                                            "<td>$allocation</td>" .
                                            "<td>$physical</td>" .
                                            "<td>" .
                                            "<a title='Remove' href=\"?action=domain-disk-remove&amp;dev=$device&amp;uuid=$uuid\">Remove</a>" .
                                            "</td>" .
                                            "</tr>";
                                    }
                                    echo "</tbody></table></div>";
                                    } else {
                                    echo "<hr><p>Guest does not have any disk devices</p>";
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                            <div class="card-header">
                                <span class="card-title">Optical Storage</span>
                                <?php
                                    if ($state == "shutoff"){
                                      echo "<span style=\"float: right\">";
                                      echo "<i data-feather=\"plus\"> </i>";
                                      echo " <a href=\"#add-optical-storage-modal\" data-toggle=\"modal\" data-target=\"#add-optical-storage-modal\">Add Optical Storage</a>";
                                      echo "<br /> <br />";
                                      echo "</span>";
                                    } 
                                ?>
                            </div>
                            <div class="card-body">
                                <?php
                                    /* Optical device information */
                                    $path = $domXML->xpath('//disk');
                                    if (!empty($path)) {
                                    echo "<div class='table-responsive'>" .
                                        "<table class='table'>" .
                                            "<thead>" .
                                                "<tr>" .
                                                    "<th>ISO file</th>" .
                                                    "<th>Driver</th>" .
                                                    "<th>Device</th>" .
                                                    "<th>Bus</th>" .
                                                    "<th>Actions</th>" .
                                                "</tr>" .
                                            "</thead>" .
                                            "<tbody>";

                                    for ($i = 0; $i < sizeof($path); $i++) {
                                        //$disk_type = $domXML->devices->disk[$i][type];
                                        $disk_device = $domXML->devices->disk[$i][device];
                                        $disk_driver_name = $domXML->devices->disk[$i]->driver[name];
                                        //$disk_driver_type = $domXML->devices->disk[$i]->driver[type];
                                        $disk_source_file = $domXML->devices->disk[$i]->source[file];
                                        if (empty($disk_source_file)) {
                                        $disk_source_file = "empty";
                                        }
                                        $disk_target_dev = $domXML->devices->disk[$i]->target[dev];
                                        $disk_target_bus = $domXML->devices->disk[$i]->target[bus];

                                        if ($disk_device == "cdrom") {
                                        echo "<tr>" .
                                            "<td>" . htmlentities($disk_source_file) . "</td>" .
                                            "<td>$disk_driver_name</td>" .
                                            "<td>$disk_target_dev</td>" .
                                            "<td>$disk_target_bus</td>" .
                                            "<td>" .
                                            "<a title='Remove' href=\"?action=domain-disk-remove&amp;dev=$disk_target_dev&amp;uuid=$uuid\">Remove</a>" .
                                            "</td>" .
                                            "</tr>";
                                        }
                                    }
                                    echo "</tbody></table></div>";
                                    } else {
                                    echo '<hr><p>Guest does not have any optical devices</p>';
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                            <div class="card-header">
                                <span class="card-title">Network Interfaces </span>
                                <?php
                                    if ($state == "shutoff"){
                                      echo "<span style=\"float: right\">";
                                      echo "<i data-feather=\"plus\"> </i>";
                                      echo " <a href=\"#add-network-adapter-modal\" data-toggle=\"modal\" data-target=\"#add-network-adapter-modal\">Add Network Adapter</a>";
                                      echo "<br /> <br />";
                                      echo "</span>";
                                    } 
                                ?>
                            </div>
                            <div class="card-body">
                                <?php
                                    /* Network interface information */
                                    $domXML = new SimpleXMLElement($lv->domain_get_xml($domName)); //need to grab again in case new adapter is added
                                    $path = $domXML->xpath('//interface');
                                    if (!empty($path)) {
                                    echo "<div class='table-responsive'>" .
                                        "<table class='table'>" .
                                            "<thead>" .
                                                "<tr>" .
                                                    "<th>Type</th>" .
                                                    "<th>MAC Address</th>" .
                                                    "<th>Source</th>" .
                                                    "<th>Mode</th>" .
                                                    "<th>Model</th>" .
                                                    "<th>Actions</th>" .
                                                "</tr>" .
                                            "</thead>" .
                                            "<tbody>";

                                    for ($i = 0; $i < sizeof($path); $i++) {
                                        $interface_type = $domXML->devices->interface[$i][type];
                                        $interface_mac = $domXML->devices->interface[$i]->mac[address];
                                        $mac_encoded = base64_encode($interface_mac); //used to send via $_GET
                                        if ($interface_type == "network") {
                                        $source_network = $domXML->devices->interface[$i]->source[network];
                                        }
                                        if ($interface_type == "direct") {
                                        $source_dev = $domXML->devices->interface[$i]->source[dev];
                                        $source_mode = $domXML->devices->interface[$i]->source[mode];
                                        }
                                        $interface_model = $domXML->devices->interface[$i]->model[type];

                                        if ($interface_type == "network") {
                                        echo "<tr>" .
                                            "<td>$interface_type</td>" .
                                            "<td>$interface_mac</td>" .
                                            "<td>" . htmlentities($source_network) . "</td>" .
                                            "<td>nat</td>" .
                                            "<td>$interface_model</td>" .
                                            "<td>" .
                                            "<a href=\"?action=domain-nic-remove&amp;uuid=$uuid&amp;mac=$mac_encoded\">" .
                                            "Remove</a>" .
                                            "</td>" .
                                            "</tr>";
                                        }
                                        if ($interface_type == "direct") {
                                        echo "<tr>" .
                                            "<td>$interface_type</td>" .
                                            "<td>$interface_mac</td>" .
                                            "<td>$source_dev</td>" .
                                            "<td>$source_mode</td>" .
                                            "<td>$interface_model</td>" .
                                            "<td>" .
                                            "<a href=\"?action=domain-nic-remove&amp;uuid=$uuid&amp;mac=$mac_encoded\">" .
                                            "Remove</a>" .
                                            "</td>" .
                                            "</tr>";
                                        }
                                    }
                                    echo "</tbody></table></div>";
                                    } else {
                                    echo '<hr><p>Guest does not have any network devices</p>';
                                    }
                                ?>
                            </div>
                        </div>


                        <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">
                            <div class="card-header">
                                <span class="card-title">Snapshots </span>
                                <?php
                                    echo "<span style=\"float: right\">";
                                    echo "<i data-feather=\"plus\"> </i>";
                                    echo "<a href=\"?action=domain-snapshot-create&amp;uuid=$uuid\" target=\"_self\" >";
                                    echo " Create Snapshot</a> <br /> <br />";
                                    echo "</span>";
                                ?>
                            </div>
                            <div class="card-body">
                                <?php
                                    /* Snapshot information */
                                    $tmp = $lv->list_domain_snapshots($dom);
                                    if (!empty($tmp)) {
                                    echo "<div class='table-responsive'>" .
                                        "<table class='table'>" .
                                            "<thead>" .
                                                "<tr>" .
                                                    "<th>Name</th>" .
                                                    "<th>Creation Time</th>" .
                                                    "<th>Domain State</th>" .
                                                    "<th>Actions</th>" .
                                                "</tr>" .
                                            "</thead>" .
                                            "<tbody>";

                                    foreach ($tmp as $key => $value) {
                                        //Getting XML info on the snapshot. Using simpleXLM because libvirt xml functions don't seem to work for snapshots
                                        $tmpsnapshotxml = $lv->domain_snapshot_get_xml($domName, $value);
                                        $tmpxml = simplexml_load_string($tmpsnapshotxml);
                                        $name = $tmpxml->name[0];
                                        $creationTime = $tmpxml->creationTime[0];
                                        $snapstate = $tmpxml->state[0];
                                        echo "<tr>";
                                        echo "<td>" . $name . "</td>";
                                        echo "<td>" . date("D d M Y", $value) . " - ";
                                        echo date("H:i:s", $value) . "</td>";
                                        echo "<td>" . $snapstate . "</td>";
                                        echo "<td>
                                        <a title='Delete snapshot' href=\"?action=domain-snapshot-delete&amp;snapshot=$value&amp;uuid=$uuid\">Delete | </a>
                                        <a title='Revert snapshot' href=?action=domain-snapshot-revert&amp;uuid=" . $uuid . "&amp;snapshot=" . $value . ">Revert | </a>
                                        <a title='View snapshot XML' href=?action=domain-snapshot-xml&amp;uuid=" . $uuid . "&amp;snapshot=" . $value . ">XML</a>
                                        </td>";
                                        echo "</tr>";
                                    }
                                    echo "</tbody></table></div>";
                                    } else {
                                    echo "<hr><p>Guest does not have any snapshots</p>";
                                    }

                                    if ($snapshotxml != null) {
                                    echo "<hr>";
                                    echo "<h3>Snapshot XML: " . $snapshot . "</h3>";
                                    echo  "<textarea rows=15 style=\"width: 100%; margin: 0; padding: 0; border-width: 0; background-color:#ebecf1;\">" . $snapshotxml . "</textarea>";
                                    }
                                ?>
                            </div>
                        </div>

                    
                    </div> <!--Ends column -->

                </div> <!-- End Row -->

            </div> <!-- End Content -->
            
            
        </main>
    </div>
</div>




<!-- Hidden modal for displaying and editing xml info -->
<div id="xml-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">XML Information </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="xmlForm" name="xml" role="form" method="POST" action="?action=domain-edit&amp;uuid=<?php echo $uuid; ?>" >
				<div class="modal-body">	
                    <p id="message"></p>			
                    <div class="form-group">
                        <label for="xml_data"></label>
                        <?php
                                /* XML information */
                                $inactive = (!$lv->domain_is_running($domName)) ? true : false;
                                $xml = $lv->domain_get_xml($domName, $inactive);
                                $xmlDisplay = htmlentities($xml);
                        ?>
                        <textarea name="xmldesc" id="xml_data" class="form-control" rows="13"><?php echo "$xmlDisplay";?></textarea>
                    </div>
                </div>
            <div class="modal-footer">					
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <?php
                    if ($state == "shutoff"){
                        echo "<input type=\"submit\" class=\"btn btn-primary\" id=\"submit\" value=\"Save\">";
                        echo "<input type=\"hidden\" name=\"action\" value=\"domain-edit\">";
                    }
                    ?>
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for adding a storage volume -->
<div id="add-storage-volume-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Add Storage Volume </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="addStorageVolumeForm" name="addStorageVolume" role="form" action="?action=add-storage-volume&amp;uuid=<?php echo $uuid; ?>" method="POST">
				<div class="modal-body">                   
          <div class="row">
            <label class="col-3 col-form-label text-right">Source File: </label>
            <div class="col-6">
              <div class="form-group">
                <select onchange="diskChangeOptions(this)"  class="form-control" name="source_file">
                  <option value="none"> Select Disk </option>
                  <option value="new"> Create New Disk Image </option>
                  <?php
                    $pools = $lv->get_storagepools();
                    for ($i = 0; $i < sizeof($pools); $i++) {
                      $info = $lv->get_storagepool_info($pools[$i]);
                      if ($info['volume_count'] > 0) {
                        $tmp = $lv->storagepool_get_volume_information($pools[$i]);
                        $tmp_keys = array_keys($tmp);
                        for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                          $path = base64_encode($tmp[$tmp_keys[$ii]]['path']);
                          $ext = pathinfo($tmp_keys[$ii], PATHINFO_EXTENSION);
                          if (strtolower($ext) != "iso")
                            echo "<option value='" . $tmp[$tmp_keys[$ii]]['path'] . "'>" . $tmp[$tmp_keys[$ii]]['path'] . "</option>";
                        }
                      }
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label diskChange text-right" id="new" style="display:none;">Volume Name: </label>
            <div class="col-6">
              <div class="form-group diskChange" id="new" style="display:none;">
                <input type="text" class="form-control" id="DataImageName" value="newVM.qcow2" placeholder="Enter new volume name" name="new_volume_name">
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label diskChange text-right" id="new" style="display:none;">Volume Size: </label>
            <div class="col-3 diskChange" id="new" style="display:none;">
              <div class="form-group">
                <input type="number" class="form-control" value="40" min="1" name="new_volume_size">
              </div>
            </div>
                    
            <div class="col-4 checkbox-radios diskChange" id="new" style="display:none;">
              <div class="form-check form-check-inline">
                <label class="form-check-label text-right">
                  <input class="form-check-input" type="radio" name="new_unit" value="M"> MB
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <label class="form-check-label text-right">
                  <input class="form-check-input" type="radio" name="new_unit" value="G" checked="checked"> GB
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label diskChange text-right" id="new" style="display:none;">Driver Type: </label>
            <div class="col-6">
              <div class="form-group diskChange" id="new" style="display:none;">
                <select class="form-control" onchange="newExtenstion(this.form)" name="new_driver_type">
                  <option value="qcow2" selected="selected">qcow2</option>
                  <option value="raw">raw</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label diskChange text-right" id="new" style="display:none;">Target bus: </label>
            <div class="col-6">
              <div class="form-group diskChange" id="new" style="display:none;">
                <select  class="form-control" name="new_target_bus" >
                  <option value="virtio" selected="selected">virtio</option>
                  <option value="ide">ide</option>
                  <option value="sata">sata</option>
                  <option value="scsi">scsi</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label diskChange text-right" id="existing" style="display:none;">Driver type: </label>
            <div class="col-6">
              <div class="form-group diskChange" id="existing" style="display:none;">
                <select  class="form-control" name="existing_driver_type" >
                  <option value="qcow2" selected="selected">qcow2</option>
                  <option value="raw">raw</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label diskChange text-right" id="existing" style="display:none;">Target bus: </label>
            <div class="col-6">
              <div class="form-group diskChange" id="existing" style="display:none;">
                <select  class="form-control" name="existing_target_bus" >
                  <option value="virtio" selected="selected">virtio</option>
                  <option value="ide">ide</option>
                  <option value="sata">sata</option>
                  <option value="scsi">scsi</option>
                </select>
              </div>
            </div>
          </div>

        </div>        
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" id="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for adding optical storage  -->
<div id="add-optical-storage-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Add Optical Storage </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="addOpticalStorageForm" name="addOpticalStorage" role="form" action="?action=add-optical-storage&amp;uuid=<?php echo $uuid; ?>" method="POST">
				<div class="modal-body"> 
          <div class="row">
            <label class="col-3 col-form-label text-right">ISO File: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="source_file">
                  <option value="none">Select File</option>
                  <?php
                    $pools = $lv->get_storagepools();
                    for ($i = 0; $i < sizeof($pools); $i++) {
                      $info = $lv->get_storagepool_info($pools[$i]);
                      if ($info['volume_count'] > 0) {
                        $tmp = $lv->storagepool_get_volume_information($pools[$i]);
                        $tmp_keys = array_keys($tmp);
                        for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                          $path = base64_encode($tmp[$tmp_keys[$ii]]['path']);
                          $ext = pathinfo($tmp_keys[$ii], PATHINFO_EXTENSION);
                          if (strtolower($ext) == "iso")
                            echo "<option value='" . $tmp[$tmp_keys[$ii]]['path'] . "'>" . $tmp[$tmp_keys[$ii]]['path'] . "</option>";
                        }
                      }
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
        </div>        
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" id="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for adding optical storage  -->
<div id="add-network-adapter-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Add Network Adapter</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="addNetworkAdapterForm" name="addNetworkAdapter" role="form" action="?action=add-network-adapter&amp;uuid=<?php echo $uuid; ?>" method="POST">
				<div class="modal-body"> 

          <div class="row">
            <label class="col-3 col-form-label text-right">MAC Address: </label>
            <div class="col-6">
              <div class="form-group">
                <?php $random_mac = $lv->generate_random_mac_addr();?>
                <input type="text" value="<?php echo $random_mac; ?>" required="required" id="DataImageName" placeholder="Enter MAC Address" class="form-control" name="mac_address" />
              </div>
            </div>
          </div>
              
          <div class="row">
            <label class="col-3 col-form-label text-right">Model Type: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="model_type">
                  <?php
                    $models = $lv->get_nic_models();
                    for ($i = 0; $i < sizeof($models); $i++) {
                      echo "<option value=\"$models[$i]\"> $models[$i] </option>";
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label text-right">Network Source: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="source_network">
                  <?php
                  $networks = $lv->get_networks();
                  for ($i = 0; $i < sizeof($networks); $i++) {
                    echo "<option value=\"$networks[$i]\"> $networks[$i] </option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

        </div>        
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" id="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for deleting a virtual machine -->
<div id="confirm-delete-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
      <div class="modal-body">				
        <p id="message"></p>
      </div>
      <div class="modal-footer">					
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a class="btn btn-warning btn-ok">Delete</a>
      </div>
		</div>
	</div>
</div>




<script>

  function domainDeleteWarning(linkURL, domName) {
    let r = confirm("Deleting virtual machine " + domName + ".");
    if (r == true) {
      window.location = linkURL;
    }
  }

  function showXML() {
    let d = document.getElementById("xml_card");
    d.style.display = "block";
  }

  function closeXML() {
    let d = document.getElementById("xml_card");
    d.style.display = "none";
  }

  function submitXML() {
    let x = document.getElementById("xml_div").innerText;
    let y = document.getElementById("xml_textarea");
    y.value = x;
  }

  function diskChangeOptions(selectEl) {
    let selectedValue = selectEl.options[selectEl.selectedIndex].value;
      if (selectedValue.charAt(0) === "/") {
        selectedValue = "existing";
      }
    let subForms = document.getElementsByClassName('diskChange')
    for (let i = 0; i < subForms.length; i += 1) {
      if (selectedValue === subForms[i].id) {
        subForms[i].setAttribute('style', 'display:block')
      } else {
        subForms[i].setAttribute('style', 'display:none')
      }
    }
  }

  function newExtenstion(f) {
    var diskName = f.new_volume_name.value;
    diskName = diskName.replace(/\s+/g, '');
    var n = diskName.lastIndexOf(".");
    var noExt = n > -1 ? diskName.substr(0, n) : diskName;
    var driverType = f.new_driver_type.value;
    if (driverType === "qcow2"){
      var ext = ".qcow2";
      var fullDiskName = noExt.concat(ext);
      f.new_volume_name.value = fullDiskName;
    }
    if (driverType === "raw"){
      var ext = ".img";
      var fullDiskName = noExt.concat(ext);
      f.new_volume_name.value = fullDiskName;
    }
  }

  function networkChangeOptions(selectEl) {
    let selectedValue = selectEl.options[selectEl.selectedIndex].value;

    let subForms = document.getElementsByClassName('networkChange')
    for (let i = 0; i < subForms.length; i += 1) {
      if (selectedValue === subForms[i].id) {
        subForms[i].setAttribute('style', 'display:block')
      } else {
        subForms[i].setAttribute('style', 'display:none')
      }
    }
  }
</script>


<?php
require('../footer.php');
?>

<script>
    //Set variables and href for delete modal
        $('#confirm-delete-modal').on('show.bs.modal', function(e) {
        var filename = $(e.relatedTarget).data('filename');
        //$("#confirm-delete-modal .modal-dialog .modal-content .modal-body p").html("Are you sure you want to delete: "+ filename);
        $(e.currentTarget).find('p[id="message"]').html("Are you sure you wish to delete <strong>" + filename + "</strong>?");
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Load show XML modal if XML requested -->
<?php if($xml_data) { ?>
  <script>
  $(window).on('load',function(){
      $('#xml-modal').modal('show');
      //$('#xml-modal textarea').html("test");
  });
  </script>
<?php } ?>