<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
    session_start();
}

//Sets the current web directory
$fileDir = dirname(__FILE__);

//Sets the filepath for the config.php file which should be created after successful setup
$path = dirname(__FILE__) . "/pages/config/config.php";

//If the config.php file exists perform the following
if (file_exists($path)) {
  require('./pages/config/config.php');

  //Change name of tables if still using openvm
  $sql = "select * from openvm_users;"; //check to see if openvm_users table exits
  $openvm_result = $conn->query($sql);
  //if openvm_users table exists and has any values, rename the tables to arclight
  if (mysqli_num_rows($openvm_result) != 0 ) {
    $sql = "RENAME TABLE openvm_users TO arclight_users";
    $rename_result = $conn->query($sql);
  }
  $sql = "select * from openvm_config;"; //check to see if openvm_users table exits
  $openvm_result = $conn->query($sql);
  //if openvm_users table exists and has any values, rename the tables to arclight
  if (mysqli_num_rows($openvm_result) != 0 ) {
    $sql = "RENAME TABLE openvm_config TO arclight_config";
    $rename_result = $conn->query($sql);
  }

  //Create the arclight_events table
  $sql = "CREATE TABLE IF NOT EXISTS arclight_events (
    eventid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    description varchar(255),
    host_uuid varchar(255),
    domain_uuid varchar(255),
    userid INT,
    date datetime)";
    $rsql = "ALTER TABLE arclight_users ADD COLUMN roles VARCHAR(50)";
    $event_result = $conn->query($sql);
    $rolequery = $conn->query($rsql);


  //Setting the SSL Certificate file path
  $sql = "SELECT value FROM arclight_config WHERE name = 'cert_path' LIMIT 1;";
  $result = $conn->query($sql);
  // Extracting the record
  if (mysqli_num_rows($result) != 0 ) {
    while ($row = $result->fetch_assoc()) {
   	  $cert_path = $row['value']; //sets value from database
    }
  }
  if ($cert_path){
    $cert_option = "--cert=" . $cert_path; //--cert is option used in noVNC connection string
  } else {
    $cert_option = "--cert /etc/ssl/self.pem"; //sets default location if nothing in database
  }

  //Setting the SSL Certificate file path
  $sql = "SELECT value FROM arclight_config WHERE name = 'key_path' LIMIT 1;";
  $result = $conn->query($sql);
  // Extracting the record
  if (mysqli_num_rows($result) != 0 ) {
    while ($row = $result->fetch_assoc()) {
      $key_path = $row['value']; //sets value from database
    }
  }
  if ($key_path){
    $key_option = "--key=" . $key_path; //--key is option used in noVNC connection string
  } else {
    $key_option = ""; //will ignore key file if nothing in database
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
  session_destroy(); //Destory all $_SESSION data
  // unset($_SESSION['username']);
}

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
  } else if ($_SESSION['username'] == $db_user){
    $_SESSION['roles'] = "Enterprise";                   //Assign IAM roles as per user data 
    header('Location: pages/domain/domain-list.php');
  }
  else if ($_SESSION['username'] != $db_user){
    $_SESSION['roles'] = "Administrator";
    header('Location: pages/domain/domain-list-user.php');
  }
 else {
  header('Location: pages/login.php');
  }
  

//If user is not logged in check to make sure that the config.php setup file is created. If it does send them to login
} elseif (file_exists($path)) {
  header('Location: pages/login.php');

//If the user is not logged in and the config.php has not yet been created send them to setup configuration.
} else {
header('Location: pages/config/setup-configuration.php');
}


  if(isset($_SESSION['roles'])){
    $roleset = $_SESSION['roles'];
    $userid = $_SESSION['userid'];
    $sql = "UPDATE arclight_users SET roles = '$roleset'  WHERE  userid = '$userid'";
    $inserttrole = mysqli_query($conn, $sql);
  } 
  else {
    header('Location: pages/login.php');

  }


?>
