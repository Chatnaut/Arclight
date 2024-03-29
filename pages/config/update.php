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

// We are now going to grab any GET/POST data and put in in SESSION data, then clear it.
// This will prevent duplicatig actions when page is reloaded.
if (isset($_POST['update'])) {
  $_SESSION['update'] = $_POST['update'];
  unset($_POST);
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// Add the header information
require('../header.php');

// Domain Actions
if (isset($_SESSION['update'])) {
  $path = exec("which git"); //determine the absolute path to git
  ($path == "") ? $notification = "It does not appear as though git is installed" : $notification = "";
  //If git is not installed, then do not run the git commands
  if ($path != "") {
    //$tmp = shell_exec("cd .. && cd .. && $path pull 2>&1"); //run git at the web root directory. Use shell_exec to display all the output, not just last line. Redirect STDERR and STDOUT to variable

    $tmp = shell_exec("cd .. && cd .. && $path init 2>&1");
    $tmp = shell_exec("cd .. && cd .. && $path remote add origin https://github.com/Chatnaut/Arclight.git 2>&1");
    // $setOrigin = shell_exec("cd .. && cd .. && $path remote set-url origin https://github.com/Chatnaut/Arclight.git 2>&1");
    $fetchOrigin = shell_exec("cd .. && cd .. && $path fetch origin develop 2>&1");
    $resetOrigin = shell_exec("cd .. && cd .. && $path reset --hard origin/develop 2>&1");
  }
}

// $arrayLatest = $_SESSION['update_version'];
$arrayExisting = file('version.php');
$existingExploded = explode('.', $arrayExisting[1]);
// $latestExploded = explode('.', $arrayLatest[0]);

// if ($existingExploded >= $latestExploded) {
//   //Remove session variables so that if page reloads it will not perform actions again
//   unset($_SESSION['update']);
//   unset($_SESSION['update_available']);
// }

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
          <span class="card-title">Software Update</span>
        </div>
        <div class="card-body">
          <h5>Installed version: <?php echo $arrayExisting[1]; ?></h5>
          <input type="submit" name="update" value="Update Now" class="btn btn-warning" style="display: none;">
          <br>
          <pre><?php echo $fetchOrigin; ?></pre>
          <pre><?php echo $resetOrigin; ?></pre>
          <br>

          <?php
          //Display the changelog on the update page
          $changelog = file('../../changelog.php');
          $length = count($changelog);
          for ($i = 1; $i < $length; $i++) { //starting at index 1, 0 index is a php line.
            print $changelog[$i] . "<br />";
          }
          ?>
        </div> <!-- end card body -->
      </div> <!-- end card -->
    </div>
  </form>
</main>
</div>
</div> <!-- end content -->
<script>
  //check from session storage if there is an update available
  (function() {
    if (sessionStorage.getItem('update-available') == 'true') {
      const update = sessionStorage.getItem('update-version');
      const flag = sessionStorage.getItem('update-flag');

      document.querySelector('h5').insertAdjacentHTML('afterend', `<h5 class="flag">Status: There is a <span class="badge">${flag}</span> update available!</h5>
      <p>The newest release is ${update}</p>`);
      if (flag == 'major') {
        document.querySelector('.badge').classList.add('badge-success');
      } else if (flag == 'minor') {
        document.querySelector('.badge').classList.add('badge-dark');
      } else {
        document.querySelector('.badge').classList.add('badge-light');
      }
      document.querySelector('input[type="submit"]').style.display = 'block';
    } else {
      sessionStorage.removeItem('update-available');
      sessionStorage.removeItem('update-version');
      document.querySelector('h5').insertAdjacentHTML('afterend', `<h5>Status: You are running the lastest version of arclight.</h5>`);
    }
  })();
</script>
<?php
require('../footer.php');
?>