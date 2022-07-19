<?php
// If the SESSION has not started, start it now
if (!isset($_SESSION)) {
    session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])){
  header('Location: ../sign-in.php');
}

// We are now going to grab any POST data and put in in SESSION data, then clear it.
// This will prevent and reloading the webpage to resubmit and action.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['cert_path'] = $_POST['cert_path'];
    $_SESSION['themeColor'] = $_POST['theme_color'];
    $_SESSION['themeColorChange'] = $_POST['theme_color'];
    $_SESSION['language'] = $_POST['language'];
    $_SESSION['password'] = $_POST['password'];
    $_SESSION['confirm_password'] = $_POST['confirm_password'];
    unset($_POST);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include_once('config.php');

// Creating table if necessary to store setttings
$sql = "CREATE TABLE IF NOT EXISTS arclight_config ( sno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), value VARCHAR(255), userid int );";
$result = $conn->query($sql);

if (isset($_SESSION['cert_path'])) {
  //Capturing the POST Data
  $cert_path = $_SESSION['cert_path'];

  $sql = "SELECT name FROM arclight_config WHERE name = 'cert_path';";
  $result = $conn->query($sql);

  if (mysqli_num_rows($result) == 0 ) {
    $sql = "INSERT INTO arclight_config (name, value) VALUES ('cert_path', '$cert_path');";
    $result = $conn->query($sql);
  } else {
    $sql = "UPDATE arclight_config SET value = '$cert_path' WHERE name = 'cert_path';";
    $result = $conn->query($sql);
  }

  unset($_SESSION['ssl_path']);
} //End if statement for POST data check


if (isset($_SESSION['themeColorChange'])) {
  //Capturing the POST Data
  $themeColorChange = $_SESSION['themeColorChange'];
  $userid = $_SESSION['userid'];

  $sql = "SELECT name FROM arclight_config WHERE name = 'theme_color' AND userid = '$userid';";
  $result = $conn->query($sql);

  if (mysqli_num_rows($result) == 0 ) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('theme_color', '$themeColorChange', '$userid');";
    $result = $conn->query($sql);
  } else {
    $sql = "UPDATE arclight_config SET value = '$themeColorChange' WHERE name = 'theme_color' AND userid = '$userid';";
    $result = $conn->query($sql);
  }

  unset($_SESSION['themeColorChange']);
} //End if statement for POST data check


if (isset($_SESSION['language'])) {
  //Capturing the POST Data
  $language = $_SESSION['language'];
  $userid = $_SESSION['userid'];

  $sql = "SELECT name FROM arclight_config WHERE name = 'language' AND userid = '$userid';";
  $result = $conn->query($sql);

  if (mysqli_num_rows($result) == 0 ) {
    $sql = "INSERT INTO arclight_config (name, value, userid) VALUES ('language', '$language', '$userid');";
    $result = $conn->query($sql);
  } else {
    $sql = "UPDATE arclight_config SET value = '$language' WHERE name = 'language' AND userid = '$userid';";
    $result = $conn->query($sql);
  }

  //unset($_SESSION['language']);
} //End if statement for POST data check


if ($_SESSION['password'] === $_SESSION['confirm_password'] && $_SESSION['password'] != "") {
  $userid = $_SESSION['userid'];
  $password = $_SESSION['password']; 

  //Use apps/password_compat for PHP version 5.4. Needed for CentOS 7 default version of PHP
  if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require('../../apps/password_compat_vm/lib/password.php');
  }

  // Hash and salt password with bcrypt
  $hash = password_hash($password, PASSWORD_BCRYPT);

  // Adding the user
  $sql = "UPDATE arclight_users SET password='$hash' WHERE userid ='$userid'";
    // Executing the SQL statement
    if ($conn->query($sql) === TRUE) {
      //Unset the SESSION variables
    }
  }
  unset($_SESSION['password']);
  unset($_SESSION['confirm_password']);
  

  //Get the current noVNC cert path to use as placeholder for textbox
  $sql = "SELECT value FROM arclight_config WHERE name = 'cert_path' LIMIT 1;";
  $result = $conn->query($sql);
  if (mysqli_num_rows($result) != 0 ) {
    while ($row = $result->fetch_assoc()) {
      $cert_path = $row['value'];
    }
  } else {
    $cert_path = "/etc/ssl/self.pem";
  }


  // Time to bring in the header and navigation
  require('../header.php');
  require('../navbar.php');

