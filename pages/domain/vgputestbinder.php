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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Document</title> -->
</head>
<body>
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

<!-------------------------------------ATTACH MDEV GPU------------------------------------->
<!--usage: nvidia-dev-ctl.py attach-mdev [-h] [--virsh-trials N]
                                     [--virsh-delay SECONDS] [-c URL]
                                     [--hotplug] [--restart] [-n]
                                     UUID DOMAIN -->



<!-------------------------------------Remove MDEV GPU------------------------------------->
<?php
$sql = "SELECT mdevuuid, mdevtype FROM arclight_vgpu WHERE action = 'createmdev' AND userid = '$userid';";
$result = $conn->query($sql);

// if ($action == "removemdev"){ 
//   $selectmdev = $_SESSION['selectmdev'];
//   echo $selectmdev;

  // $removedmdev = shell_exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py remove-mdev '".$optionalarguments."'");
  // if(!$removemdev)
  // {
  //   // //     echo "Inserted into databse";
  //   // }
  //   // else{
  //       }
  

?>
 
  <style>  
    .container {  
      max-width: 400px;  
      margin: 60px auto;  
      text-align: center;  
    }  
    input[type="submit"] {  
      margin-bottom: 25px;  
    }  
    .select-block {  
      width: 350px;  
      margin: 100px auto 40px;  
      position: relative;  
    }  
    select {  
      width: 100%;  
      height: 50px;  
      font-size: 100%;  
      font-weight: bold;  
      cursor: pointer;  
      border-radius: 0;  
      background-color: #1A33FF;  
      border: none;  
      border: 2px solid #1A33FF;  
      border-radius: 4px;  
      color: white;  
      appearance: none;  
      padding: 8px 38px 10px 18px;  
      -webkit-appearance: none;  
      -moz-appearance: none;  
      transition: color 0.3s ease, background-color 0.3s ease, border-bottom-color 0.3s ease;  
    }  
      select::-ms-expand {  
      display: none;  
    }  
    /* .selectIcon {  
      top: 7px;  
      right: 15px;  
      width: 30px;  
      height: 36px;  
      padding-left: 5px;  
      pointer-events: none;  
      position: absolute;  
      transition: background-color 0.3s ease, border-color 0.3s ease;  
    }   */
    /* .selectIcon svg.icon {  
      transition: fill 0.3s ease;  
      fill: white;  
    }   */
    select:hover {  
      color: #000000;  
      background-color: white;  
    }  
select:focus {  
      color: #000000;  
      background-color: white;  
    }  
    select:hover~.selectIcon  
     {  
      background-color: white;  
    }  
    select:focus~.selectIcon {  
      background-color: white;  
    }  
    select:hover~.selectIcon svg.icon  
    {  
      fill: #1A33FF;  
    }  
  select:focus~.selectIcon svg.icon {  
      fill: #1A33FF;  
    }  
h2 {  
 font-style: italic;  
font-family: "Playfair Display","Bookman",serif;  
 color: #999;   
letter-spacing: -0.005em;   
word-spacing:1px;  
font-size: 1.75em;  
font-weight: bold;  
  }  
h1 {  
 font-style: italic;  
 font-family: "Playfair Display","Bookman",serif;  
 color: #999;   
letter-spacing: -0.005em;   
word-spacing: 1px;  
 font-size: 2.75em;  
  font-weight: bold;  
  }  
input[type=submit] {  
  border: 3px solid;  
  border-radius: 2px;  
  color: ;  
  display: block;  
  font-size: 1em;  
  font-weight: bold;  
  margin: 1em auto;  
  padding: 1em 4em;  
  position: relative;  
  text-transform: uppercase;  
}  
input[type=submit]::before,  
input[type=submit]::after {  
  background: #fff;  
  content: '';  
  position: absolute;  
  z-index: -1;  
}  
input[type=submit]:hover {  
  color: #1A33FF;  
}  
  </style>  
 
  <div class="container mt-5">  

<?php
    if (mysqli_num_rows($result) > 0) {
?>
    <form action="" method="post">  
      <select name="removemdev">  
    <!-- <select class="form-select" id="selectmdev" name="selectmdev"> -->
        <option value = "" selected> Select option </option>  
    <?php
$i=0;
while($DB_ROW = mysqli_fetch_array($result)) {
    ?>
        <option value="<?php echo $DB_ROW["mdevuuid"];?>"><?php echo $DB_ROW["mdevtype"];?>  (<?php echo $DB_ROW["mdevuuid"];?>)</option>
      <?php
$i++;
}
    }
    else{
        echo "No device found";
}
?>
    </select> 
      <!-- <div class="selectIcon">  
        <svg focusable="false" viewBox="0 0 104 128" width="25" height="35" class="icon">  
          <path  
            d="m2e1 95a9 9 0 0 1 -9 9 9 9 0 0 1 -9 -9 9 9 0 0 1 9 -9 9 9 0 0 1 9 9zm0-3e1a9 9 0 0 1 -9 9 9 9 0 0 1 -9 -9 9 9 0 0 1 9 -9 9 9 0 0 1 9 9zm0-3e1a9 9 0 0 1 -9 9 9 9 0 0 1 -9 -9 9 9 0 0 1 9 -9 9 9 0 0 1 9 9zm14 55h68v1e1h-68zm0-3e1h68v1e1h-68zm0-3e1h68v1e1h-68z">  
          </path>  
        </svg>  
      </div>   -->
      <br> <br> <input type = "submit" name = "submit" value = "submit">  
    </form>  
    <?php  
        if(isset($_POST['submit'])){  
        if(!empty($_POST['removemdev'])) {  
            // $selected = $_POST['removemdev'];  
            $selected = clean_input($_POST['removemdev']);
            $removeddmdev = shell_exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py remove-mdev '".$selected."'");

        $rsql = "DELETE FROM arclight_vgpu WHERE mdevuuid = '$selected';";
        $result = $conn->query($rsql);

        echo 'Removed MDEV ID:'  . $selected;

    }
     else {
        echo 'Please select the value.'  . $selected;
    }

        } 
        
        
    ?>  
  </div>  
</body>  
</html> 