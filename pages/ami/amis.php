<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])) {
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../sign-in.php');
}
require('../header.php');
require('../navbar.php');
require('../footer.php');
include_once('../config/config.php');


// This function is used to prevent any problems with user form input
function clean_input($data)
{
  $data = trim($data); //remove spaces at the beginning and end of string
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = str_replace(' ', '', $data); //remove any spaces within the string
  $data = filter_var($data, FILTER_SANITIZE_STRING);
  return $data;
}

if (isset($_POST['action'])) {
  $_SESSION['action'] = $_POST['action'];
  $_SESSION['pciaddr'] = clean_input($_POST['pciaddr']);
  $_SESSION['mdevtype'] = $_POST['mdevtype'];
  $_SESSION['UUID'] = $_POST['UUID'];
  $_SESSION['domain_name'] = $_POST['domain_name'];
  // unset($_POST);

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
$userid = $_SESSION['userid'];
?>
<link href="../../assets/css/uploader.css" rel="stylesheet">
<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                    echo "main-dark";
                                                                  } ?> ">
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <h3 class="h3">Arclight Machine Images (AMIs)</h3>
  </div>
  <!-- background color #0f0f0f if dark-edition -->
  <div class="container " style=<?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "background-color:#0f0f0f";
                                } ?>>
    <div class="wrapper" style=<?php if ($_SESSION['themeColor'] == "dark-edition") {
                                  echo "background-color:#0f0f0f";
                                } ?>>
      <header>Upload Custom Image ISO</header>
      <form action="#">
        <input class="file-input" type="file" name="file" hidden>
        <i class="fas fa-cloud-upload-alt"></i>
        <p>Browse File to Upload</p>
      </form>
      <section class="progress-area"></section>
      <section class="uploaded-area"></section>
    </div>
  </div>
  <form action="" method="POST" enctype="multipart/form-data">
    <div class="content">
      <div class="row">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

          <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                              echo "card-dark";
                            } ?>">
            <div class="card-header">
              <span class="card-title"></span>
            </div>
            <!-- ------- -->
            <div class="card-body">
              <div class="table-responsive">
                <table class="table">
                  <tbody>
                    <!-- start project list -->
                    <?php

                    ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</main>
<!-- end content of physical GPUs -->

<script>
  const form = document.querySelector("form"),
    fileInput = document.querySelector(".file-input"),
    progressArea = document.querySelector(".progress-area"),
    uploadedArea = document.querySelector(".uploaded-area");

  // form click event
  form.addEventListener("click", () => {
    fileInput.click();
  });

  fileInput.onchange = ({
    target
  }) => {
    let file = target.files[0]; //getting file [0] this means if user has selected multiple files then get first one only
    if (file) {
      let fileName = file.name; //getting file name
      if (fileName.length >= 12) { //if file name length is greater than 12 then split it and add ...
        let splitName = fileName.split('.');
        fileName = splitName[0].substring(0, 13) + "... ." + splitName[1];
      }
      uploadFile(fileName); //calling uploadFile with passing file name as an argument
    }
  }

  // file upload function
  function uploadFile(name) {
    let xhr = new XMLHttpRequest(); //creating new xhr object (AJAX)
    xhr.open("POST", "builder/upload.php"); //sending post request to the specified URL
    xhr.upload.addEventListener("progress", ({
      loaded,
      total
    }) => { //file uploading progress event
      let fileLoaded = Math.floor((loaded / total) * 100); //getting percentage of loaded file size
      let fileTotal = Math.floor(total / 1000); //gettting total file size in KB from bytes
      let fileSize;
      // if file size is less than 1024 then add only KB else convert this KB into MB
      (fileTotal < 1024) ? fileSize = fileTotal + " KB": fileSize = (loaded / (1024 * 1024)).toFixed(2) + " MB";
      let progressHTML = `<li class="row">
                          <i class="fas fa-file-alt"></i>
                          <div class="content">
                            <div class="details">
                              <span class="name">${name} &bull; Uploading</span>
                              <span class="percent">${fileLoaded}%</span>
                            </div>
                            <div class="progress-bar">
                              <div class="progress" style="width: ${fileLoaded}%"></div>
                            </div>
                          </div>
                        </li>`;
      // uploadedArea.innerHTML = ""; //uncomment this line if you don't want to show upload history
      uploadedArea.classList.add("onprogress");
      progressArea.innerHTML = progressHTML;
      if (loaded == total) {
        progressArea.innerHTML = "";
        let uploadedHTML = `<li class="row">
                            <div class="content upload">
                              <i class="fas fa-file-alt"></i>
                              <div class="details">
                                <span class="name">${name} &bull; Uploaded</span>
                                <span class="size">${fileSize}</span>
                              </div>
                            </div>
                            <i class="fas fa-check"></i>
                          </li>`;
        uploadedArea.classList.remove("onprogress");
        // uploadedArea.innerHTML = uploadedHTML; //uncomment this line if you don't want to show upload history
        uploadedArea.insertAdjacentHTML("afterbegin", uploadedHTML); //remove this line if you don't want to show upload history
      }
    });
    let data = new FormData(form); //FormData is an object to easily send form data
    xhr.send(data); //sending form data
  }
</script>