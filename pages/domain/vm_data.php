<?php



    require('../config/config.php');
  
   $sql = "SELECT * FROM arclight_vm";
   $tablesql = mysqli_query($conn, $sql);
   $z = mysqli_num_rows($tablesql);
echo $z;




?>





<!-- $sql = "CREATE TABLE IF NOT EXISTS arclight_vm (
      userid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      description varchar(255),
      username varchar(255),
      domain_type varchar(255),
      domain_name varchar(255),
      clock_offset varchar(255),
      os_platform varchar(255),
      vcpu INT,
      memory INT,
      memory_unit char(1),
      source_file_volume varchar(255),
      volume_image_name varchar(255),
      volume_capacity INT,
      volume_size INT,
      driver_type varchar(255),
      target_bus varchar(255),
      storage_pool varchar(255),
      existing_driver_type varchar(255),
      source_file_cd varchar(255),
      mac_address TEXT,
      model_type varchar(255),
      source_network varchar(255),
      xml_data TEXT);";
   $tablesql = mysqli_query($conn, $sql);
  
   $sql = " SELECT * FROM arclight_vm WHERE userid = '$userid';";
   $tablesql = mysqli_query($conn, $sql);

  
    $sql = "INSERT INTO `arclight_vm`(`userid`, `description`, `username`, `domain_type`, `domain_name`, `clock_offset`, `os_platform`, `vcpu`, `memory`, `memory_unit`, `source_file_volume`, `volume_image_name`, `volume_capacity`, `volume_size`, `driver_type`, `target_bus`, `storage_pool`, `existing_driver_type`, `source_file_cd`, `mac_address`, `model_type`, `source_network`, `xml_data`, `date`) VALUES('$userid', '$username', '$domain_type', '$domain_name' '$clock_offset', '$os_platform', '$vcpu', '$memory', '$memory_unit', '$source_file_volume', '$volume_image_name', '$volume_capacity', '$volume_size', '$driver_type', '$target_bus', '$storage_pool', '$existing_driver_type', '$source_file_cd, '$mac_address', '$model_type', $source_network, '$xml_data')";
    $tablesql = mysqli_query($conn, $sql);
   
   if($tablesql)
   echo "Maar diya table pe";
   else{
     echo "error";
   }
  
  }







   require('../config/config.php');
    $sql = "CREATE TABLE IF NOT EXISTS arclight_vm (
      userid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,     
      domain_name varchar(255));";
   $tablesql = mysqli_query($conn, $sql);
  

    $sql = "SELECT name FROM arclight_config WHERE name = 'theme_color' AND userid = '$userid';";
    $tablesql = $conn->query($sql);

  
    $sql = "INSERT INTO `arclight_vm`(`domain_name`) VALUES ('$domain_name')";
    $tablesql = mysqli_query($conn, $sql);
   
   if($tablesql)
   echo "Maar diya table pe";
   else{
     echo "error";
   }





   $sql = 'SELECT userid, username, domain_type, domain_name, clock_offset, os_platform, vcpu, memory, memory_unit, source_file_volume, volume_image_name, volume_capacity, volume_size, driver_type, target_bus, storage_pool, existing_driver_type, source_file_cd, mac_address, model_type, source_network, xml_data FROM arclight_vm';
   $tablesql = mysqli_query($conn, $sql); -->
