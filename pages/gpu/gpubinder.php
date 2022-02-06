<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../login.php');
}
require('../header.php');
require('../navbar.php');
require('../footer.php');
require('../config/config.php');


// This function is used to prevent any problems with user form input
function clean_input($data)
{
  $data = trim($data); //remove spaces at the beginning and end of string
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = str_replace(' ', '', $data); //remove any spaces within the string
  $data = filter_var($data, FILTER_SANITIZE_STRING);
  return $data;
}

if (isset($_POST['action'])) {
  $_SESSION['action'] = $_POST['action'];
  $_SESSION['pciaddr'] = clean_input($_POST['pciaddr']);
  $_SESSION['UUID'] = $_POST['UUID'];
  $_SESSION['domain_name'] = $_POST['domain_name'];
  // unset($_POST);

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
$userid = $_SESSION['userid'];


?>


<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <h3 class="h3">GPU</h3>
  </div>

  <form action="" method="POST">
    <div class="content">
      <div class="row">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

          <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                              echo "card-dark";
                            } ?>">
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



<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <form action="" method="POST">
    <div class="content">
      <div class="row">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

          <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                              echo "card-dark";
                            } ?>">
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


<!-- ------------------------------------------------------------------------------------------------------------ -->
<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <form action="" method="POST">
    <div class="content">
      <div class="row">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

          <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                              echo "card-dark";
                            } ?>">
            <div class="card-header">
              <span class="card-title"></span>
            </div>
            <div class="card-body">

              <div class="table-responsive">
                <table class="table">
                  <thead class="text-none">
                    <th>
                      <button type="button" class="custom-btn btn-2" data-toggle="modal" data-target="#attachpci-modal">Attach</button>
                      <button type="button" class="custom-btn btn-2" data-toggle="modal" data-target="#detachpci-modal">Detach</button>
                    </th>
                  </thead>
                  <tbody>
                    <!-- start attachpci project list -->
                    <?php
                    if (isset($_POST['attachpci'])) {
                      $sql = "CREATE TABLE IF NOT EXISTS arclight_gpu (
                            sno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            userid INT,
                            pciaddr varchar(255),    
                            action varchar(255),
                            domain_name varchar(255),
                            dt DATETIME)";
                      $tablesql = mysqli_query($conn, $sql);

                      if ($_POST['pciaddr'] && $_POST['domain_name'] != "") {
                        $userid = $_SESSION['userid'];
                        $pciaddr = $_POST['pciaddr'];
                        $domain_name = $_POST['domain_name'];
                        $optionalargs = $_POST['optionalargs'];

                        $attachpci = exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py attach-pci '" . $optionalargs . "' '" . $pciaddr . "' '" . $domain_name . "'", $output, $return_var);
                        if (empty($return_var)) {
                          $action = "Attached";
                          echo "Successfully Passthrough to VM " . $domain_name;
                          $sql = "INSERT INTO arclight_gpu (userid, pciaddr, action, domain_name, dt) VALUES ('$userid', '$pciaddr', '$action', '$domain_name', current_timestamp())";
                          $result = $conn->query($sql);
                        } else {
                          echo "GPU Passthrough Error";
                        }
                      }
                    }
                    //  start detachpci project list
                    if (isset($_POST['detachpci'])) {
                      if ($_POST['pciaddr'] && $_POST['domain_name'] != "") {
                        $userid = $_SESSION['userid'];
                        $pciaddr = $_POST['pciaddr'];
                        $domain_name = $_POST['domain_name'];
                        $optionalargs = $_POST['optionalargs'];

                        $detachpci = exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py detach-pci '" . $optionalargs . "' '" . $pciaddr . "' '" . $domain_name . "'", $output, $return_var);
                        if (empty($return_var)) {
                          $action = "Detached";
                          echo "Succesfully Removed " . $domain_name;
                          $rsql = "DELETE FROM arclight_gpu WHERE pciaddr = '$pciaddr' AND domain_name = '$domain_name'";
                          $result = $conn->query($rsql);
                        } else {
                          echo "Passthrough Error 485";
                        }
                      }
                    }
                    ?>
                    <div class="container-xl">
                      <div class="table-responsive">
                        <div class="table-wrapper">
                          <div class="table-title">
                            <div class="row">
                              <table class="table table-striped table-hover">
                                <thead>
                                  <tr>
                                    <th>PCI Address</th>
                                    <th>Action</th>
                                    <th>VM</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php
                                  $selectquery = "SELECT * FROM arclight_gpu";
                                  $query = mysqli_query($conn, $selectquery);

                                  while ($res = mysqli_fetch_array($query)) {
                                  ?>
                                    <tr>
                                      <td><?php echo $res['pciaddr']; ?></td>
                                      <td>
                                        <?php
                                        if ($res['action'] == 'Attached') {
                                        ?>
                                          <span class="status text-success">&bull;</span> Attached
                                        <?php
                                        }  ?>
                                      </td>
                                      <td><?php echo $res['domain_name']; ?></td>
                                      <td>
                                        <?php if ($res['action'] == 'Attached') { ?>
                                          <!-- Redirecting customers directly to detach and remove page actions by their ids via GET -->
                                          <a href="../domain/dpci.php?pciid=<?php echo $res['sno']; ?>" class="delete" title="Detach" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>
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
  </form>
</main><!-- end content of attach and detach Pyhsical GPU -->

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">


  <!-- Trigger the modal with a button -->
  <button type="button" class="custom-btn btn-2" data-toggle="modal" data-target="#save-modal">Save</button>
  <button type="button" class="custom-btn btn-2" data-toggle="modal" data-target="#restore-modal">Restore</button>

  <?php
  if (isset($_POST['savegpuconf'])) {
    $sql = "CREATE TABLE IF NOT EXISTS arclight_gpuevents (
        sno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        userid INT,
        action varchar(255),
        conf char(50),    
        dt DATETIME)";
    $tablesql = mysqli_query($conn, $sql);

    $userid = $_SESSION['userid'];
    $filename = $_POST['filename'];
    if ($_POST['filename'] != "") {
      $creatgpuconf = shell_exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py save -o '" . $filename . "'.conf");
      if ($creatgpuconf == "") {
        $action = "Configuration File Created";
        echo $action;
        $sql = "INSERT INTO arclight_gpuevents (userid, action, conf, dt) VALUES('$userid', '$action', '$filename', current_timestamp());";
        $inserttablesql = mysqli_query($conn, $sql);
      } else {
        echo "Error creating file";
      }
    } else {
      echo "Please enter a filename";
    }
  }
  ?>

  <div id="save-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "modal-dark";
                                } ?>">
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
            <input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Save">
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- end content of Save file Configuration -->

  <?php
  $dsql = "SELECT * from arclight_gpuevents WHERE userid = '$userid'";
  $dresult = $conn->query($dsql);

  if (isset($_POST['restore'])) {
    if (!empty($_POST['restoreconf'])) {
      $rfilename = clean_input($_POST['restoreconf']);
      $restoregpuconf = shell_exec("cd /var/www/html/arclight/gpubinder && sudo ./nvidia-dev-ctl.py restore -o '" . $rfilename . "'.conf");
      if ($restoregpuconf == "") {
        $action = "GPU Configuration Restored";
        echo $action;

        $rsql = "DELETE FROM arclight_gpuevents WHERE conf = '$rfilename';";
        $result = $conn->query($rsql);

        // Delete the file from the directory after restoring it
        $file_pointer = unlink(__DIR__ . "/../../gpubinder/" . $rfilename . ".conf");
      } else {
        echo "Error While Restoring GPU Configuration";
      }
    }
  }
  ?>

  <div id="restore-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "modal-dark";
                                } ?>">
        <div class="modal-header">
          <h5 class="modal-title">Restore GPU Configuration</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="gpumodal">
          <form action="" method="post">
            <?php
            if (mysqli_num_rows($dresult) > 0) {
            ?>
              <select class="modeselect" name="restoreconf">
                <!-- <option value = "" selected> File Name</option>   -->
                <?php
                $i = 0;
                while ($DB_ROW = mysqli_fetch_array($dresult)) {
                ?>
                  <option value="<?php echo $DB_ROW["conf"]; ?>"><?php echo $DB_ROW["conf"]; ?>.conf</option>
              <?php
                  $i++;
                }
              } else {
                echo "Configuration File Not Found";
              }
              ?>
              </select>
              <br> <br> <input type="submit" name="restore" id="modalsv" value="Restore">
          </form>
        </div>
      </div>
    </div>
  </div><!-- end content of Restore file Configuration -->


  <div id="attachpci-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "modal-dark";
                                } ?>">
        <div class="modal-header">
          <h5 class="modal-title">Add GPU Passthrough</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="attachpci-conf" name="attachpci-conf" role="form" action="" method="post">
          <div class="modal-body">
            <div class="form-group">
              <label for="pciaddr">PCI Address</label>
              <input type="text" name="pciaddr" class="form-control">
            </div>
            <div class="form-group">
              <label for="domname">VM Name</label>
              <input type="text" name="domain_name" class="form-control">
            </div>
            <input type="hidden" name="attachpci" class="form-control">
          </div>
          </select>
          <select class="form-select" name="optionalargs">
            <option value="">None</option>
            <option value="--hotplug">Hotplug</option>
            <option value="--restart">Restart</option>
          </select>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Add">
          </div>
        </form>
      </div>
    </div>
  </div><!-- end content of Attach PCI modal-->

  <div id="detachpci-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "modal-dark";
                                } ?>">
        <div class="modal-header">
          <h5 class="modal-title">Remove GPU Passthrough</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="detachpci-conf" name="detachpci-conf" role="form" action="" method="post">
          <div class="modal-body">
            <div class="form-group">
              <label for="pciaddr">PCI Address</label>
              <input type="text" name="pciaddr" class="form-control">
            </div>
            <div class="form-group">
              <label for="domname">VM Name</label>
              <input type="text" name="domain_name" class="form-control">
            </div>
            <input type="hidden" name="detachpci" class="form-control">
          </div>
          </select>
          <select class="form-select" name="optionalargs">
            <option value="">None</option>
            <option value="--hotplug">Hotplug</option>
            <option value="--restart">Restart</option>
          </select>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Remove">
          </div>
        </form>
      </div>
    </div>
  </div><!-- end content of detach PCI modal-->


  <?php
  $pciids = $_GET['pciid'];
  $showquery = "SELECT * from arclight_gpu WHERE sno={$pciids} AND action = 'Attached'";
  $showdata = mysqli_query($conn, $showquery);
  $pciarrdata = mysqli_fetch_array($showdata);
  ?>
  <div id="detachpcibtn-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "modal-dark";
                                } ?>">
        <div class="modal-header">
          <h5 class="modal-title">Remove GPU Passthrough</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="detachpci-conf" name="detachpci-conf" role="form" action="" method="post">
          <select class="browser-default custom-select" name="pciaddr">
            <option value="<?php echo $pciarrdata['pciaddr']; ?>"><?php echo $pciarrdata['pciaddr']; ?></option>
          </select>
          <select class="browser-default custom-select" name="domain_name">
            <option value="<?php echo $pciarrdata['domain_name']; ?>"><?php echo $pciarrdata['domain_name']; ?></option>
          </select>

          <select class="form-select" name="optionalargs">
            <option value="">None</option>
            <option value="--hotplug">Hotplug</option>
            <option value="--restart">Restart</option>
          </select>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Remove">
          </div>
        </form>
      </div>
    </div>
  </div>
</main>