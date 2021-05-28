<?php
            // Setting up the Database Connection
            $db_host = 'localhost';
            $db_user = 'vmdashboard';
            $db_password = '77777777';
            $db_name = 'vmdashboard';
            $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
            if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
            }
            ?>