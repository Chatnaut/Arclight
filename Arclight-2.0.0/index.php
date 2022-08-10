<?php

// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

//Sets the current web directory
$fileDir = dirname(__FILE__);

//Sets the filepath for the .env file which should be created after successful setup
$envpath = dirname(__FILE__) . "/.env";

//If the config.php file exists perform the following
if (file_exists($envpath)) {
  include_once('./pages/config/config.php');

  //create a new instance of the DbManager class
  $db = new DbManager();
  $conn = $db->getConnection();
  $userid = $_SESSION['userid'];

//find document 
  $filter = ['name' => 'cert_path'];
  $read = new MongoDB\Driver\Query($filter);
  $result = $conn->executeQuery("arclight.arclight_configs", $read);
  $result = $result->toArray();

  //get value from array
  $cert_path = $result[0]->value;
  if($cert_path != ""){
    $cert_option = "--cert=" . $cert_path; //--cert is option used in noVNC connection string
  }else{
    $cert_option = ""; //sets default location if nothing in database
  }

  //find document 
  $filter1 = ['name' => 'key_path'];
  $read1 = new MongoDB\Driver\Query($filter1);
  $result1 = $conn->executeQuery("arclight.arclight_configs", $read1);
  $result1 = $result1->toArray();

  //get value from array
  $key_path = $result1[0]->value;
  if($key_path != ""){
    $key_option = "--key=" . $key_path; //--key is option used in noVNC connection string
  }else{
    $key_option = ""; //sets default location if nothing in database
  }
} //Ends if statement if config.php file exists

//letsencrypt setup -> sudo certbot certonly --standalone -d host.example.com
//letsencrypt options -> --cert=/etc/letsencrypt/live/host.example.com/fullchain.pem --key=/etc/letsencrypt/live/host.example.com/privkey.pem
//shell_exec("./apps/noVNC/utils/websockify/run --web $fileDir/apps/noVNC/ --cert /etc/ssl/self.pem --target-config ./tokens.php 6080 > logs/novnc.log 2>&1 &");
//shell_exec("./apps/noVNC/utils/websockify/run --web $fileDir/apps/noVNC/ --cert $cert_path --target-config ./tokens.php 6080 > logs/novnc.log 2>&1 &");
shell_exec("./apps/noVNC/utils/websockify/run --web $fileDir/apps/noVNC/ $cert_option $key_option --target-config ./tokens.php 6080 > logs/novnc.log 2>&1 &");

//currently using GET to at the index page to indicate logout, may switch to SESSION VARIABLE
$action = $_GET['action'];
if ($action == "logout") {
  session_unset();   //Remove all session variables
  session_destroy(); //Destory all $_SESSION data
}

$username = $_SESSION['username'];

//Redirect based on login session or initial setup complete
if (isset($_SESSION['username'])) {
  require('pages/libvirt.php');
  $lv = new Libvirt();
  if ($lv->connect("qemu:///system") == false)
    die('<html><body>Cannot open connection to hypervisor. Please check to make sure that the Qemu service is running.</body></html>');
  //Check if storage pools exist, if not send the user there. This is used mostly on new install
  $pools = $lv->get_storagepools();
  if (empty($pools)) {
    header('Location: pages/storage/storage-pools.php');
  } else {
    header('Location: pages/domain/instance-list-user.php');
  }

  //If user is not logged in check to make sure that the config.php setup file is created. If it does send them to login
} elseif (file_exists($envpath)) {
  header('Location: pages/sign-in.php');

  //If the user is not logged in and the config.php has not yet been created send them to setup configuration.
} else {
  header('Location: pages/config/setup-configuration.php');
}
