<?php
// If the SESSION has not started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  header('Location: ../sign-in.php');
}

// We are now going to grab any POST data and put in in SESSION data, then clear it.
// This will prevent and reloading the webpage to resubmit and action.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // $_SESSION['language'] = $_POST['language'];
  $_SESSION['password'] = $_POST['password'];
  $_SESSION['confirm_password'] = $_POST['confirm_password'];
  unset($_POST);
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

//getting user id from session
$userid = $_SESSION['userid'];

// Time to bring in the header and navigation
require('../header.php');
require('../navbar.php');
require_once('config.php');

//create a new instance of the DbManager class
$db = new DbManager();
$conn = $db->getConnection();
$filter = ['userid' => $userid, 'name' => 'theme_color'];
$read = new MongoDB\Driver\Query($filter);
$result = $conn->executeQuery("arclight.arclight_configs", $read);
$result = $result->toArray();

//set $_SESSION['themeColor'] to the value of the themeColor document in the database
$_SESSION['themeColor'] = $result[0]->value;

//password check
if ($_SESSION['password'] === $_SESSION['confirm_password'] && $_SESSION['password'] != "") {
  $userid = $_SESSION['userid'];
  $password = $_SESSION['password'];

  // Hash and salt password with bcrypt
  $hash = password_hash($password, PASSWORD_BCRYPT);

  $update = new MongoDB\Driver\BulkWrite();
  $update->update(['_id' => new MongoDB\BSON\ObjectID($userid)], ['$set' => ['password' => $hash]]);
  $result1 = $conn->executeBulkWrite('arclight.arclight_users', $update);
}
unset($_SESSION['password']);
unset($_SESSION['confirm_password']);
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <!-- <h1 class="h2">Virtual Machine from XML</h1> -->
  </div>

  <form class="" action="" method="">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
      <div class="card h-200 <?php if ($_SESSION['themeColor'] == "dark-edition") {
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
                <div class="input-group">
                  <input type="text" value="" class="form-control" name="cert_path" id="certpath" />
                  <div class="input-group-addon">
                    <i class="fas fa-sync-alt fa-xl" id="refresh_cert" style="margin: 20px 0px 0px 5px;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">SSL Key File Path (VNC): </label>
            <div class="col-6">
              <div class="input-group">
                <input type="text" value="" class="form-control" name="key_path" id="keypath" />
                <div class="input-group-addon">
                  <i class="fas fa-sync-alt fa-xl" id="refresh_key" style="margin: 20px 0px 0px 5px;"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="row pt-5 pb-5">
            <label class="col-3 col-form-label text-right">API Bearer Token: </label>
            <div class="col-6">
              <div class="input-group">
                <input type="text" value="" class="form-control" name="apitoken" id="apitoken" readonly />
              </div>
            </div>
          </div>
        </div> <!-- end card -->
      </div>
    </div>
  </form>
</main>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
  </div>
  <form action="" method="POST">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
      <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                          echo "card-dark";
                        } ?> ">
        <div class="card-header text-center">
          <span class="card-title">User Preferences</span>
        </div>
        <div class="card-body">
          <div class="row">
            <label class="col-3 col-form-label text-right">Language: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="language">
                  <option value="english" <?php if ($_SESSION['language'] == "english") {
                                            echo "selected";
                                          } ?>>English (English)</option>
                  <!--    <option value="spanish" <?php if ($_SESSION['language'] == "spanish") {
                                                    echo "selected";
                                                  } ?> >Spanish (Espa ol)</option> -->
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Theme: </label>
            <div class="col-6 checkbox-radios">
              <div class="form-check form-check-inline">
                <label class="form-check-label">
                  <input class="form-check-input" type="radio" name="theme_color" value="white">Standard
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <label class="form-check-label text-right">
                  <input class="form-check-input" type="radio" name="theme_color" value="dark-edition">Dark
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
            </div>
          </div>
          <br /><br />
          <div class="row">
            <label class="col-3 col-form-label text-right">New Password: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="password" placeholder="New Password" class="form-control" name="password" id="pass1" onfocusout="checkPassword();" />
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Confirm New Password: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="password" placeholder="Confirm Password" class="form-control" name="confirm_password" id="pass2" onkeyup="checkPassword();" />
              </div>
            </div>
          </div>
          <span id="confirmMessage" class="confirmMessage text-center"></span>
        </div> <!-- end card body -->
        <div class="card-footer justify-content-center text-center">
          <button type="submit" class="btn btn-primary btn-sm text-center">Change Password</button>
        </div>
      </div> <!-- end card -->
    </div>
  </form>
