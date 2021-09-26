
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
  unset($_POST);
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}
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
                      <thead class="text-none">
                        <th>Physical GPUs [PCI]</th>
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
  </div> 
</div> <!-- end content of physical GPUs-->




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
                        <th>Virtual GPUs [mdev]</th>
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
  </div> 
</div> <!-- end content of virtual GPUs-->




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
                        <th>Management</th>

                        <!-- start project list include '../domain/rud.php-->
                        <div class="container-xl">
                          <div class="table-responsive">
                              <div class="table-wrapper">
                                  <div class="table-title">
                                      <div class="row">
                                  <table class="table table-striped table-hover">
                                      <thead>
                                          <tr>
                                              <th>Mdev UUID</th>
                                              <th>Action</th>						
                                              <th>VM</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                        <?php 
                                        require('../config/config.php');
                                        $userid = $_SESSION['userid'];
                                        $selectquery = "SELECT * FROM arclight_vgpu WHERE userid = '$userid';";
                                        $query = mysqli_query($conn, $selectquery);

                                        while($res = mysqli_fetch_array($query)){
                                              ?>
                                          
                                          <tr>
                                              <td><?php echo $res['mdevuuid']; ?></td>
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
                                                  <!-- Redirecting customers directly to detach and remove page actions by using their ids via GET -->
                                                  <a href="../domain/dmdev.php?id=<?php echo $res['sno']; ?>" class="settings" title="Detach" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>
                                                  <?php } ?>

                                                  <?php if ($res['action'] == ('createmdev')||('detachmdev')){ ?>
                                                  <a href="#" class="delete" title="Suspend" data-toggle="tooltip"><i class="material-icons">&#xE5C9;</i></a>
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
          </div>
        </div>
      </form>
    </main>
  </div> 
</div> <!-- end content of GPU Manager-->

