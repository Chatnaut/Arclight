<?php

//Increase file upload size limit in PHP-Apache - https://www.cyberciti.biz/faq/increase-file-upload-size-limit-in-php-apache-app/

  $file_name =  $_FILES['file']['name']; //getting file name
  $tmp_name = $_FILES['file']['tmp_name']; //getting temp_name of file
  $file_up_name = time().$file_name; //making file name dynamic by adding time before file name
  move_uploaded_file($tmp_name, "images/".$file_up_name); //moving file to the specified folder with dynamic name
?>


