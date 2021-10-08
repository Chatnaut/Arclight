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

  function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = str_replace(' ','',$data);
    $data = filter_var($data, FILTER_SANITIZE_STRING);
    return $data;
  }

  // We are now going to grab any GET/POST data and put in in SESSION data, then clear it.
  // This will prevent duplicatig actions when page is reloaded.
  if (isset($_GET['action'])) {
      $_SESSION['action'] = $_GET['action'];
      $_SESSION['pool'] = $_GET['pool'];
      $_SESSION['path'] = $_GET['path'];
      $_SESSION['filename'] = $_GET['filename'];
      $_SESSION['clone_name'] = $_GET['clone_name']; //Used for cloning volume
      $_SESSION['volume_image_name'] = clean_input($_GET['volume_image_name']); //Used for creating new volume
      $_SESSION['volume_size'] = $_GET['volume_size']; //Used for creating new volume
      $_SESSION['unit'] = $_GET['unit']; //Used for creating new volume
      $_SESSION['driver_type'] = $_GET['driver_type']; //Used for creating new volume
      $_SESSION['pool_name'] = clean_input($_GET['pool_name']); //Used for adding storage pool
      $_SESSION['pool_path'] = $_GET['pool_path']; //Used for adding storage pool

      header("Location: ".$_SERVER['PHP_SELF']);
      exit;
  }

  require('../header.php');
  require('../navbar.php');


  //Set all the $_SESSION variables as local variables
  $action = $_SESSION['action']; //grab the $action variable from $_SESSION
  $pool = $_SESSION['pool'];
  $path = base64_decode($_SESSION['path']); //path was encoded for passing through URL
  $filename = $_SESSION['filename'];
  $clone_name = $_SESSION['clone_name'];
  $volume_image_name = $_SESSION['volume_image_name'];
  $volume_size = $_SESSION['volume_size'];
  $volume_capacity = $_SESSION['volume_size']; //set to same as $volume_size
  $unit = $_SESSION['unit'];
  $driver_type = $_SESSION['driver_type'];
  $pool_name = $_SESSION['pool_name'];
  $pool_path = $_SESSION['pool_path'];


  //Unset all the $_SESSION variables
  unset($_SESSION['action']); //Unset the Action Variable to prevent repeats of action on page reload
  unset($_SESSION['pool']);
  unset($_SESSION['path']);
  unset($_SESSION['filename']);
  unset($_SESSION['clone_name']);
  unset($_SESSION['volume_image_name']);
  unset($_SESSION['volume_size']);
  unset($_SESSION['unit']);
  unset($_SESSION['driver_type']);
  unset($_SESSION['pool_name']);
  unset($_SESSION['pool_path']);


  //Cycle through all the action options
  if ($action == 'volume-delete') {
    $notification = $lv->storagevolume_delete($path) ? "" : 'Cannot delete volume: '.$lv->get_last_error();
  }

  if ($action == 'volume-clone') {
    $pool_res = $lv->get_storagepool_res($pool);
    $notification = $lv->storagevolume_create_xml_from($pool_res, $path, $filename, $clone_name) ? "" : 'Cannot clone volume: '.$lv->get_last_error();
  }

  if ($action == 'pool-delete') {
    $res = $lv->get_storagepool_res($pool);
    $notification = $lv->storagepool_undefine($res) ? "" : 'Cannot remove pool';
  }

  if ($action == 'pool-destroy') {
    $res = $lv->get_storagepool_res($pool);
    $notification = $lv->storagepool_destroy($res) ? "" : 'Cannot stop pool';
  }

  if ($action == 'pool-start') {
    $res = $lv->get_storagepool_res($pool);
    $notification = $lv->storagepool_create($res) ? "" : 'Cannot start pool';
  }

  if ($action == 'volume-create') {
    $notification = $lv->storagevolume_create($pool, $volume_image_name, $volume_capacity.$unit, $volume_size.$unit, $driver_type) ? "" : "Cannot create volume: ".$lv->get_last_error();
  }

  if ($action == 'storage-pool-add') {
    if (substr($pool_path, 0, 4) == "/var" || substr($pool_path, 0, 4) == "/mnt" || substr($pool_path, 0, 6) == "/media") {
      $xml = "
        <pool type='dir'>
          <name>$pool_name</name>
          <target>
            <path>$pool_path</path>
            <permissions>
            </permissions>
          </target>
        </pool>";
      $notification = $lv->storagepool_define_xml($xml) ? "" : "Cannot add storagepool: ".$lv->get_last_error();
    }
  }

  //pool-xml not yet configured
  if ($action == "pool-xml") {
    $poolname = "default";
    $info = $lv->get_storagepool_info($poolname);
    echo "<textarea>";
    echo $info['xml'];
    echo "</textarea>";
  }

