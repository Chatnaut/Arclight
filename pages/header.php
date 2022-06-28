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
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <!--gpu manager material icons -->
  <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
  <!-- Font Awesome 5.10.0 cdn -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" integrity="sha512-PgQMlq+nqFLV4ylk1gwUOgm6CtIIXkKwaIHp/PAIWHzig/lKZSEGKEysh0TCVbHJXCLN7WetD8TFecIky75ZfQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Btn Main and OScard Styles -->
  <link href="../../dist/css/buttons.css" rel="stylesheet">
  <link href="../../assets/css/oscard.css" rel="stylesheet">
  <!-- fontawesome icons-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<?php

require('../config/config.php');
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