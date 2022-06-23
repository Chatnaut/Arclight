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
  <form action="" method="POST">
    <div class="content">
      <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
          <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                              echo "card-dark";
                            } ?>">
            <!-- make button to start ssh -->
            <button name="start_ssh" class="btn btn-primary" id="start-ssh">Start SSH</button>

            <div class="card-body" id="sshframe">
              <!-- <iframe src="https://3.111.98.248:4433/" height="600px" width="100%" frameborder="0"></iframe> -->
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </form>
</main>
<!-- end content of physical GPUs -->

<?php
//Setting the SSL Certificate file path
$userid = $_SESSION['userid'];
$sql = "SELECT value FROM arclight_config WHERE name = 'cert_path' AND userid = '$userid' LIMIT 1;";
$result = $conn->query($sql);
// Extracting the record
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $cert_path = $row['value']; //sets value from database
  }
}
if ($cert_path) {
  $cert_option = "--certfile=" . $cert_path; //--cert is option used in noVNC connection string
} else {
  $cert_option = ""; //sets default location if nothing in database
}

//Setting the SSL Certificate file path
$sql = "SELECT value FROM arclight_config WHERE name = 'key_path' AND userid = '$userid' LIMIT 1;";
$result = $conn->query($sql);
// Extracting the record
if (mysqli_num_rows($result) != 0) {
  while ($row = $result->fetch_assoc()) {
    $key_path = $row['value']; //sets value from database
  }
}
if ($key_path) {
  $key_option = "--keyfile=" . $key_path; //--key is option used in noVNC connection string
} else {
  $key_option = ""; //will ignore key file if nothing in database
}

// start a https server, certfile and keyfile must be passed e.g wssh --certfile='/path/to/cert.crt' --keyfile='/path/to/cert.key'

// shell_exec("wssh $cert_option $key_option --log-file-prefix=../../logs/webssh.log -a 2>&1");

// 'wssh --certfile=/etc/letsencrypt/live/arc.chatnaut.com/fullchain.pem --keyfile=/etc/letsencrypt/live/arc.chatnaut.com/privkey.pem'

?>

<script>
    const hostname = window.location.hostname;

      // change protocol according to the localhost protocol
      var protocol = window.location.protocol;
    if (protocol == "https:") {
      var port = "3000";
    } else {
      var port = "3001";
    }

  //send cert_option and key_option to backend via axios
  document.getElementById("start-ssh").onclick = async () => {
    try{
    const data = await axios.post(`${protocol}//${hostname}:${port}/api/terminal/wssh`, {
      cert_option: '<?php echo $cert_option; ?>',
      key_option: '<?php echo $key_option; ?>'
    });
    console.log(data);
    }catch(error){
      console.log(error);
      
    }


  }
</script>