</main>
</div>
</div> <!-- end content -->

<script>
  const refresh_cert = document.getElementById('refresh_cert');
  const certpath = document.getElementById('certpath');
  const userid = localStorage.getItem('userid');
  const refresh_key = document.getElementById('refresh_key');
  const keypath = document.getElementById('keypath');
  const theme = document.getElementById('themecolor');
  const token = localStorage.getItem('token');
  const apitoken = document.getElementById('apitoken');

  //request send to axios post to update cert path on click
  refresh_cert.addEventListener('click', async () => {
    try {
      const response = await axios.post('/api/v1/config/arc_config', {
        name: 'cert_path',
        value: certpath.value,
        userid: userid
      }, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      if (response.data.success == 1) {
        refresh_cert.classList.remove('fa-sync-alt');
        refresh_cert.classList.add('fa-check');
        refresh_cert.style.color = '#32de84';
      } else {
        refresh_cert.style.color = 'red';
      }
    } catch (error) {
      console.log(error);
    }
  });

  //request send to axios post to update key path on click
  refresh_key.addEventListener('click', async () => {
    try {
      const response = await axios.post('/api/v1/config/arc_config', {
        name: 'key_path',
        value: keypath.value,
        userid: userid
      }, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      if (response.data.success == 1) {
        refresh_key.classList.remove('fa-sync-alt');
        refresh_key.classList.add('fa-check');
        refresh_key.style.color = '#32de84';
      } else {
        refresh_key.style.color = 'red';
      }
    } catch (error) {
      console.log(error);
    }
  });

  //request send to axios post to update theme on select
  document.addEventListener('input', (e) => {
    if (e.target.getAttribute('name') == 'theme_color') {
      try {
        const response = axios.post('/api/v1/config/arc_config', {
          name: 'theme_color',
          value: e.target.value,
          userid: userid
        }, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
      } catch (error) {
        console.log(`Error: ${error}`);
      }
      setTimeout(function() {
        location.reload();
      }, 1000);
    }
  });

  //get cert path and key path from array inside result object and add to input values
  const getConfig = async () => {
    try {
      apitoken.value = token;
      const response = await axios.get(`/api/v1/config/arc_config/${userid}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      response.data.result.forEach(element => {

        switch (element.name) {
          // case 'cert_path':
          //   certpath.value = element.value;
          //   break;
          // case 'key_path':
          //   keypath.value = element.value;
          //   break;
          case 'theme_color':
            if (element.value == 'dark-edition') {
              document.querySelector('input[value="dark-edition"]').setAttribute('checked', 'checked');
              document.querySelector('input[value="white"]').removeAttribute('checked');
            } else {
              document.querySelector('input[value="white"]').setAttribute('checked', 'checked');
              document.querySelector('input[value="dark-edition"]').removeAttribute('checked');
            }
            break;
        }
      });
      certpath.value = response.data.certs;
      keypath.value = response.data.keys;
    } catch (error) {
      console.log(error);
    }
  }
  getConfig();

  //check if passwords match
  function checkPassword() {
    var pass1 = document.getElementById('pass1');
    var pass2 = document.getElementById('pass2');
    var message = document.getElementById('confirmMessage');
    var goodColor = "#66cc66";
    var badColor = "#ff6666";
    if (pass1.value == pass2.value) {
      pass2.style.backgroundColor = "#ffffff";
      message.style.color = goodColor;
      message.innerHTML = "<p>Passwords Match!</p>"
    } else {
      pass2.style.backgroundColor = badColor;
      message.style.color = badColor;
      message.innerHTML = "<p>Passwords Do Not Match!</p>"
    }
  }
</script>

<?php
require('../footer.php');
?>