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




// <!-------------------------------------attach MDEV GPU------------------------------------->
$pciid = $_GET['pciid'];
$showquery = "SELECT * from arclight_gpu WHERE sno={$pciid} AND action = 'Attached'";
$showdata = mysqli_query($conn,$showquery);
$pciarrdata = mysqli_fetch_array($showdata);

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
<!-- </head>  

<body> -->
  <div class="container mt-5">  
    <form action="" method="post">  
      <select name="pciaddr">  
        <option value="<?php echo $pciarrdata['pciaddr']; ?>"><?php echo $pciarrdata['pciaddr']; ?></option>
        </select> 
      <select name="domainname">  
            <option value="<?php echo $pciarrdata["domain_name"];?>"><?php echo $pciarrdata["domain_name"];?></option>
      </select>
        <select name="optionalargument">  
          <!-- <select class="form-select" id="selectmdev" name="selectmdev"> -->
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
      <br> <br> <input type = "submit" name = "detachpci" value = "Remove">  
    </form>  
  </div>
    <?php  
        if(isset($_POST['detachpci'])){  
            if(!empty($_POST['pciaddr'])) {  
            $pciaddr = clean_input($_POST['pciaddr']);
            $optionalarguments = clean_input($_POST['optionalargument']);
            $action = 'attachmdev';
            $domainname = clean_input($_POST['domainname']);
            $dpci = exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py detach-pci '".$optionalarguments."' '".$pciaddr."' '".$domainname."'", $output, $return_var);
            // var_dump($return_var);
            // echo "return_var is: $return_var" . "\n";
            // var_dump($output);
            // echo '<pre>'; print_r($output); echo '</pre>';
            
            if (empty($return_var)){

                $sql = "DELETE FROM arclight_gpu WHERE sno = '$pciid';";


            $inserttablesql = mysqli_query($conn, $sql);
            if ($inserttablesql) {
                echo "Successfully Removed";
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
            }
            else{
                echo "Error: " . $dpci . "<br>" . mysqli_error($conn);
            }
            }  
            else{  
                echo "Please enter a pci address";  
            }
        }
    ?>  
  <!-- </body>  
</html>  -->

<!-- Basic execution from a PHP script

There are four (!) PHP functions which purpose is to run an external command and return output:

    exec() accepts command as input and returns the last line from the result of the command. Optionally, it can fill a provided array with every line of the output and also assign the return code to the variable. On failure, the function returns false.

    passthru() executes a command and passes the raw output directly to the browser. The PHP documentation recommends it in case if binary output has to be sent without interference.

    shell_exec() executes a command and returns the complete output as a string. It does not provide the exit code. The function return value is confusing because it can be null both if an error occured or if the command produced no output.

    system() acts like passthru(), but it also returns the last line of the output. This function works well only with text output. -->
