<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/favicon.png" />
  <link rel="icon" type="image/png" href="../assets/img/favicon.png" />
  <title>Arclight By Chatnaut Cloud</title>
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Main Styling -->
  <link href="../assets/css/styles.css?v=1.0.2" rel="stylesheet" />
  <style>
    .api-status-dot {
      display: inline-block;
      width: 5px;
      height: 5px;
      vertical-align: 9px;
      pointer-events: none;
      border-radius: 50%;
      /* background-color: #a9a9a9; */
    }
    div.messages {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    ul.messages {
      list-style: none;
      /* min-width: 600px; */
      max-width: 600px;
      border-radius: 8px;
      overflow: hidden;
      margin: 16px;
    }
    ul.messages * {
      padding: 8px 12px;
      color: black;
    }
    .messages .error {
      background-color: #f87171;
    }
    .messages .success {
      background-color: #6ee7b7;
    }
    .messages .warning {
      background-color: #fcd34d;
    }
    .messages .info {
      background-color: #93c5fd;
    }
  </style>
</head>