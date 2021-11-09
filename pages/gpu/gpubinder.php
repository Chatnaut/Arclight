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
require('../header.php');
require('../navbar.php');
require('../footer.php');

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
                      <thead class="text-none">
                        <th>PCI Devices</th>
                      </thead>
                      <tbody>
                    <!-- start project list -->
                    <?php
                    $result = shell_exec('cd /var/www/html/arclight/gpubinder && ./nvidia-dev-ctl.py list-pci -o table');
                      echo "<tr>" .
                        "<td><pre>{$result}</pre></td>" .
                        "</tr>";                
                      echo "</tbody></table>";
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>
  <!-- end content of physical GPUs -->




<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">


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
                      <thead class="text-none">
                        <th>List of Attached Physical GPUs [PCI]</th>
                      </thead>
                      <tbody>
                    <!-- start project list -->
                    <?php
                    $result = shell_exec('cd /var/www/html/arclight/gpubinder && ./nvidia-dev-ctl.py list-used-pci -o table');
                      echo "<tr>" .
                        "<td><pre> {$result} </pre></td>" .
                        "</tr>";
                      echo "</tbody></table>";
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>
  <!-- end content of list of physical GPUs -->



<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">


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
                      <thead class="text-none">
                        <th>List of Attached Virtual GPUs [MDEV]</th>
                      </thead>
                      <tbody>
                    <!-- start project list -->
                    <?php
                    $result = shell_exec('cd /var/www/html/arclight/gpubinder && ./nvidia-dev-ctl.py list-mdev --output-all');

                      echo "<tr>" .
                        "<td><pre>{$result}</pre></td>" .
                        "</tr>";
                    
                    echo "</tbody></table>";
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>
  <!-- end content of list of virtual GPUs -->


<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">


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
                      <thead class="text-none">
                        <th>Create Virtual GPU</th>
                        </thead>
                        <tbody>

                          <!-- start project list -->
                          <?php                                                 
                            
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
                            echo "</tbody></table>";
                                              
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
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>
  <!-- end content of Creating virtual GPUs -->




<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">


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
                      <thead class="text-none">
                        <th>Profile Management</th>

                        <!-- start project list include '../domain/rud.php-->
                        <div class="container-xl">
                          <div class="table-responsive">
                              <div class="table-wrapper">
                                  <div class="table-title">
                                      <div class="row">
                                  <table class="table table-striped table-hover">
                                      <thead>
                                          <tr>
                                              <th>MDEV UUID</th>
                                              <th>MDEV Type</th>
                                              <th>Action</th>						
                                              <th>VM</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                        <?php 
                                        require('../config/config.php');
                                        $selectquery = "SELECT * FROM arclight_vgpu";
                                        $query = mysqli_query($conn, $selectquery);

                                        while($res = mysqli_fetch_array($query)){
                                              ?>
                                          
                                          <tr>
                                              <td><?php echo $res['mdevuuid']; ?></td>
                                              <td><?php echo $res['mdevtype']; ?></td>

                                                  <td>
                                                  <?php
                                              if ($res['action'] == 'attachmdev'){
                                                  ?>
                                                  <span class="status text-success">&bull;</span> Attached
                                                  <?php 
                                              }  else if ($res['action'] == ('createmdev')||('detachmdev')){
                                                  ?>
                                                      <span class="status text-warning">&bull;</span> Idle
                                                  <?php 
                                              }  else {
                                                  ?>
                                                      <span class="status text-danger">&bull;</span> Error</td>                        
                                                  <?php
                                              }  ?>

                                              <td><?php echo $res['domain_name']; ?></td>
                                              <td> 
                                                  <?php if ($res['action'] == 'attachmdev'){ ?>
                                                  <!-- Redirecting customers directly to detach and remove page actions by their ids via GET -->
                                                  <a href="../domain/dmdev.php?id=<?php echo $res['sno']; ?>" class="settings" title="Detach" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>
                                                  <?php } ?>

                                                  <?php if ($res['action'] == 'createmdev'){ ?>
                                                  <a href="../domain/amdev.php?id=<?php echo $res['sno']; ?>" class="settings" title="Attach" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>

                                                  <a href="../domain/rmdev.php?id=<?php echo $res['sno']; ?>" class="delete" title="Suspend" data-toggle="tooltip"><i class="material-icons">&#xE5C9;</i></a>
                                                  <?php } ?>
                                                </td> 
                                                

                                          </tr>
                                        <?php } ?>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                      </div>     
                </div>
              </div>
            </div>
          <!-- </div>
        </div> -->
      </form>

<!--------------------------------------------------------------------- -->

  <!-- Trigger the modal with a button -->
  <button type="button" class="custom-btn btn-2" data-toggle="modal" data-target="#save-modal">Save</button>
  <button class="custom-btn btn-2" data-toggle="modal" data-target="#restore-modal">Restore</button>

  <?php                                                 
    if (isset($_POST['savegpuconf'])){
        $sql = "CREATE TABLE IF NOT EXISTS arclight_gpuevents (
        sno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        userid INT,
        action varchar(255),
        conf char(50),    
        dt DATETIME)";
        $tablesql = mysqli_query($conn, $sql);
        
        $userid = $_SESSION['userid'];
        $filename = $_POST['filename']; 
      if($_POST['filename'] != ""){                          
        $creatgpuconf = shell_exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py save -o '".$filename."'.conf");
        if($creatgpuconf == ""){
          $action = "Configuration File Created";
          echo $action;
          $sql = "INSERT INTO arclight_gpuevents (userid, action, conf, dt) VALUES('$userid', '$action', '$filename', current_timestamp());"; 
          $inserttablesql = mysqli_query($conn, $sql);
        }
        else{
          echo "Error creating file";
        }
      }
      else{
        echo "Please enter a filename";
      }
    }
  ?>

  <div id="save-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Save GPU Configuration</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div> 
			 <form id="savegpu-conf" name="savegpu-conf" role="form" action="" method="post">
				<div class="modal-body">				
					<div class="form-group">
						<label for="filename">File Name</label>
						<input type="text" name="filename" class="form-control">
          </div>
          <input type="hidden" name="savegpuconf" class="form-control">	
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" value="Save">
				</div>
			</form>
		</div>
	</div>
</div>
<!-- end content of Save file Configuration -->
<?php
require('../config/config.php');

$showquery = "SELECT * from arclight_gpuevents WHERE userid = '$userid'";
$showdata = mysqli_query($conn,$showquery);
$arrdata = mysqli_fetch_array($showdata);
?>

<div id="restore-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Restore GPU Configuration</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div> 
      <div class="container mt-5">  

      <form action="" method="post">   

        <?php
        if (mysqli_num_rows($arrdata) > 0) {
?>
      <select name="domainname">  

<option selected>Open this select menu</option>
        <!-- <select class="form-select" id="selectmdev" name="selectmdev"> -->
        <option value = "" selected> File Name</option>  
                <?php
                $i=0;
                while($DB_ROW = mysqli_fetch_array($arrdata)) {
                ?>
            <option value="<?php echo $DB_ROW["conf"];?>"><?php echo $DB_ROW["conf"];?></option>
            <?php
                $i++;
                }     
              }  
                else{
                        echo "Configuration  File Not Found";
                }
            ?>
      </select>
      <br> <br> <input type = "submit" name = "submit" value = "Attach">  
    </form> 
		</div>
	</div>
</div>
</div>


    </main>




<!-- replaceState method of JQuery to prevent data again after submission due to post back -->
<script>
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>