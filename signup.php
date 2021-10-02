<?php
    $showAlert = false;
    $showError = false;
//   // If the database config.php file exists already redirect to index.php
//   $path = realpath(__DIR__) . "pages/config/config.php";
//   if (file_exists($path)) {
//     header('Location: pages/index.php');
//   }

  // Database names should be basic string characters without spaces or symbols
  function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = str_replace(' ','',$data);
    $data = filter_var($data, FILTER_SANITIZE_STRING);
    return $data;
  }

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    include 'partials/_dbconnect.php';


        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $password = $_POST['password']; //do not need to sanitize because it will be hashed
        $cpassword = $_POST["cpassword"];
        // $exists = false;
        //check whether this username exists
        $existSql = "SELECT * FROM `arclight_users` WHERE username = '$username'";
        $result = mysqli_query($conn, $existSql);
        $numExistsRows = mysqli_num_rows($result);
        if($numExistsRows >0){
          // $exists = true;
          $showError = "Username already exists";
        }
        else{
          // $exists = false;
        
        if(($password == $cpassword) ){
        $hash  = password_hash($password, PASSWORD_DEFAULT);
      
        $sql = "INSERT INTO arclight_users (username, password)
          VALUES ('$username', '$hash');";
                  $result = mysqli_query($conn, $sql);
                  if($result){
                      $showAlert = true;
                      header('Location: pages/login.php');
                  }
             }
                  else{
                      $showError = "Passwords don't match";
                  }
           }
          }

 ?>


<!-- **/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/* -->
<!-- **/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/* -->
<!-- **/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/* -->
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="assets/img/favicon.png">

    <title>Arclight Web Console - Signup</title>

    <!-- Bootstrap core CSS -->
    <link href="dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/login.css" rel="stylesheet">
  </head>
  <body>


<div class="container">
    <form class="form-signin" method="post" action="">
      <div class="text-center mb-4">
        <img class="mb-4" src="assets/img/squarelogo.png" alt="" width="100" height="100">
        <h1 class="h3 mb-3 font-weight-normal">Create new account</h1>
      </div>

      <div class="form-label-group">
        <input type="text" name="username" id="inputUsername" class="form-control" placeholder="Username" required autofocus>
        <label for="inputUsername">Username</label>
      </div>

      <div class="form-label-group">
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <label for="inputPassword">Password</label>
      </div>

      <div class="form-label-group">
        <input type="password" name="cpassword" id="inputPassword" class="form-control" placeholder="Password" required>
        <label for="inputPassword">Confirm your password</label>
      </div>


      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign up</button>
      <!-- Alert from bootstrap ----------------------------------------------------------------------->
<?php
if ($showAlert){
echo ' <br> <div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success</strong> Your account is now created and you can login.
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div> '; 
}
if ($showError){
    echo ' <br> <div class="alert alert-primary d-flex align-items-center" role="alert">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
      <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
    </svg>
    <div>
    <strong>Error </strong>'.$showError. '
    </div>
  </div> '; 
    }
?>
<!-- Alert from bootstrap ends ----------------------------------------------------------------------->
      <p class="mt-5 mb-3 text-muted text-center">&copy; 
        <script>
          document.write(new Date().getFullYear())
        </script>, chatnaut cloud
      </p>
    </form>
    </div>
  </body>
</html>
