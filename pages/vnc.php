<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
    session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])){
  header('Location: login.php');
}

$protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$url = $protocol . $_SERVER['HTTP_HOST'];
$token = $_GET['token'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/arclight-dark.svg">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
    Arclight Dashboard
    </title>
  </head>

  <body>
    <iframe src="<?php echo $url; ?>:6080/vnc_lite.html?path=&scale=yes&token=<?php echo $token ?>" style="position:fixed; top:0px; bottom:0px; right:0px; width: 100%; border: none; margin:0; padding:0; overflow: hidden; z-index:999999; height: 100%;"></iframe>
  </body>
</html>