?>



    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
        <!-- <h1 class="h2">Virtual Machine from XML</h1> -->
      </div>

      <form action="" method="POST">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

          <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?> ">

            <div class="card-header text-center">
              <span class="card-title">User Preferences</span>
            </div>
            <div class="card-body">

              <div class="row">
                <label class="col-3 col-form-label text-right">Language: </label>
                <div class="col-6">
                  <div class="form-group">
                    <select class="form-control" name="language">
                      <option value="english" <?php if ($_SESSION['language'] == "english") { echo "selected"; } ?> >English (English)</option>
                  <!--    <option value="spanish" <?php if ($_SESSION['language'] == "spanish") { echo "selected"; } ?> >Spanish (Espa ol)</option> -->
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <label class="col-3 col-form-label text-right">Theme: </label>
                <div class="col-6 checkbox-radios">
                  <div class="form-check form-check-inline">
                    <label class="form-check-label">
                      <input class="form-check-input" type="radio" name="theme_color" value="white" <?php if ($_SESSION['themeColor'] != "dark-edition") {echo "checked";} ?> > Standard
                      <span class="circle">
                        <span class="check"></span>
                      </span>
                    </label>
                  </div>
                  <div class="form-check form-check-inline">
                    <label class="form-check-label text-right">
                      <input class="form-check-input" type="radio" name="theme_color" value="dark-edition" <?php if ($_SESSION['themeColor'] == "dark-edition") {echo "checked";} ?> > Dark
                      <span class="circle">
                        <span class="check"></span>
                      </span>
                    </label>
                  </div>
                </div>
              </div>

              <br/><br/>

              <div class="row">
              <label class="col-3 col-form-label text-right">New Password: </label>
                <div class="col-6">
                  <div class="form-group">
                    <input type="password" placeholder="New Password" class="form-control" name="password" id="pass1" onfocusout="checkPassword();"/>
                  </div>
                </div>
              </div>

              <div class="row">
                <label class="col-3 col-form-label text-right">Confirm New Password: </label>
                <div class="col-6">
                  <div class="form-group">
                    <input type="password" placeholder="Confirm Password" class="form-control" name="confirm_password" id="pass2"  onkeyup="checkPassword();"/>
                  </div>
                </div>
              </div>

              <span id="confirmMessage" class="confirmMessage text-center"></span>

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

<script>
function checkPassword()
{
  //Store the password field objects into variables ...
  var pass1 = document.getElementById('pass1');
  var pass2 = document.getElementById('pass2');
  //Store the Confimation Message Object ...
  var message = document.getElementById('confirmMessage');
  //Set the colors we will be using ...
  var goodColor = "#66cc66";
  var badColor = "#ff6666";
  //Compare the values in the password field
  //and the confirmation field
  if(pass1.value == pass2.value){
    //The passwords match.
    //Set the color to the good color and inform
    //the user that they have entered the correct password
    pass2.style.backgroundColor = "#ffffff";
    message.style.color = goodColor;
    message.innerHTML = "<p>Passwords Match!</p>"
  }else{
    //The passwords do not match.
    //Set the color to the bad color and
    //notify the user.
    pass2.style.backgroundColor = badColor;
    message.style.color = badColor;
    message.innerHTML = "<p>Passwords Do Not Match!</p>"
  }
}
</script>

<?php
require('../footer.php');
?>


