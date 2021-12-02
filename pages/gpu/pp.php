<?php
// PHP program to delete a file named gfg.txt 
// using unlike() function 
   
$rfilename = "bitchwa"; 
   
    $file_pointer = unlink(__DIR__ . "/../../gpubinder/" .$rfilename. ".conf");
    if($file_pointer){
      echo "File deleted successfully";
    }
    else{
      echo "Error deleting file";
    }  
?>