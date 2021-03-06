<?php

// ================  Load settings ================
$config = require "config.php";
//===================================================

require 'php/system.php';

$all_errors = array();

// ================  Get system info ================

$hostname = getSystemHostname($all_errors);
$ip = getLanIp($all_errors);
$cores = getCpuCoresNumber($all_errors);
$os = getOperatingSystem($all_errors);
$kernel = getKernel($all_errors);
$uptime = getUptime($all_errors);
$bootTime = getBootupTime($all_errors);

$cpumodel      = getCpuModel($all_errors);
$cpufrequency  = getCpuFrequency($all_errors);
$cpucache      = getCpuCacheSize($all_errors);
$cputemp       = getCpuTemperature($all_errors);

$cpudata = getCpuLoadData($all_errors);

$ramdata = getRamInfo($all_errors);

$swap = getSwapData($all_errors);
$network = getNetworkData($all_errors);
$disk = getDiskData($all_errors);

//===================================================

// Limit shown errors to max 8

$error_count = count($all_errors);
if ($error_count > 8) {
    $all_errors = array_slice($all_errors, 0, 7);
    $all_errors[] = $lang['THERE_WERE'] . " " . ($error_count - 7) . " " . $lang['ERRORS_NOT_SHOWN'];
}

$wtitle = $config["windowtitle"];
$wtitle = str_replace("{hostname}", $hostname, $wtitle);
$wtitle = str_replace("{ip}", $ip, $wtitle);
$wtitle = str_replace("{os}", $os, $wtitle);
$wtitle = str_replace("{kernel}", $kernel, $wtitle);

//===================================================

// Allow the user to specify the theme in the address

if (isset($_GET['theme'])) {
    $theme = basename($_GET['theme']);
    
    if(file_exists("css/themes/{$theme}.css"))
        $config['theme'] = $theme;
}
