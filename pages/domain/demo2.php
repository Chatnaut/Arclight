<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])){
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../sign-in.php');
}
require('../header.php');
require('../navbar.php');
require('../footer.php');
require('../config/config.php');


  // This function is used to prevent any problems with user form input
  function clean_input($data) {
    $data = trim($data); //remove spaces at the beginning and end of string
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = str_replace(' ','',$data); //remove any spaces within the string
    $data = filter_var($data, FILTER_SANITIZE_STRING);
    return $data;
  }

if (isset($_POST['action'])) {
  $_SESSION['action'] = $_POST['action'];
  $_SESSION['pciaddr'] = clean_input($_POST['pciaddr']);
  $_SESSION['mdevtype'] = $_POST['mdevtype'];
  $_SESSION['UUID'] = $_POST['UUID'];
  $_SESSION['domain_name'] = $_POST['domain_name'];
  // unset($_POST);
 
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}
$userid = $_SESSION['userid'];


?> 




<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
        <h3 class="h3">GPU</h3>
      </div>

      <form action="" method="POST">
        <div class="content">
          <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

              <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
                <div class="card-header">
                  <span class="card-title"></span>
                </div>
                <div class="card-body">

                  <div class="table-responsive">
                    <table class="table">
                      <tbody>
                    <!-- start project list -->
                    <?php

$ci  = $lv->get_connect_information();
$info = '';
if ($ci['uri'])
    $info .= ' <i>'.$ci['uri'].'</i>, ';
echo "<strong>:</strong> {$ci['hypervisor_string']} <br>";


//$getlibvirtversion = $lv->get_l_version();

//echo "Libvirt Version: " . $getlibvirtversion['libvirt.major'] . "." . $getlibvirtversion['libvirt.minor'] . "." . $getlibvirtversion['libvirt.release'];

//Delete storage volume==========================================================
// $path = "/var/lib/libvirt/images/goku.qcow2";
// $del_storagevolume = $lv->storagevolume_delete($path);
// if($del_storagevolume) {
//   echo "Storage Volume Deleted";
// } else {
//   echo "Storage Volume Not Deleted";
// }

//Resize storage volume==========================================================

  // $path = "/var/lib/libvirt/images/goku.qcow2";
  // $size = "80G";
  // $resize_storagevolume = $lv->storagevolume_resize($path, $size);
  // if($resize_storagevolume) {
  //   echo "Storage Volume Resized";
  // } else {
  //   echo "Storage Volume Not Resized";
  // }

//create new storage volume in pool default==========================================================
  // $pool_name = "default";
  // $name = "gokuo.qcow2";
  // $size = "10G";
  // $format = "qcow2";
  // $notification = $lv->storagevolume_create($pool_name, $name, $size, $format)? "Storage Volume Created" : "Storage Volume Not Created";


  //get hosttname
  $hostname = $lv->get_hostname();
  echo "Hostname: " . $hostname;

// set libvirt logfile
$logfile = "libvirtd.log";
$set_logfile = $lv->set_logfile($logfile);
if($set_logfile) {
  echo "Logfile Set";
} else {
  echo "Logfile Not Set";
}


                    ?>
 <iframe src="https://3.111.98.248:4433/" height="500" width="800" title="arclight ssh"></iframe> 

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>
  <!-- end content of physical GPUs -->

<script>
console.log(localStorage.getItem("hostname"));
</script>
