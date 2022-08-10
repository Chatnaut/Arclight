<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../sign-in.php');
}
require('../header.php');
require('../navbar.php');
require('../footer.php');
include_once('../config/config.php');

//create a new instance of the DbManager class
$db = new DbManager();
$conn = $db->getConnection();
$userid = $_SESSION['userid'];

//Setting the SSL Certificate file path
$filter = ['name' => 'cert_path'];
$option = [];
$read = new MongoDB\Driver\Query($filter, $option);
$result = $conn->executeQuery("arclight.arclight_configs", $read);
$result = $result->toArray();
//get value from array
$cert_path = $result[0]->value;
if ($cert_path != "") {
  $cert_option = "--certfile=" . $cert_path; //--cert is option used in noVNC connection string
} else {
  $cert_option = "--certfile=/etc/ssl/fullchain.pem"; //sets default location if nothing in database
}

//Setting the SSL Certificate file path
$filter = ['name' => 'key_path'];
$option = [];
$read = new MongoDB\Driver\Query($filter, $option);
$result = $conn->executeQuery("arclight.arclight_configs", $read);
$result = $result->toArray();

//get value from array
$key_path = $result[0]->value;
if ($key_path != "") {
  $key_option = "--keyfile=" . $key_path; //--key is option used in noVNC connection string
} else {
  $key_option = "--keyfile=/etc/ssl/privkey.pem"; //sets default location if nothing in database
}

?>
<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <div class="d-flex flex-row-reverse flex-wrap flex-md-nowrap align-items-center">
    <div class="btn-toolbar mb-md-0 mr-3 pr-3">
      <div class="btn-group mr-3 pr-3">
        <button class="btn btn-sm btn-outline-secondary" id="start-ssh">Start SSH <i class="fa-solid fa-terminal"></i></button>
      </div>
    </div>
  </div>

  <div class="content" style="display:none;">
    <div class="row">
      <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card-body" id="sshframe"></div>
      </div>
    </div>
  </div>
  </div>
</main>


<script>
  const startssh = document.getElementById('start-ssh');
  const sshframe = document.getElementById('sshframe');
  const sshicon = document.getElementsByClassName('fa-solid fa-terminal');
  const cert = '<?php echo $cert_option; ?>';
  const key = '<?php echo $key_option; ?>';

  startssh.addEventListener('click', function(e) {
    e.preventDefault();
    axios.post(`/api/v1/terminal/wssh`, {
        cert_option: cert,
        key_option: key
      })
      .then(function(response) {
        if (response.data.success == 1 && response.status == 200) {
          console.log(response);
          document.getElementsByClassName('content')[0].style.display = 'block';
          sshframe.style.display = 'block';
          sshicon[0].style.color = '#32de84';
          sshframe.innerHTML = `<iframe src="${window.location.protocol}//${window.location.hostname}:4433/" height="538px" width="100%" frameborder="0"></iframe>`;
        } else {
          console.log(response.data.error);
          sshicon[0].style.color = '#ff0000';
        }
      })
      .catch(function(error) {
        console.log(error);
        sshicon[0].style.color = '#ff0000';

      });
  });
</script>