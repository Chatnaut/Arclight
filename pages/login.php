<?php
  // If the SESSION has not been started, start it now
  if (!isset($_SESSION)) {
      session_start();
  }
  //Grab post infomation and add new drive
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require('config/config.php');

    //Use apps/password_compat for PHP version 5.4. Needed for CentOS 7 default version of PHP
    if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require('../apps/password_compat_vm/lib/password.php');
    }

    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Creating the SQL statement
    $sql = "SELECT password, userid FROM arclight_users WHERE username = '$username' LIMIT 1;";

    // Executing the SQL statement
    $result = $conn->query($sql);

    // Extracting the record and storing the hash
    while ($row = $result->fetch_assoc()) {
      $hash = $row['password'];
      $userid = $row['userid'];
    }

    //Verifying the password to the hash in the database
    if (password_verify($password, $hash)) {
      //Set the username session to keep logged in
      $_SESSION['username'] = $username;
      $_SESSION['userid'] = $userid; //used to set items such as themeColor in index.php

      $arrayLatest = file('https://arclight.org/version.php'); //Check for a newer version of OpenVM
      $arrayExisting = file('config/version.php'); //Check the existing version of OpenVM
      $latestExploded = explode('.', $arrayLatest[1]); //Seperate Major.Minor.Patch
      $existingExploded = explode('.', $arrayExisting[1]); //Seperate Major.Minor.Patch
      $latest = $latestExploded[0] . $latestExploded[1] . $latestExploded [2];
      $existing = $existingExploded[0] . $existingExploded[1] . $existingExploded[2];

      //Compare each component Major, Minor, and Patch
      if ($latest > $existing) {
        $_SESSION['update_available'] = true;
        $_SESSION['update_version'] = $arrayLatest;
      }

      //Setting the user's theme color choice
      $sql = "SELECT value, userid FROM arclight_config WHERE name = 'theme_color';";
      $result = $conn->query($sql);
      // Extracting the record
      if (mysqli_num_rows($result) != 0 ) {
        while ($row = $result->fetch_assoc()) {
          if ($_SESSION['userid'] == $row['userid']){
            $_SESSION['themeColor'] = $row['value'];
          }
        }
      } else {
        $_SESSION['themeColor'] = "white";
      }

      //Setting the user's language choice
      $sql = "SELECT value, userid FROM arclight_config WHERE name = 'language';";
      $result = $conn->query($sql);
      // Extracting the record
      if (mysqli_num_rows($result) != 0 ) {
        while ($row = $result->fetch_assoc()) {
          if ($_SESSION['userid'] == $row['userid']){
            $_SESSION['language'] = $row['value'];
          }
        }
      } else {
        $_SESSION['language'] = "english";
      }

      //Send the user back to the page they came from or to index.php
      if(isset($_SESSION['return_location'])) {
        $return_url = $_SESSION['return_location'];
        unset($_SESSION['return_location']);
        header('Location: '.$return_url);
      } else {
        header('Location: ../index.php');
      }
    } else {
      //If credentials were not a correct match
      $ret = "Credentials are incorrect";
    }

    $conn->close();

  }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../assets/img/favicon.png">

    <title>Arclight Dashboard - Login Page</title>

    <!-- Bootstrap core CSS -->
    <link href="../dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../assets/css/login.css" rel="stylesheet">
  </head>

  <body>
    <form class="form-signin" method="post" action="">
      <div class="text-center mb-4">
        <img class="mb-4" src="../assets/img/squarelogo.png" alt="" width="100" height="100">
        <h1 class="h3 mb-3 font-weight-normal">Sign in to Arclight web console</h1>
      </div>

      <div class="form-label-group">
        <input type="text" name="username" id="inputUsername" class="form-control" placeholder="Username" required autofocus>
        <label for="inputUsername">Username</label>
      </div>

      <div class="form-label-group">
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <label for="inputPassword">Password</label>
      </div>


      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      <p class="mt-5 mb-3 text-muted text-center">&copy; 
        <script>
          document.write(new Date().getFullYear())
        </script>, chatnaut cloud
      </p>
    </form>
  </body>
</html>
