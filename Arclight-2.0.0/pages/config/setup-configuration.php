<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If the database config.php and env file exists already redirect to index.php
$path = realpath(__DIR__) . "../../.env";
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
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $password = $_POST['password']; //do not need to sanitize because it will be hashed

  // include database file-----------------------------------------------------------------------
  include_once './config.php';

  //check if database connection is successful
  $db = new DbManager();
  $conn = $db->getConnection();

  //Hash and salt password with bcrypt
  $hash = password_hash($password, PASSWORD_BCRYPT);

  // insert record
  $insert = new MongoDB\Driver\BulkWrite();
  $insert->insert(['username' => $username, 'email' => $email, 'password' => $hash, 'role' => 'Enterprise', 'status' => 'active', 'createdAt' => new MongoDB\BSON\UTCDateTime()]);
  $result = $conn->executeBulkWrite("arclight.arclight_users", $insert);

  if ($result->getInsertedCount() > 0) {

    // Create the connection information for .env file
    $env_string = "PORT=3000
            AUTH_KEY=arclightsecretkey
            MONGO_URI=mongodb://localhost:27017/arclight
            SESSION_SECRET=MySuperSecretSession
            ADMIN_EMAIL=$email";

    //Create .env files
    $env_file = "../../.env";
    $env_create = file_put_contents($env_file, $env_string);

    //If config.php and .env files were created successfully redirect to index.php
    if ($env_create) {
      header('Location: ../../index.php');
    } else {
      $error = "Unable to create env. Check folder permissions";
    }
  } else {
    $error = "Unable to establish connection " . $conn->error;
  }
}

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
      <input type="email" name="email" id="inputEmail" class="form-control" placeholder="" required autofocus>
      <label for="inputEmail">Email address</label>
    </div>

    <div class="form-label-group">
      <input type="password" name="password" id="inputPassword" class="form-control" placeholder="" required autofocus>
      <label for="inputPassword">Password</label>
    </div>

    <!-- -------------- DATABASE CONFIGURATION INFORMATION -------------- -->
    <!-- <div class="text-center mb-4">
      <h1 class="h3 mb-3 font-weight-normal">Configure the database</h1>
    </div>

    <div class="form-label-group">
      <input type="text" name="db_name" id="inputDatabaseName" class="form-control" placeholder="arclight" required autofocus>
      <label for="inputDatabaseName">Database Name</label>
    </div>

    <div class="form-label-group">
      <input type="text" name="db_user" id="inputDatabaseUser" class="form-control" placeholder="username" required autofocus>
      <label for="inputDatabaseUser">Database User</label>
    </div>

    <div class="form-label-group">
      <input type="password" name="db_password" id="inputDatabasePassword" class="form-control" placeholder="" required autofocus>
      <label for="inputDatabasePassword">Database Password</label>
    </div>

    <div class="form-label-group">
      <input type="text" name="db_host" id="inputDatabaseHost" class="form-control" placeholder="localhost" value="localhost" required autofocus>
      <label for="inputDatabaseHost">Database Host</label>
    </div> -->

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