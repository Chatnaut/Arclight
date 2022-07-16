<?php

// include database file
include_once 'config.php';

//DB connection
$db = new DbManager();
$conn = $db->getConnection();
try{ 
    $dbs = $conn->listDatabases(); 
    echo '<pre>';
    print_r($dbs);
    echo '</pre>';
    // Or Nothing if you just wanna check for errors 
}
catch(Exception $e){
    echo "Unable to connect to Database at the moment ! ";
    exit();
}



