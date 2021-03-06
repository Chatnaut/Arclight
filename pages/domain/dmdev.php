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

  // This function is used to prevent any problems with user form input
  function clean_input($data) {
    $data = trim($data); //remove spaces at the beginning and end of string
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = str_replace(' ','',$data); //remove any spaces within the string
    $data = filter_var($data, FILTER_SANITIZE_STRING);
    return $data;
  }

require('../header.php');
require('../navbar.php');
require('../footer.php');
require('../config/config.php');

$userid = $_SESSION['userid']; //grab the $uuid variable from $_POST, only used for actions below

// <!-------------------------------------Detach MDEV GPU------------------------------------->
$ids = $_GET['id'];
$showquery = "SELECT * from arclight_vgpu WHERE sno={$ids} AND action = 'attachmdev' AND userid = '$userid'";
$showdata = mysqli_query($conn,$showquery);
$arrdata = mysqli_fetch_array($showdata);

// $sql = "SELECT mdevtype, mdevuuid FROM arclight_vgpu WHERE action = 'createmdev' AND userid = '$userid';";
// $dsql = "SELECT domain_name FROM arclight_vm WHERE userid = '$userid';";

// $result = $conn->query($sql);
// $dresult = $conn->query($dsql);


?>

<!-- <html lang="en">  
<head>  
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> 
  <title> PHP Select Dropdown Example </title> -->
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
</head>  
<!-- usage: nvidia-dev-ctl.py detach-mdev [-h] [--virsh-trials N]
                                     [--virsh-delay SECONDS] [-c URL]
                                     [--hotplug] [--restart] [-n]
                                     UUID DOMAIN -->

                                     
<body>
  <div class="container mt-5">  
    <form action="" method="post">  
      <select name="uuid">  
        <!-- <select class="form-select" id="selectmdev" name="selectmdev"> -->
            <option value="<?php echo $arrdata['mdevuuid']; ?>"><?php echo $arrdata['mdevtype']; ?> (<?php echo $arrdata['mdevuuid']; ?>)</option>
        </select> 
      <select name="domainname">  
            <option value="<?php echo $arrdata['domain_name']; ?>"><?php echo $arrdata['domain_name']; ?></option>
      </select>

        <select name="optionalargument">  
        <!-- <option value = "" selected> Arguments </option>  -->
        <option value="">None</option>
        <option value="--hotplug">Hotplug</option>
        <option value="--restart">Restart</option>
        </select> 
      <!-- <div class="selectIcon">  
        <svg focusable="false" viewBox="0 0 104 128" width="25" height="35" class="icon">  
          <path  
            d="m2e1 95a9 9 0 0 1 -9 9 9 9 0 0 1 -9 -9 9 9 0 0 1 9 -9 9 9 0 0 1 9 9zm0-3e1a9 9 0 0 1 -9 9 9 9 0 0 1 -9 -9 9 9 0 0 1 9 -9 9 9 0 0 1 9 9zm0-3e1a9 9 0 0 1 -9 9 9 9 0 0 1 -9 -9 9 9 0 0 1 9 -9 9 9 0 0 1 9 9zm14 55h68v1e1h-68zm0-3e1h68v1e1h-68zm0-3e1h68v1e1h-68z">  
          </path>  
        </svg>  
      </div>   -->
      <br> <br> <input type = "submit" name = "submit" value = "Detach">  
    </form>  
    <?php  
        if(isset($_POST['submit'])){  
            if(!empty($_POST['uuid'])) {  
            $selecteduuid = clean_input($_POST['uuid']);
            $optionalarguments = clean_input($_POST['optionalargument']);
            $domainname = clean_input($_POST['domainname']);
            $dettachmdev = exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py detach-mdev '".$optionalarguments."' '".$selecteduuid."' '".$domainname."'", $output, $return_var);

            if (empty($return_var)){
            $sql = "UPDATE arclight_vgpu SET domain_name = '', action = 'createmdev' WHERE action = 'attachmdev' AND userid = '$userid';";
            $result = $conn->query($sql);
            echo 'MDEV with UUID: '  . $selecteduuid; 
            echo "<br>";
            echo 'was succesfully detached from  '  . $domainname;
             
            }else {
              echo "Exception Error";
            }
          }
          else{
            echo 'No value selected';
        }
     } 
        
        
    ?>  
   </div>  
  <!-- </body>  
</html>  -->