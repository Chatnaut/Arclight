<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}
//Grab post infomation and add new drive
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
require('config/config.php');
$_SESSION['hostname'] = $hostname;


//   //Use apps/password_compat for PHP version 5.4. Needed for CentOS 7 default version of PHP
//   if (version_compare(PHP_VERSION, '5.5.0', '<')) {
//     require('../apps/password_compat_vm/lib/password.php');
//   }

//   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
//   $password = $_POST['password'];

//   // Creating the SQL statement
//   $sql = "SELECT password, userid ,username FROM arclight_users WHERE email = '$email' LIMIT 1;";

//   // Executing the SQL statement
//   $result = $conn->query($sql);

//   // Extracting the record and storing the hash
//   while ($row = $result->fetch_assoc()) {
//     $hash = $row['password'];
//     $userid = $row['userid'];
//     $username = $row['username'];
//   }

//   //Verifying the password to the hash in the database
//   if (password_verify($password, $hash)) {
//     //Set the username session to keep logged in
//     $_SESSION['username'] = $username;
//     $_SESSION['userid'] = $userid; //used to set items such as themeColor in index.php

//     $arrayLatest = file('https://arclight.chatnaut.com/version/'); //Check for a newer version of Arclight
//     $arrayExisting = file('config/version.php'); //Check the existing version of Arclight
//     $latestExploded = explode('.', $arrayLatest[0]); //Seperate Major.Minor.Patch
//     $existingExploded = explode('.', $arrayExisting[1]); //Seperate Major.Minor.Patch
//     $latest = $latestExploded[0] . $latestExploded[1] . $latestExploded[2];
//     $existing = $existingExploded[0] . $existingExploded[1] . $existingExploded[2];

//     //Compare each component Major, Minor, and Patch
//     if ($latest > $existing) {
//       $_SESSION['update_available'] = true;
//       $_SESSION['update_version'] = $arrayLatest;
//     }

//     //Setting the user's theme color choice
//     $sql = "SELECT value, userid FROM arclight_config WHERE name = 'theme_color';";
//     $result = $conn->query($sql);
//     // Extracting the record
//     if (mysqli_num_rows($result) != 0) {
//       while ($row = $result->fetch_assoc()) {
//         if ($_SESSION['userid'] == $row['userid']) {
//           $_SESSION['themeColor'] = $row['value'];
//         }
//       }
//     } else {
//       $_SESSION['themeColor'] = "white";
//     }

//     //Setting the user's language choice
//     $sql = "SELECT value, userid FROM arclight_config WHERE name = 'language';";
//     $result = $conn->query($sql);
//     // Extracting the record
//     if (mysqli_num_rows($result) != 0) {
//       while ($row = $result->fetch_assoc()) {
//         if ($_SESSION['userid'] == $row['userid']) {
//           $_SESSION['language'] = $row['value'];
//         }
//       }
//     } else {
//       $_SESSION['language'] = "english";
//     }

//     //Send the user back to the page they came from or to index.php
//     if (isset($_SESSION['return_location'])) {
//       $return_url = $_SESSION['return_location'];
//       unset($_SESSION['return_location']);
//       header('Location: ' . $return_url);
//     } else {
//       header('Location: ../index.php');
//     }
//   } else {
//     //If credentials were not a correct match
//     $ret = "Credentials are incorrect";
//   }

//   $conn->close();
// }
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="../assets/img/favicon.png">

  <title>Arclight Dashboard - Login Page</title>

  <!-- Bootstrap core CSS -->
  <link href="../dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="../assets/css/form-template.css" rel="stylesheet">
  <link href="../dist/css/buttons.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <form class="form-signin" method="" action="">
      <div class="text-center mb-4">
        <!-- <img src="../assets/img/arclight-dark.svg" class="rounded mx-auto d-block" alt="..."> -->
        <img class="mb-4" src="../assets/img/arclight-dark.svg" alt="" width="300" height="200">
        <h1 class="h3 mb-3 font-weight-normal">Sign in to Arclight web console</h1>
      </div>

      <div class="form-label-group">
        <input type="text" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <label for="inputEmail">Email address</label>
      </div>

      <div class="form-label-group">
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required autofocus>
        <label for="inputPassword">Password</label>
      </div>

      <div class="center">
        <button class="log-btnlong btn-2" type="submit">Sign in</button>
      </div>
      <p class="mt-5 mb-3 text-muted text-center">&copy;
        <script>
          document.write(new Date().getFullYear())
        </script>, chatnaut cloud
      </p>
    </form>
  </div>
  <!-- getting axios library  -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    // (function() {
    //   //set $hostname from config to the localStorage 
    //   localStorage.setItem('hostname', '<?php // echo $_SESSION['hostname']; ?>');
    // })()

    const formDOM = document.querySelector('.form-signin');
    const emailInputDOM = document.querySelector('#inputEmail');
    const passwordInputDOM = document.querySelector('#inputPassword');
    // const hostname = localStorage.getItem('hostname');
    const hostname = window.location.hostname;


    // change protocol according to the localhost protocol
    var protocol = window.location.protocol;
    if (protocol == "https:") {
      var port = "3000";
    } else {
      var port = "3001";
    }
    formDOM.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = emailInputDOM.value;
      const password = passwordInputDOM.value;
      try {
        const {
          data
        } = await axios.post(`${protocol}//${hostname}:${port}/api/arc/login`, {
          email,
          password
        });
        localStorage.setItem('token', data.token);
        localStorage.setItem('userid', data.user.userid);
        localStorage.setItem('username', data.user.username);
        setConfigSession();
        // console.log(data);

      } catch (error) {
        localStorage.removeItem('token');
        localStorage.removeItem('userid');
        localStorage.removeItem('username');
      }
    });
    //get auc inner join data
    const setConfigSession = async () => {
      const token = localStorage.getItem('token')
      const email = emailInputDOM.value;
      try {
        const {
          data
        } = await axios.get(`${protocol}//${hostname}:${port}/api/arc/auc/${email}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        })
        //iterate through array and set the config session
        data.message.forEach(element => {
          if (element.name == "theme_color") {
            theme_color = element.value;
          }
          if (element.name == "language") {
            language = element.value;
          }
          userid = element.userid;
          username = element.username;
        });
        //sending data to store in php session
        axios.post('sessions.php', {
          userid: userid,
          username: username,
          theme_color: theme_color,
          language: language
        }).then(function(response) {
          window.location.href = '../index.php';
        })
      } catch (error) {
        //call setUserSession function
        setUserSession();
      }
    }
    const setUserSession = async () => {
      try {
        axios.post('sessions.php', {
          userid: localStorage.getItem('userid'),
          username: localStorage.getItem('username'),
          theme_color: "white",
          language: "english"
        }).then(function(response) {
          window.location.href = '../index.php';
        })
      } catch (error) {
        console.log(error);
      }
    }
  </script>
</body>

</html>