<?php
    
    $server = "localhost";
    $username = "arclight";
    $password = "77777777";
    $database = "arclight";
 

$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn){
//     echo "success";
// }
// else{
    die("error".mysqli_connect_error());
}



?>