?>

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
        <h3 class="h3">Storage</h3>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group mr-2">
            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#create-new-volume-modal"></i>Create Storage Volume</button>
            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#add-storage-pool-modal">Register Storage Pool</button>
          </div>
        </div>
      </div>

      <form action="" method="POST">

        <div class="content">
          <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

              <?php
              $pools = $lv->get_storagepools();

              if (empty($pools)) {
                ?>
        
                    <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
                      <div class="card-header">
                        <span class="card-title">Storage volumes </span>
                      </div>
                      <div class="card-body">
                      <p>There are no configured storage pools. Please <a href="#add-storage-pool-modal" data-toggle="modal" data-target="#add-storage-pool-modal">add</a> a new storage pool, then start it.</p>
                      <p>Storage pools are used to contain the storage volumes (disk drives) and ISO files of your virtual machines</p>
                      <br />
                      <a href="#add-storage-pool-modal" data-toggle="modal" data-target="#add-storage-pool-modal">Add Storage Pool</a><br /> <br />
                    </div>
                  </div>
           
                <?php
              } else {
                for ($i = 0; $i < sizeof($pools); $i++) {
                  //get the pool resource to use with refreshing the pool data
                  $res = $lv->get_storagepool_res($pools[$i]);
                  //refreshing the data before displaying because ISOs were not refreshing automatically and also the Available data was not correct after adding volumes
                  $msg = $lv->storagepool_refresh($res) ? "Pool has been refreshed" : "Error refreshing pool: ".$lv->get_last_error();
                  //getting the pool information to display the data in a table
                  $info = $lv->get_storagepool_info($pools[$i]);
                  $poolName = $pools[$i];
                  $act = $info['active'] ? 'Active' : 'Inactive';
                  ?>


           
                  <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
                    <div class="card-header">
                      <span class="card-title">Pool Name: <?php echo $pools[$i]; ?> </span>
                        <br />
                        <?php
                        echo "<strong>State:</strong> " . $lv->translate_storagepool_state($info['state']);
                        echo "&ensp; | &ensp;<strong>Capacity:</strong> " . $lv->format_size($info['capacity'], 2) ;
                        echo "&ensp; | &ensp;<strong>Allocation:</strong> " . $lv->format_size($info['allocation'], 2) ;
                        echo "&ensp; | &ensp;<strong>Available:</strong> " . $lv->format_size($info['available'], 2) ;
                        echo "&ensp; | &ensp;<strong>Path:</strong> " . $info['path'] ;
                        echo "&ensp; | &ensp;<strong>Actions:</strong> ";
                        if ($lv->translate_storagepool_state($info['state']) == "Running") {
                          echo "<a style=\"color:#fa8a05;\" href=\"?action=pool-destroy&amp;pool=$pools[$i]\"> Stop</a>";
                        }
                        if ($lv->translate_storagepool_state($info['state']) != "Running") {
                          echo "<a style=\"color:#fa8a05;\" href=\"?action=pool-start&amp;pool=$pools[$i]\"> Start</a>";
                          //echo "<a href=\"?action=pool-delete&amp;pool=$pools[$i]\"> | Remove</a>";
                          echo "<a style=\"color:#fa8a05;\" href=\"?action=pool-delete&amp;pool=$poolName\"> | Remove</a>";
                        }

                        ?>
                      </p>
                    </div>
                    <div class="card-body">

                      <div class="table-responsive">
                        <table class="table">
                          <thead class="text-none">
                            <tr>
                              <th>File Name</th>
                              <th>Type</th>
                              <th>Capacity</th>
                              <th>Allocation</th>
                              <th>Path</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            //run code only if there are drives in the storage pool
                            if ($info['volume_count'] > 0) {
                              $tmp = $lv->storagepool_get_volume_information($pools[$i]);
                              $tmp_keys = array_keys($tmp);
                              for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                                $capacity = $lv->format_size($tmp[$tmp_keys[$ii]]['capacity'], 2);
                                if ($capacity == 0)
                                  continue; //used to not display directories
                                $filename = $tmp_keys[$ii];
                                $path = base64_encode($tmp[$tmp_keys[$ii]]['path']);
                                echo "<tr>" .
                                  "<td>$filename</td>" .
                                  "<td>{$lv->translate_volume_type($tmp[$tmp_keys[$ii]]['type'])}</td>" .
                                  "<td>$capacity</td>" .
                                  "<td>{$lv->format_size($tmp[$tmp_keys[$ii]]['allocation'], 2)}</td>" .
                                  "<td>{$tmp[$tmp_keys[$ii]]['path']}</td>" .
                                  "<td><a href=\"#clone-modal\" data-toggle=\"modal\" data-target=\"#clone-modal\" data-clone-name=\"clone-$filename\" data-original-filename=\"$filename\" data-path=\"$path\" data-pool=\"$poolName\">Clone </a>" .
                                  " |  <a data-href=\"?action=volume-delete&amp;path=$path\" data-filename=\"$filename\" data-toggle=\"modal\" data-target=\"#confirm-delete-modal\" href=\"#confirm-delete-modal\"> Delete</a></td>" .


                                  "<td>
                                  <form action=\"volume-resize\" method=\"\">  

                                  <div class=\"form-outline\">
                                  <input type=\"number\" id=\"typeNumber\" class=\"form-control\" />
                                </div>
                                <br> <br> <input type = \"submit\" name = \"submit\" value = \"Resize\">  
                                </form> 
                                
                                </td>" .
                                  "</tr>";
                              }
                            }
                            ?>

                          </tbody>
                        </table>
                      </div>
                    </div> <!-- end card body -->
                  </div> <!-- end card -->
             


                  <?php } //ends the for loop for each storage pool
                } ?>
              
            </div>
          </div>
        </div>
      </form>
    </main>
  </div> 
