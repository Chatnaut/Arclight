<?php
if(!isset($_SESSION)){
    session_start();
}

// get the incoming POST data
$post = file_get_contents('php://input') ?? $_POST;

// decode the JSON data
$post = json_decode($post, true);

// assign the needed data from the POST object
$_SESSION["userid"] = $post["userid"];
$_SESSION["username"] = $post["username"];
$_SESSION["email"] = $post["email"];
$_SESSION["role"] = $post["role"];
$_SESSION["themeColor"] = $post["theme_color"];
$_SESSION["language"] = $post["language"];

//echo session data
// echo json_encode($_SESSION);
?>