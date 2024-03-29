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

$arrayExisting = file('../config/version.php');
$existingExploded = explode('.', $arrayExisting[1]); //Seperate Major.Minor.Patch
$existingVersion = $existingExploded[0] . $existingExploded[1] . $existingExploded[2];

include_once('../config/config.php');
?>

<body class=" <?php if ($_SESSION['themeColor'] == "dark-edition") {
                    echo "main-dark";
                } ?> ">
    <nav class="navbar navbar-dark bg-dark sticky-top flex-md-nowrap p-0 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                                echo "main-dark";
                                                                            } ?> ">
        <a class="navbar-brand navbar-dark col-sm-3 col-md-2 mr-0" href="../../index.php"><img src="../../assets/img/arclight-dark.svg" width="100px"> &ensp;</a>
        <!-- <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search"> -->
        <ul class="navbar-nav px-3">
            <!-- <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre >
                    Manage<span class="caret"></span>
                </a>
                <div class="dropdown-menu position-absolute dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="">Account</a>
                    <a class="dropdown-item" href="">Billing</a>
                    <a class="dropdown-item" href="/api/v1/admin/users">Access (IAM)</a>

                </div>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" id="update-status" href="../config/update.php">Update</a>
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
                <div class="dropdown-menu position-absolute dropdown-menu-right" aria-labelledby="navbarDropdown"  style="right: 0px;">
                    <a class="dropdown-item" href="">Role [<?php echo $_SESSION['role']; ?>]</a>
                    <!-- <a class="dropdown-item" href="">Profile</a> -->
                    <!-- <a class="dropdown-item" href="../config/preferences.php">Preferences</a> -->
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
                                    <a class="nav-link" href="../domain/instance-list-user.php">
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
                                    <a class="nav-link" target="blank" href="../monitoring/index.php">
                                        <span data-feather="activity"></span>
                                        Monitoring
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../modules/update_modules.php">
                                        <span data-feather="box"></span>
                                        Modules
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../terminal/xterm.php">
                                        <span data-feather="terminal"></span>
                                        Terminal
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../ami/amis.php">
                                        <span data-feather="image"></span>
                                        AMIs
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../logging/logs.php">
                                        <span data-feather="file-text"></span>
                                        Logs
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <script>
                //check from https://docs.chatnaut.com/update.json object for updates and compare to current version from config/version.php
                async function get() {
                    let url = 'https://docs.chatnaut.com/update.json'
                    let obj = await (await fetch(url)).json();

                    // console.log(obj);
                    return obj;
                }
                var tags;
                (async () => {
                    //set interval to check for updates every 24 hours
                    tags = await get()
                    setTimeout(async () => {
                        //console.log(tags)
                        const existingVersion = `<?php echo $existingVersion ?>`;
                        const arraySplitVersion = tags.version.split(".");
                        const newVersion = arraySplitVersion[0] + arraySplitVersion[1] + arraySplitVersion[2];
                        sessionStorage.setItem("state", tags.state)
                        if (newVersion > existingVersion) {
                            const status = document.getElementById("update-status");
                            //gradient border in status green if update available
                            status.style.border = "1px solid rgb(0, 255, 0)";
                            status.style.padding = "5px";

                            status.innerText = "Update " + tags.version + " available";
                            sessionStorage.setItem("update-available", true);
                            sessionStorage.setItem("update-version", tags.version);
                            sessionStorage.setItem("update-flag", tags.extension);
                        } else {
                            sessionStorage.setItem("update-available", false);
                        }
                    }, 3000);
                })();
            </script>
            <!-- axios library for AJAX calls -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>