</div> <!-- end content -->


<!-- Hidden modal for cloning a storage volume -->
<div id="clone-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Clone Storage Volume </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="cloneForm" name="clone" role="form">
				<div class="modal-body">				
					<div class="form-group">
						<label for="clone_name">Clone Name</label>
						<input type="text" name="clone_name" class="form-control">
          </div>
          <input type="hidden" name="path" class="form-control">
          <input type="hidden" name="pool" class="form-control">
          <input type="hidden" name="filename" class="form-control">
          <input type="hidden" name="action" value="volume-clone" class="form-control">
					<!--<div class="form-group">
						<label for="message">Message</label>
						<textarea name="message" class="form-control"></textarea>
					</div>			-->		
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" id="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for deleting a storage volume -->
<div id="confirm-delete-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
      <div class="modal-body">				
        <p id="message"></p>
      </div>
      <div class="modal-footer">					
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a class="btn btn-warning btn-ok">Delete</a>
      </div>
		</div>
	</div>
</div>


<!-- Hidden modal for creating a storage volume -->
<div id="create-new-volume-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Create New Volume </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="createNewVolumeForm" name="createNewVolume" role="form" action="">
				<div class="modal-body">				          
          <div class="row">
            <label class="col-3 col-form-label text-right">Volume Name: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="newVolume.qcow2" required="required" placeholder="Enter name for new volume image" class="form-control" name="volume_image_name" />
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Volume Size: </label>
            <div class="col-3">
              <div class="form-group">
                 <input type="number" class="form-control" required="required" value="40" min="1" name="volume_size">
              </div>
            </div>
            <div class="col-4 checkbox-radios">
              <div class="form-check form-check-inline">
                <label class="form-check-label text-right">
                  <input class="form-check-input" type="radio" name="unit" value="M"> MB
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <label class="form-check-label text-right">
                  <input class="form-check-input" type="radio" name="unit" value="G" checked="checked"> GB
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Driver Type: </label>
            <div class="col-6">
              <div class="form-group">
                <select  class="form-control" name="driver_type" onchange="newExtenstion(this.form)">
                  <option value="qcow2" selected>qcow2</option>
                  <option value="raw">raw</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Storage Pool: </label>
            <div class="col-6">
              <div class="form-group">
                <select  class="form-control" name="pool">
                  <?php
                    $pools = $lv->get_storagepools();
                    for ($i = 0; $i < sizeof($pools); $i++) {
                      $poolName = $pools[$i];
                      echo "<option value=\"$poolName\">$poolName</option>";
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <input type="hidden" name="action" value="volume-create" class="form-control">
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" id="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for adding a storage pool -->
<div id="add-storage-pool-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Add Storage Pool </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="addStoragePoolForm" name="addStoragePool" role="form">
				<div class="modal-body">				          
          <div class="row">
            <label class="col-3 col-form-label text-right">Pool Name: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="default" required="required" placeholder="Enter name for storage pool" class="form-control" name="pool_name" />
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Pool Path: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="/var/lib/libvirt/images" required="required" placeholder="Enter full filepath" class="form-control" name="pool_path" />
                <br /> * Only paths that start with <em>/var</em>, <em>/media</em>, or <em>/mnt</em> will be allowed
              </div>
            </div>
          </div>
          <input type="hidden" name="action" value="storage-pool-add" class="form-control">
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="btn btn-primary" id="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>



<?php
require('../footer.php');
?>

<script>
  //Set variables for clone modal
  $('#clone-modal').on('show.bs.modal', function(e) {
      var cloneName = $(e.relatedTarget).data('clone-name');
      var originalFilename = $(e.relatedTarget).data('original-filename');
      var path = $(e.relatedTarget).data('path');
      var pool = $(e.relatedTarget).data('pool');
      $(e.currentTarget).find('input[name="clone_name"]').val(cloneName);
      $(e.currentTarget).find('input[name="filename"]').val(originalFilename);
      $(e.currentTarget).find('input[name="path"]').val(path);
      $(e.currentTarget).find('input[name="pool"]').val(pool);
  });


  //Set variables and href for delete modal
  $('#confirm-delete-modal').on('show.bs.modal', function(e) {
    var filename = $(e.relatedTarget).data('filename');
    //$("#confirm-delete-modal .modal-dialog .modal-content .modal-body p").html("Are you sure you want to delete: "+ filename);
    $(e.currentTarget).find('p[id="message"]').html("Are you sure you wish to delete <strong>" + filename + "</strong>?");
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
  });


  //Set variables for clone modal
  $('#create-new-volume-modal').on('show.bs.modal', function(e) {
    var pool = $(e.relatedTarget).data('pool');
    $(e.currentTarget).find('input[name="pool"]').val(pool);
  });




  //Set submit action for clone modal
  $(document).ready(function(){	
    $("#cloneForm").submit(function(event){
      submitCloneForm();
      return false;
    });
  });

  //Define submit action for clone modal
  function submitCloneForm(){
    $.ajax({
      type: "POST",
      url: "storage-pools.php",
      cache:false,
      data: $('form#cloneForm').serialize(),
      success: function(response){
        $("#clone").html(response)
        $("#clone-modal").modal('hide');
      },
      error: function(){
        alert("Error");
      }
    });
  }

  

  //Set submit action for add storage pool modal
  $(document).ready(function(){	
    $("#addStoragePoolForm").submit(function(event){
      submitAddForm();
      return false;
    });
  });

  //Define submit action for add storage pool modal
  function submitAddForm(){
    $.ajax({
      type: "POST",
      url: "storage-pools.php",
      cache:false,
      data: $('form#addStoragePoolForm').serialize(),
      success: function(response){
        $("#addStoragePool").html(response)
        $("#add-storage-pool-modal").modal('hide');
      },
      error: function(){
        alert("Error");
      }
    });
  }

  
  //Changes the file extension when creating a new storage volume
  function newExtenstion(f) {
    var diskName = f.volume_image_name.value;
    diskName = diskName.replace(/\s+/g, '');
    var n = diskName.lastIndexOf(".");
    var noExt = n > -1 ? diskName.substr(0, n) : diskName;
    var driverType = f.driver_type.value;
    if (driverType === "qcow2"){
      var ext = ".qcow2";
      var fullDiskName = noExt.concat(ext);
      f.volume_image_name.value = fullDiskName;
    }
    if (driverType === "raw"){
      var ext = ".img";
      var fullDiskName = noExt.concat(ext);
      f.volume_image_name.value = fullDiskName;
    }
  }

</script>