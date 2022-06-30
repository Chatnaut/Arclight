<?php
// If the SESSION has not started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  header('Location: ../sign-in.php');
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
  unset($_POST);
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

require('config.php');

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