<?php

// include database file
include_once 'config.php';

$dbname = 'arclight';
$collection = 'arclight_users';


//DB connection
$db = new DbManager();
$conn = $db->getConnection();

// // read all records
// $filter = [];
// $option = [];
// $read = new MongoDB\Driver\Query($filter, $option);

//fetch records
// $records = $conn->executeQuery("$dbname.$collection", $read);

//find password from arclight_users where object _id is 62d2f7872d533964874c33b8 and username is 'admin'
$filter = ['_id' => new MongoDb\BSON\ObjectID('62d2f7872d533964874c33b8'), 'username' => 'myadmin'];
$option = [];
$read = new MongoDB\Driver\Query($filter, $option);
$result = $conn->executeQuery("$dbname.$collection", $read);
$result = $result->toArray();


//get password from array
$password = $result[0]->password;

if($password == "$2y$10$15OjhcBAvzh1n3rhAWJ/o.LS1YrnplN4WdnUHk4hj80TjPPmDtBS"){
  echo  "Password is correct";
}else{
    echo "Password is incorrect";
    }



?>


