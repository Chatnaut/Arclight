<?php
wp_load_translations_early();

$protocol = wp_get_server_protocol();
header( "$protocol 503 Service Unavailable", true, 503 );
header( 'Content-Type: text/html; charset=utf-8' );
header( 'Retry-After: 30' );
?>
<!DOCTYPE html>
<head>
<link href="https://fonts.googleapis.com/css?family=Roboto+Slab|Slabo+27px" rel="stylesheet">
</head>

<body>
<div class="container">
  <div class="logo-row"><img src="http://lompocpolicefoundation.org/wp-content/uploads/2018/06/logo.png"/></div>
<h2>Maintenance Mode</h2>
<p>We'll be back... shortly</p>
      <div class="progress-bar">
      <span></span>
  </div>
</div>
</body>
<style>
<?php $upgrading = 1526402687; ?>
html {
  height: 100%;
}

body {
  /*
  background-image: radial-gradient(circle, #ffffff 20%, #ee661b);
  */
  background:black;
  min-height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.container {
  display: flex;
  flex-direction: column;
  text-align: center;
  padding: 40px;

}
  /*HIDES THE DEFAULT TEXT*/
h1 {
	display:none!important;
}

h2 {
  font-family: 'Roboto Slab', serif;
  font-size: 35px;
  margin-bottom: 10px;
  text-align: center;
  /*
  text-shadow: #333 1px 4px 3px;
  */
  color:#fff;
}

p {
  font-family: 'Slabo 27px', serif;
  font-size: 25px;
  text-transform:uppercase;
  font-weight:bold;  
  color:#fff;
}
  .logo-row img {
    max-width:800px;
    border-radius:10px;
    animation:logoGlow 1s linear infinite;
  }
  
  @keyframes logoGlow {
    0%, 100% {
      border:3px solid #ff0000;
      filter:drop-shadow(0 2px 12px #ff0000);
    }
    50% {
      border:3px solid #0000FF;
      filter:drop-shadow(0 0px 0px #0000FF);
    }
  }
/*
.progress-bar {
  align-self: center;
  height: 7px;
  width: 70%;
  position: relative;
  background: #ff5533;
  -moz-border-radius: 15px;
  -webkit-border-radius: 15px;
  border-radius: 15px;
  padding: 5px;
  margin-top: 15px;
  box-shadow: 1px 0 7px  #eca7ff;
}

.progress-bar span {
  width: 65%;
  display: block;
  height: 100%;
    -moz-border-radius: 8px;
  -webkit-border-radius: 8px;
  border-radius: 8px;
  background-color: #cc2288;
  
}
</style>
<script>
var maintenance_check = function() {
    var request = new XMLHttpRequest();
    request.open( 'HEAD', window.location, true );

    request.onload = function() {
        if ( this.status >= 200 && this.status < 400 ) {
            // Maintenance mode ended. Reload page.
            window.location.reload();
        } else {
            // Still in maintenance mode. Try again in 3 seconds.
            setTimeout( maintenance_check, 3000 );
        }
    };

    request.onerror = function() {
        // Connection error. Try again in 3 seconds.
        setTimeout( maintenance_check, 3000 );
    };

    request.send();
};
maintenance_check();
</script>