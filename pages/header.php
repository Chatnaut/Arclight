<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Web based tool to manage Libvirt and KVM virtual machines ">
    <meta name="author" content="">
    <link rel="icon" href="../../assets/img/favicon.png">

    <title>Arclight Dashboard</title>

    <!-- Bootstrap core CSS -->
    <link href="../../dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../../assets/css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/fontawesome.css" integrity="sha384-WK8BzK0mpgOdhCxq86nInFqSWLzR5UAsNg0MGX9aDaIIrFWQ38dGdhwnNCAoXFxL" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/brands.css" integrity="sha384-whKHCkwP9f4MyD1vda26+XRyEg2zkyZezur14Kxc784RxUU1E7HvWVYj9EoJnUV7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> <!--gpu manager material icons -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <!-- BTN Main Styles -->
    <link href="../../dist/css/buttons.css" rel="stylesheet">

  </head>



  <?php
    //Change name of tables if still using openvm
    require('../config/config.php');
    $sql = "select * from openvm_users;"; //check to see if openvm_users table exits
    $openvm_result = $conn->query($sql);
    //if openvm_users table exists and has any values, rename the tables to arclight
    if (mysqli_num_rows($openvm_result) != 0 ) {
    $sql = "RENAME TABLE openvm_users TO arclight_users";
    $rename_result = $conn->query($sql);
    }
    $sql = "select * from openvm_config;"; //check to see if openvm_users table exits
    $openvm_result = $conn->query($sql);
    //if openvm_users table exists and has any values, rename the tables to arclight
    if (mysqli_num_rows($openvm_result) != 0 ) {
    $sql = "RENAME TABLE openvm_config TO arclight_config";
    $rename_result = $conn->query($sql);
    }


    //bring in the language
    if ($_SESSION['language'] == "english") {
    require('../config/lang/english.php');
    } elseif ($_SESSION['language'] == "spanish") {
    require('../config/lang/spanish.php');
    } else {
    require('../config/lang/english.php');
    }

    //bring in the libvirt class and methods
    require('../libvirt.php');
    $lv = new Libvirt();

    //attempt to connect to system
    if ($lv->connect("qemu:///system") == false)
    die('<html><body>Cannot open connection to hypervisor</body></html>');

    //attempt to learn the server's hostname
    $hn = $lv->get_hostname();
    if ($hn == false)
    die('<html><body>Cannot get hostname</body></html>');

?>