<?php
// If the SESSION has not started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  header('Location: ../login.php');
}

// This function is used to prevent any problems with user form input
function clean_input($data)
{
  $data = trim($data); //remove spaces at the beginning and end of string
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = str_replace(' ', '', $data); //remove any spaces within the string
  $data = str_replace('--', '', $data); //remove -- within the string
  $data = filter_var($data, FILTER_SANITIZE_STRING);
  return $data;
}

// We are now going to grab any POST data and put in in SESSION data, then clear it.
// This will prevent and reloading the webpage to resubmit and action.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $_SESSION['cert_path'] = clean_input($_POST['cert_path']);
  $_SESSION['key_path'] = clean_input($_POST['key_path']);
  $_SESSION['api_cert_path'] = clean_input($_POST['api_cert_path']);
  $_SESSION['api_key_path'] = clean_input($_POST['api_key_path']);
  unset($_POST);
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

require('config.php');
//.env file path
$env_file = "../../.env";

//getting user id from session
$userid = $_SESSION['userid'];

// Creating table if necessary to store setttings
$sql = "CREATE TABLE IF NOT EXISTS arclight_config ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), value VARCHAR(255), userid int, date datetime)";
$result = $conn->query($sql);

//If the VNC SSL Cert File Path has been updated or inserted------------------------------------------------------
if (isset($_SESSION['cert_path'])) {
  $cert_path = $_SESSION['cert_path'];
  unset($_SESSION['cert_path']);

  $sql = "SELECT name FROM arclight_config WHERE name = 'cert_path';";
  $result = $conn->query($sql);

  if (mysqli_num_rows($result) == 0) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('cert_path', '$cert_path', '$userid');";
    $result = $conn->query($sql);
  } else {
    $sql = "UPDATE arclight_config SET value = '$cert_path' WHERE name = 'cert_path' AND userid = '$userid';";
    $result = $conn->query($sql);
  }
}

//If the VNC SSL Key File Path has been updated or inserted
if (isset($_SESSION['key_path'])) {
  $key_path = $_SESSION['key_path'];
  unset($_SESSION['key_path']);

  $sql = "SELECT name FROM arclight_config WHERE name = 'key_path';";
  $result = $conn->query($sql);

  if (mysqli_num_rows($result) == 0) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('key_path', '$key_path', '$userid');";
    $result = $conn->query($sql);
  } else {
    $sql = "UPDATE arclight_config SET value = '$key_path' WHERE name = 'key_path' AND userid = '$userid';";
    $result = $conn->query($sql);
  }
}

//If the Arc API SSL Cert File Path has been updated or inserted
if (isset($_SESSION['api_cert_path'])) {
  $api_cert_path = $_SESSION['api_cert_path'];
  unset($_SESSION['api_cert_path']);

  $sql = "SELECT name FROM arclight_config WHERE name = 'api_cert_path';";
  $result = $conn->query($sql);

  //search if the cert_path is already in the .env file
  $get_env_content = file_get_contents($env_file);
  // finalise the regular expression, matching the whole line
  $search_api_cert = '/^API_CERT_PATH=(.*)$/m';
  // search the regular expression in the file
  $match_api_cert = preg_match($search_api_cert, $get_env_content, $matches);
  // if the cert_path is not in the .env file, then insert it

  //check if cert_path is in databse and .env if not then insert in databse and .env or update in databse and .env or if cert_path is in databse but not in .env then insert in .env or if cert_path is in .env but not in databse then insert in databse
  if (mysqli_num_rows($result) == 0 && $match_api_cert == 0) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('api_cert_path', '$api_cert_path', '$userid');";
    $result = $conn->query($sql);
    $env_string_cert = "\nAPI_CERT_PATH=$api_cert_path\n";
    $env_append_cert = file_put_contents($env_file, $env_string_cert, FILE_APPEND);
  } elseif (mysqli_num_rows($result) == 0 && $match_api_cert == 1) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('api_cert_path', '$api_cert_path', '$userid');";
    $result = $conn->query($sql);
  } elseif (mysqli_num_rows($result) == 1 && $match_api_cert == 0) {
    $sql = "UPDATE arclight_config SET value = '$api_cert_path' WHERE name = 'api_cert_path' AND userid = '$userid';";
    $result = $conn->query($sql);
    $env_string_cert = "\nAPI_CERT_PATH=$api_cert_path\n";
    $env_append_cert = file_put_contents($env_file, $env_string_cert, FILE_APPEND);
  } else {
    $sql = "UPDATE arclight_config SET value = '$api_cert_path' WHERE name = 'api_cert_path' AND userid = '$userid';";
    $result = $conn->query($sql);
    $env_string_cert = "API_CERT_PATH=$api_cert_path";
    $env_replace_cert = preg_replace('/API_CERT_PATH=.*/', $env_string_cert, file_get_contents($env_file));
    $env_append_cert = file_put_contents($env_file, $env_replace_cert);
    // if API_CERT_PATH is still not in the .env file, then insert it
    if($match_api_cert == 0){
      $env_string_cert = "\nAPI_CERT_PATH=$api_cert_path\n";
      $env_append_cert = file_put_contents($env_file, $env_string_cert, FILE_APPEND);
    }
  }
}

