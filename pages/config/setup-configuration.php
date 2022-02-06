<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If the database config.php file exists already redirect to index.php
$path = realpath(__DIR__) . "/config.php";
if (file_exists($path)) {
  header('Location: ../../index.php');
}

// Database names should be basic string characters without spaces or symbols
function clean_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = str_replace(' ', '', $data);
  $data = filter_var($data, FILTER_SANITIZE_STRING);
  return $data;
}

// Check for POST data, then create config.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  //Capturing the POST Data
  $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
  $password = $_POST['password']; //do not need to sanitize because it will be hashed
  $db_name = clean_input($_POST['db_name']);
  $db_user = clean_input($_POST['db_user']);
  $db_password = $_POST['db_password'];
  $db_host = clean_input($_POST['db_host']);

  //Test the database connection information
  $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

  if ($conn->connect_error) {
    $error = "Unable to connect to database";
  } else {
    //Creating the SQL for the users tables
    $sql = "CREATE TABLE arclight_users (
        userid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username varchar(255),
        email varchar(255),
        password varchar(255))";

    //Test to see if we can create the table for users
    if ($conn->query($sql) === TRUE) {

      //Use apps/password_compat for PHP version 5.4. Needed for CentOS 7 default version of PHP
      if (version_compare(PHP_VERSION, '5.5.0', '<')) {
        require('../../apps/password_compat_vm/lib/password.php');
      }

      //Hash and salt password with bcrypt
      $hash = password_hash($password, PASSWORD_BCRYPT);

      //Test the SQL statement for adding the admin user
      if ($conn->query($sql) === TRUE) {

        // Create the connection information for config.php file
        $config_string = "<?php
            // Setting up the Database Connection
            \$db_host = '$db_host';
            \$db_user = '$db_user';
            \$db_password = '$db_password';
            \$db_name = '$db_name';
            \$conn = new mysqli(\$db_host, \$db_user, \$db_password, \$db_name);
            if (\$conn->connect_error) {
              die(\"Connection failed: \" . \$conn->connect_error);
            }
            ?>";

        //Create config.php file
        $config_file = "config.php";
        $config_create = file_put_contents($config_file, $config_string);

        //If config.php was created move to index.php
        if ($config_create) {
          header('Location: ../../index.php');
        } else {
          $error = "Unable to create config.php. Check folder permissions";
        }
      } else {
        $error = "Error: " . $conn->error;
      }
    } else {
      $error = "Error: " . $conn->error;
    }
  } //End else statement checking for connection error
} // End if statement for POST data
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="../../assets/img/favicon.png">

  <title>Arclight - Configuration Setup</title>

  <!-- Bootstrap core CSS -->
  <link href="../../dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="../../assets/css/form-template.css" rel="stylesheet">
  <link href="../../dist/css/buttons.css" rel="stylesheet">

</head>

<body>
  <form class="form-signin" method="post" action="">
    <div class="text-center mb-4">
      <?php
      if ($error) {
        $error = htmlentities($error);
        echo "<h1 class=\"h3 mb-3 font-weight-normal\">$error</h1>";
      }
      ?>
      <img class="mb-4" src="../../assets/img/arclight-dark.svg" alt="" width="300" height="200">
      <h1 class="h3 mb-3 font-weight-normal">Create an admin account</h1>
    </div>

    <!-- -------------- USER ACCOUNT INFORMATION -------------- -->
    <div class="form-label-group">
      <input type="text" name="username" id="inputUsername" class="form-control" placeholder="" required autofocus>
      <label for="inputUsername">Username</label>
    </div>

    <div class="form-label-group">
      <input type="password" name="password" id="password" class="form-control" placeholder="" required>
      <label for="inputPassword">Password</label>
    </div>


    <!-- -------------- DATABASE CONFIGURATION INFORMATION -------------- -->
    <div class="text-center mb-4">
      <h1 class="h3 mb-3 font-weight-normal">Configure the database</h1>
    </div>

    <div class="form-label-group">
      <input type="text" name="db_name" id="inputDatabaseName" class="form-control" placeholder="arclight" required autofocus>
      <label for="inputDatabaseName">Database Name</label>
    </div>

    <div class="form-label-group">
      <input type="text" name="db_user" id="inputDatabaseUser" class="form-control" placeholder="username" required>
      <label for="inputDatabaseUser">Database User</label>
    </div>

    <div class="form-label-group">
      <input type="password" name="db_password" id="inputDatabasePassword" class="form-control" placeholder="" required>
      <label for="inputDatabasePassword">Database Password</label>
    </div>

    <div class="form-label-group">
      <input type="text" name="db_host" id="inputDatabaseHost" class="form-control" placeholder="localhost" value="localhost" required>
      <label for="inputDatabaseHost">Database Host</label>
    </div>

    <div class="center">
      <button class="log-btnlong btn-2" type="submit">Submit</button>
    </div>

    <p class="mt-5 mb-3 text-muted text-center">&copy;
      <script>
        document.write(new Date().getFullYear())
      </script>, Arclight
    </p>
  </form>
</body>
</html>