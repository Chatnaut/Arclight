<?php
  // If the SESSION has not been started, start it now
  if (!isset($_SESSION)) {
      session_start();
  }
  
  // If there is no username, then we need to send them to the login
  if (!isset($_SESSION['username'])){
    $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
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

if (isset($_POST['action'])) {
  $_SESSION['action'] = $_POST['action'];
  $_SESSION['pciaddr'] = clean_input($_POST['pciaddr']);
  $_SESSION['mdevtype'] = $_POST['mdevtype'];
  $_SESSION['UUID'] = $_POST['UUID'];
  $_SESSION['domain_name'] = $_POST['domain_name'];
  unset($_POST);
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}
 

?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body> -->
<?php

require('../header.php');

$userid = $_SESSION['userid']; //grab the $uuid variable from $_POST, only used for actions below
$action = $_SESSION['action']; //grab the $action variable from $_SESSION
unset($_SESSION['action']); //Unset the Action Variable to prevent repeats of action on page reload

//Creating user's databse table
require('../config/config.php');
$sql = "CREATE TABLE IF NOT EXISTS arclight_vgpu (
sno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
userid INT,
domain_name varchar(255),
action varchar(255),
mdevtype varchar(255),
mdevuuid varchar(255),
UUID varchar(255),
dt DATETIME)";
$tablesql = mysqli_query($conn, $sql);



// -------------------------------------CREATE MDEV GPU-------------------------------------
// usage: nvidia-dev-ctl.py create-mdev [-n] PCI_ADDRESS, MDEV_TYPE_OR_NAME

if ($action == "createmdev"){ 
  $pciaddr = $_SESSION['pciaddr'];
  $mdevtype = $_SESSION['mdevtype'];


  $createddmdev = shell_exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py create-mdev '".$pciaddr."' '".$mdevtype."'");
  $createdmdev = chop($createddmdev); //chop() function removes whitespaces or other predefined characters from the right end of a string. 
  
  // if variable $createdmdev gives permission error
  // 1.Edit your sudoers file
  // nano /etc/sudoers

  // 2.Put this line
  // www-data ALL=(ALL) NOPASSWD: ALL
  // ----------------------------------------------------------------------------------------


if ($createdmdev){
  $sql = "INSERT INTO arclight_vgpu (userid, action, mdevtype, mdevuuid, dt) VALUES('$userid', '$action', '$mdevtype', '$createdmdev', current_timestamp());"; 
  $inserttablesql = mysqli_query($conn, $sql);

  if(!$inserttablesql)
    {
      // //     echo "Inserted into databse";
      // }
      // else{
        echo("Error description: " . mysqli_error($conn));
    }

}
}


?>
<!-------------------------------------CREATE MDEV GPU------------------------------------->

<form class="row gx-3 gy-2 align-items-center" id="create-mdev" name="create-mdev" role="form" action="" method="post" >
  <div class="col-sm-3">
    <label class="visually-hidden" for="pciaddr">PCI Address</label>
    <input type="text" class="form-control" id="pciaddr" name="pciaddr" placeholder="PCI Address">
  </div>
  <div class="col-sm-3">
    <label class="visually-hidden" for="mdevtype">Mdev Type</label>
    <input type="text" class="form-control" id="mdevtype" name="mdevtype" placeholder="Mdevtype">
  </div>
          <input type="hidden" name="action" value="createmdev">
  <div class="col-auto">
    <button type="Create" class="btn btn-primary">Create Virtual GPU</button>
  </div>
</form>


<!-- </body>  
</html>  -->