//If the Arc API SSL Key File Path has been updated or inserted
if (isset($_SESSION['api_key_path'])) {
  $api_key_path = $_SESSION['api_key_path'];
  unset($_SESSION['api_key_path']);

  $sql = "SELECT name FROM arclight_config WHERE name = 'api_key_path';";
  $result = $conn->query($sql);

  $serach_api_key = '/^API_KEY_PATH=(.*)$/m';
  $match_api_key = preg_match($serach_api_key, $get_env_content, $matches);

//check if key_path is in databse and .env if not then insert in databse and .env or update in databse and .env or if key_path is in databse but not in .env then insert in .env or if key_path is in .env but not in databse then insert in databse
  if (mysqli_num_rows($result) == 0 && $match_api_key == 0) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('api_key_path', '$api_key_path', '$userid');";
    $result = $conn->query($sql);
    $env_string_key = "\nAPI_KEY_PATH=$api_key_path\n";
    $env_append_key = file_put_contents($env_file, $env_string_key, FILE_APPEND);
  } elseif (mysqli_num_rows($result) == 0 && $match_api_key == 1) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('api_key_path', '$api_key_path', '$userid');";
    $result = $conn->query($sql);
  } elseif (mysqli_num_rows($result) == 1 && $match_api_key == 0) {
    $sql = "UPDATE arclight_config SET value = '$api_key_path' WHERE name = 'api_key_path' AND userid = '$userid';";
    $result = $conn->query($sql);
    $env_string_key = "\nAPI_KEY_PATH=$api_key_path\n";
    $env_append_key = file_put_contents($env_file, $env_string_key, FILE_APPEND);
  } else {
    $sql = "UPDATE arclight_config SET value = '$api_key_path' WHERE name = 'api_key_path' AND userid = '$userid';";
    $result = $conn->query($sql);
    $env_string_key = "API_KEY_PATH=$api_key_path";
    $env_replace_key = preg_replace('/API_KEY_PATH=.*/', $env_string_key, file_get_contents($env_file));
    $env_append_key = file_put_contents($env_file, $env_replace_key);
    // if API_KEY_PATH is still not in the .env file, then insert it
    if($match_api_key == 0){
      $env_string_key = "\nAPI_KEY_PATH=$api_key_path\n";
      $env_append_key = file_put_contents($env_file, $env_string_key, FILE_APPEND);
    }
  }
}


//Get the current noVNC cert path to use as placeholder for textbox------------------------------------------------------
$sql = "SELECT value FROM arclight_config WHERE name = 'cert_path' LIMIT 1;";
$result = $conn->query($sql);
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $cert_path = $row['value'];
  }
} else {
  $cert_path = "/etc/ssl/self.pem";
}

//Get the current noVNC key path to use as placeholder for textbox
$sql = "SELECT value FROM arclight_config WHERE name = 'key_path' LIMIT 1;";
$result = $conn->query($sql);
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $key_path = $row['value'];
  }
} else {
  $key_path = "";
}

//Get the current Arc API cert path to use as placeholder for textbox
$sql = "SELECT value FROM arclight_config WHERE name = 'api_cert_path' LIMIT 1;";
$result = $conn->query($sql);
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $api_cert_path = $row['value'];
  }
} else {
  $api_cert_path = "";
}

//Get the current Arc API key path to use as placeholder for textbox
$sql = "SELECT value FROM arclight_config WHERE name = 'api_key_path' LIMIT 1;";
$result = $conn->query($sql);
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $api_key_path = $row['value'];
  }
} else {
  $api_key_path = "";
}

// Time to bring in the header and navigation
require('../header.php');
require('../navbar.php');

?>



<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <!-- <h1 class="h2">Virtual Machine from XML</h1> -->
  </div>

  <form action="" method="POST">

    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

      <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                          echo "card-dark";
                        } ?> ">

        <div class="card-header text-center">
          <span class="card-title">Settings</span>
        </div>
        <div class="card-body">
          <!-- VNC Certificate -->
          <div class="row">
            <label class="col-3 col-form-label text-right">SSL Certificate File Path (VNC): </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="<?php echo $cert_path; ?>" class="form-control" name="cert_path" />
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label text-right">SSL Key File Path (VNC): </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="<?php echo $key_path; ?>" class="form-control" name="key_path" />
              </div>
            </div>
          </div>
          <!-- Arc API Certificate -->
          <div class="row">
            <label class="col-3 col-form-label text-right">SSL Certificate File Path (Arc API): </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="<?php echo $api_cert_path; ?>" class="form-control" name="api_cert_path" />
              </div>
            </div>
          </div>

          <div class="row">
            <label class="col-3 col-form-label text-right">SSL Key File Path (Arc API): </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="<?php echo $api_key_path; ?>" class="form-control" name="api_key_path" />
              </div>
            </div>
          </div>
        </div> <!-- end card body -->

        <div class="card-footer justify-content-center text-center">
          <button type="submit" class="btn btn-primary text-center">Submit</button>
        </div>

      </div> <!-- end card -->
    </div>
  </form>
</main>
</div>
</div> <!-- end content -->


<?php
require('../footer.php');
?>