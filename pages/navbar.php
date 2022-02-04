<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
    session_start();
}

function getOSInformation()
{ //https://stackoverflow.com/questions/1482260/how-to-get-the-os-on-which-php-is-running
    if (false == function_exists("shell_exec") || false == is_readable("/etc/os-release")) {
        return null;
    }

    $os         = shell_exec('cat /etc/os-release');
    $listIds    = preg_match_all('/.*=/', $os, $matchListIds);
    $listIds    = $matchListIds[0];

    $listVal    = preg_match_all('/=.*/', $os, $matchListVal);
    $listVal    = $matchListVal[0];

    array_walk($listIds, function (&$v, $k) {
        $v = strtolower(str_replace('=', '', $v));
    });

    array_walk($listVal, function (&$v, $k) {
        $v = preg_replace('/=|"/', '', $v);
    });

    return array_combine($listIds, $listVal);
}

$os_info = getOSInformation();
$host_os = $os_info['name'];

// fetching users data to give permission to access different pages according to their roles
require('../config/config.php');


?>


<body class=" <?php if ($_SESSION['themeColor'] == "dark-edition") {
                    echo "main-dark";
                } ?> ">
    <nav class="navbar navbar-dark bg-dark sticky-top flex-md-nowrap p-0 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                                echo "main-dark";
                                                                            } ?> ">
        <a class="navbar-brand navbar-dark col-sm-3 col-md-2 mr-0" href="../../index.php"><img src="../../assets/img/arclight-dark.svg" width="28px"> &ensp; Dashboard</a>
        <!-- <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search"> -->
        <ul class="navbar-nav px-3">


            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    Manage<span class="caret"></span>
                </a>
                <div class="dropdown-menu position-absolute dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="">Account</a>
                    <a class="dropdown-item" href="">Billing</a>
                    <a class="dropdown-item" href="">Access (IAM)</a>

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
                    Hi, <?php echo $_SESSION['username']; ?><span class="caret"></span>
                </a>
                <div class="dropdown-menu position-absolute dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href=""><?php echo $_SESSION['roles']; ?></a>
                    <a class="dropdown-item" href="">Profile</a>
                    <a class="dropdown-item" href="../config/preferences.php">Preferences</a>
                    <a class="dropdown-item" href="../config/settings.php">Settings</a>
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
                                    <?php if ($_SESSION['roles'] == "Enterprise") { ?>
                                        <a class="nav-link" href="../storage/storage-pools.php">
                                        <?php } else { ?>
                                            <a class="nav-link" href="../storage/storage-pools-user.php"> <?php } ?>
                                            <span data-feather="database"></span>
                                            Storage
                                            </a></a>

                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../network/network-list.php">
                                        <span data-feather="link-2"></span>
                                        Networking
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" target="blank" href="../monitoring/index.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-activity" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2Z"></path>
                                        </svg>
                                        Monitoring
                                    </a>
                                </li>
                                <li class="nav-item">

                                    <?php if ($_SESSION['roles'] == "Enterprise") { ?>
                                        <a class="nav-link" href="../gpu/gpubinder.php"> <?php } else { ?>
                                            <a class="nav-link" href="../gpu/gpubinder_profiles.php"> <?php } ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gpu-card" viewBox="0 0 16 16">
                                                <path d="M4 8a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm7.5-1.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z" />
                                                <path d="M0 1.5A.5.5 0 0 1 .5 1h1a.5.5 0 0 1 .5.5V4h13.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5H2v2.5a.5.5 0 0 1-1 0V2H.5a.5.5 0 0 1-.5-.5Zm5.5 4a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM9 8a2.5 2.5 0 1 0 5 0 2.5 2.5 0 0 0-5 0Z" />
                                                <path d="M3 12.5h3.5v1a.5.5 0 0 1-.5.5H3.5a.5.5 0 0 1-.5-.5v-1Zm4 1v-1h4v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5Z" />
                                            </svg>
                                            GPU
                                            </a>
                                </li>
                                <li class="nav-item">

                                    <?php if ($_SESSION['roles'] == "Enterprise") { ?>
                                        <a class="nav-link" href="../modules/update_modules.php">
                                            <span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"></path>
                                                </svg></span>
                                            Modules
                                        </a> <?php } ?>
                                </li>

                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>