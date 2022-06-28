<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../login.php');
}
require('../header.php');
require('../navbar.php');
require('../footer.php');
require('../config/config.php');
?>
<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <div class="d-flex flex-row-reverse flex-wrap flex-md-nowrap align-items-center">
    <div class="btn-toolbar mb-md-0 mr-3 pr-3">
      <div class="btn-group mr-3 pr-3">
        <button class="btn btn-sm btn-outline-secondary" id="start-ssh">Start SSH  <i class="fa-solid fa-terminal"></i></button>
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

<?php
//Setting the SSL Certificate file path
$userid = $_SESSION['userid'];
$sql = "SELECT value FROM arclight_config WHERE name = 'cert_path' AND userid = '$userid' LIMIT 1;";
$result = $conn->query($sql);
// Extracting the record
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $cert_path = $row['value'];
  }
}
if ($cert_path) {
  $cert_option = "--certfile=" . $cert_path;
} else {
  $cert_option = "";
}

//Setting the SSL Certificate file path
$sql = "SELECT value FROM arclight_config WHERE name = 'key_path' AND userid = '$userid' LIMIT 1;";
$result = $conn->query($sql);
// Extracting the record
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $key_path = $row['value'];
  }
}
if ($key_path) {
  $key_option = "--keyfile=" . $key_path;
} else {
  $key_option = "";
}
?>

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