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
  ?>
  <!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Arclight Dashboard</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<style>
<?php
// include '../../assets/css/rud.php';
?>
</style>
<script>
$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip();
});
</script>
</head>
<body>
<div class="container-xl">
    <div class="table-responsive">
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-5">
                        <h2>User <b>Management</b></h2>
                    </div>
                    <div class="col-sm-7">
                        <a href="#" class="btn btn-secondary"><i class="material-icons">&#xE147;</i> <span>Add New User</span></a>
                        <a href="#" class="btn btn-secondary"><i class="material-icons">&#xE24D;</i> <span>Export to Excel</span></a>						
                    </div>
                </div>
            </div>
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
                //    include '../../assets/css/rud.css';
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
                            <a href="dmdev.php?id=<?php echo $res['sno']; ?>" class="settings" title="Detach" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>
                            <?php } ?>

                            <?php if ($res['action'] == ('createmdev')||('detachmdev')){ ?>
                            <a href="#" class="delete" title="Suspend" data-toggle="tooltip"><i class="material-icons">&#xE5C9;</i></a>
                            <?php } ?>

                        </td> 
                    </tr>
                   <?php
                    }
                    ?>
                    
                    <!-- <tr>
                        <td>5</td>
                        <td><a href="#"><img src="/examples/images/avatar/5.jpg" class="avatar" alt="Avatar"> Martin Sommer</a></td>
                        <td>12/08/2017</td>                        
                        <td>Moderator</td>
                        <td><span class="status text-warning">&bull;</span> Inactive</td>
                        <td>
                            <a href="#" class="settings" title="Settings" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>
                            <a href="#" class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE5C9;</i></a>
                        </td>
                    </tr> -->
                </tbody>
            </table>
            <div class="clearfix">
                <div class="hint-text">Showing <b>5</b> out of <b>25</b> entries</div>
                <ul class="pagination">
                    <li class="page-item disabled"><a href="#">Previous</a></li>
                    <li class="page-item"><a href="#" class="page-link">1</a></li>
                    <li class="page-item"><a href="#" class="page-link">2</a></li>
                    <li class="page-item active"><a href="#" class="page-link">3</a></li>
                    <li class="page-item"><a href="#" class="page-link">4</a></li>
                    <li class="page-item"><a href="#" class="page-link">5</a></li>
                    <li class="page-item"><a href="#" class="page-link">Next</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>     
</body>
</html>