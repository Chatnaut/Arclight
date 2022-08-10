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

$userid = $_SESSION['userid'];
?>

<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                        echo "main-dark";
                                                                    } ?> ">
    <form action="" method="POST">
        <div class="content">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                            echo "card-dark";
                                        } ?>">
                        <div class="card-header">
                            <span class="card-title">NoVNC Logs</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style='max-height:400px; overflow-y:scroll;'>
                                <table class="table novnc">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>
<!-- </div> 
</div> end content of virtual GPUs -->
<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                                                        echo "main-dark";
                                                                    } ?> ">
    <form action="" method="POST">
        <div class="content">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <div class="card <?php if ($_SESSION['themeColor'] == "dark-edition") {
                                            echo "card-dark";
                                        } ?>">
                        <div class="card-header">
                            <span class="card-title">Terminal Logs</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style='max-height:400px; overflow-y:scroll;'>
                                <table class="table terminal">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>


<script>
    const novnc = document.querySelector('.novnc');
    const terminal = document.querySelector('.terminal');
    const token = localStorage.getItem('token');

    const getLogs = async () => {
        try {
            const response = await axios.get(`/api/v1/logs/getlogs`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            novnc.insertAdjacentHTML('beforeend', `<tbody>
                <tr>
                    <td><pre>${response.data.result[0]}</pre></td>
                </tr>
                </tbody>`);
            terminal.insertAdjacentHTML('beforeend', `<tbody>
                <tr>
                    <td><pre>${response.data.result[1]}</pre></td>
                </tr>
                </tbody>`);

        } catch (error) {
            console.log(error);
            novnc.insertAdjacentHTML('beforeend', `<tbody>
                <tr>
                    <td><pre>${error}</pre></td>
                </tr>
                </tbody>`);
            terminal.insertAdjacentHTML('beforeend', `<tbody>
                <tr>
                    <td><pre>${error}</pre></td>
                </tr>
                </tbody>`);
        }
    };
    getLogs();
</script>