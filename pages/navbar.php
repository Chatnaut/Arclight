<?php 
function getOSInformation() { //https://stackoverflow.com/questions/1482260/how-to-get-the-os-on-which-php-is-running
    if (false == function_exists("shell_exec") || false == is_readable("/etc/os-release")) {
        return null;
    }

    $os         = shell_exec('cat /etc/os-release');
    $listIds    = preg_match_all('/.*=/', $os, $matchListIds);
    $listIds    = $matchListIds[0];

    $listVal    = preg_match_all('/=.*/', $os, $matchListVal);
    $listVal    = $matchListVal[0];

    array_walk($listIds, function(&$v, $k){
        $v = strtolower(str_replace('=', '', $v));
    });

    array_walk($listVal, function(&$v, $k){
        $v = preg_replace('/=|"/', '', $v);
    });

    return array_combine($listIds, $listVal);
}

$os_info = getOSInformation();
$host_os = $os_info['name'];

?>


<body class=" <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">
    <nav class="navbar navbar-dark bg-dark sticky-top flex-md-nowrap p-0 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">
      <a class="navbar-brand navbar-dark col-sm-3 col-md-2 mr-0" href="../../index.php"><img src="../../assets/img/squarelogo.png" width="28px"> &ensp; Dashboard</a>
      <!-- <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search"> -->
      <ul class="navbar-nav px-3">

          
          <li class="nav-item dropdown">
              <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                  Manage<span class="caret"></span>
                </a>
                <div class="dropdown-menu position-absolute dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="{{ route('logout') }}" >Account</a>
                    <a class="dropdown-item" href="{{ route('logout') }}" >Billing</a>
                    <a class="dropdown-item" href="{{ route('logout') }}" >Access (IAM)</a>
                    <a class="dropdown-item" href="{{ route('logout') }}" >Logout</a>
                    
                </div>
            </li>
            <li class="nav-item">
                <?php
                    if ($_SESSION['update_available'] == true) {
                        echo "<a class=\"nav-link\" style=\"color:orange;\" href=\"../config/update.php\">Update</a>";
                    } else {
                        echo "<a class=\"nav-link\" href=\"../config/update.php\">Update</a>";
                    }
                ?>
            </li>
            
        <!-- <li class="nav-item">
          <a class="nav-link" href="../config/settings.php">Settings</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="../config/preferences.php">Preferences</a>
        </li> -->

        <li class="nav-item dropdown">
             <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                Hi, <?php echo $_SESSION['username'];?><span class="caret"></span>
             </a>
             <div class="dropdown-menu position-absolute dropdown-menu-right" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ route('logout') }}" >Profile</a>
                <a class="dropdown-item" href="../config/preferences.php" >Preferences</a>
                <a class="dropdown-item" href="../config/settings.php" >Settings</a>
                <a class="dropdown-item" href="../../index.php?action=logout">Sign out</a>

             </div>
        </li>
      </ul>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-dark navbar-dark sidebar">
            
          <div class="sidebar-sticky">
            <ul class="nav flex-column mb-2">

                <li>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="true" class="dropdown-toggle text-muted host-heading">
                            <span style="font-size: 18px; color: #999; padding-right:2px;">
                                <?php
                                 if ($host_os == "Ubuntu")
                                 echo "<i class=\"fab fa-ubuntu\"></i>";
                                 elseif (preg_match('Red Hat', $host_os))
                                 echo "<i class=\"fab fa-redhat\"></i>";
                                 elseif ($host_os == "CentOS Linux")
                                 echo "<i class=\"fab fa-centos\"></i>";
                                 elseif ($host_os == "Fedora")
                                 echo "<i class=\"fab fa-fedora\"></i>";
                                 elseif (preg_match('SUSE', $host_os))
                                 echo "<i class=\"fab fa-suse\"></i>";
                                 else 
                                 echo "<i class=\"fab fa-linux\"></i>";
                                ?>
                            </span>      
                            localhost
                        </a>
                    </h6>
                    <ul class="collapse show list-unstyled" id="pageSubmenu">
                        <li class="nav-item">
                            <a class="nav-link" href="../host/host-info.php">
                                <span data-feather="server"></span>
                                Host Information
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../domain/domain-list.php">
                                <span data-feather="layers"></span>
                                Virtual Machines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../storage/storage-pools.php">
                                <span data-feather="database"></span>
                                Storage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../network/network-list.php">
                                <span data-feather="link-2"></span>
                                Networking
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" target="blank" href="../swmp/index.php">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path d="M9 19a1 1 0 0 0 .38-.08a1.15 1.15 0 0 0 .33-.21a1 1 0 0 0 .12-.16a.56.56 0 0 0 .09-.17a.64.64 0 0 0 .08-.18a1.36 1.36 0 0 0 0-.2a1 1 0 0 0-.08-.38a.9.9 0 0 0-.54-.54A1 1 0 0 0 8.8 17l-.18.06a.56.56 0 0 0-.17.09a1 1 0 0 0-.16.12a1 1 0 0 0-.21.33A1 1 0 0 0 8 18a1 1 0 0 0 1 1zm-3.71-.29a1.15 1.15 0 0 0 .33.21A1 1 0 0 0 6 19h.19a.6.6 0 0 0 .19-.06a.76.76 0 0 0 .18-.09l.15-.12a1.15 1.15 0 0 0 .21-.33A.84.84 0 0 0 7 18a1.36 1.36 0 0 0 0-.2a.64.64 0 0 0-.06-.18a.56.56 0 0 0-.09-.17a1 1 0 0 0-.12-.16a1 1 0 0 0-1.09-.21a1 1 0 0 0-.33.21a1 1 0 0 0-.12.16a.56.56 0 0 0-.09.17a.64.64 0 0 0-.1.18a1.36 1.36 0 0 0 0 .2a1 1 0 0 0 .08.38a1.15 1.15 0 0 0 .21.33zM19 2H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3zm1 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3h16zm0-5H4v-4h4a1 1 0 0 0 .71-.29L10 8.46l2.8 3.2a1 1 0 0 0 .72.34a1 1 0 0 0 .71-.29L15.91 10H20zm0-6h-4.5a1 1 0 0 0-.71.29l-1.24 1.25l-2.8-3.2a1 1 0 0 0-1.46 0L7.59 8H4V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1z" fill="#626262"/><rect x="0" y="0" width="24" height="24" fill="rgba(0, 0, 0, 0)" /></svg>
                                Monitoring
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../gpu/gpubinder.php">
                                <span data-feather="link-2"></span>
                                GPU
                            </a>
                        </li>
                        
                    </ul>
                </li>
            </ul>
          </div>
        </nav>