<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
    session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])){
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../sign-in.php');
}

// We are now going to grab any GET/POST data and put in in SESSION data, then clear it.
// This will prevent duplicatig actions when page is reloaded.
if (isset($_GET['action'])) {
    $_SESSION['action'] = $_GET['action'];
    $_SESSION['name'] = $_GET['name'];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['clear'])) {
    unset($_POST);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

require('../header.php');
require('../navbar.php');

$action = $_SESSION['action']; //grab the $action variable from $_SESSION
$name = $_SESSION['name'];
unset($_SESSION['action']); //Unset the Action Variable to prevent repeats of action on page reload
unset($_SESSION['name']);

$info = $lv->host_get_node_info(); //needed to get number of cores and cpu speed for calculations
$cpu_stats = $lv->host_get_node_cpu_stats();
$mem_stats = $lv->host_get_node_mem_stats();

//Determine the percentage of memory used
$mem_percentage = (($mem_stats['total'] - $mem_stats['free']) / $mem_stats['total']) * 100;
$mem_percentage = number_format($mem_percentage, 2, '.',','); //format the percentage to 2 decimal digits

//Determine the percentage of cpu used
$processor_speed = $info['mhz'] * 1000000; //Used to determine how many processor cycles can happen in a second (hertz)
$multiplier = $info['nodes'] * $info['cores']; //Multiplying by the number of phycial cores, not hyperthreaded cores
$usage0 = $cpu_stats['0']['kernel'] + $cpu_stats['0']['user']; //First reading of CPU data
$usage1 = $cpu_stats['1']['kernel'] + $cpu_stats['1']['user']; //Second reading of CPU data one second later
$cpu_percentage = ($usage1 - $usage0) / ($processor_speed * $multiplier) * 100;
$cpu_percentage = number_format($cpu_percentage, 2, '.', ',' ); // PHP: string number_format ( float $number [, int $decimals [, string $dec_point, string $thousands_sep]] )

$host_node_info = $lv->host_get_node_info(); // Get and array of information on the host
$arch = $host_node_info['model'];
$device_cap = $lv->get_node_device_cap_options(); //Get Host Device Options

// Used when the user clicks the XML link. Will display XML data
if ($action == 'dumpxml') {
  $xml_data = htmlentities($lv->get_node_device_xml($name, false));
}

//get Qemu/KVM info
$ci  = $lv->get_connect_information();
$info = '';
if ($ci['uri'])
    $info .= ' <i>'.$ci['uri'].'</i>, ';

$getlibvirtversion = $lv->get_lib_version();

?>



    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
        <!-- <h1 class="h2">Host Information</h1> -->
      </div>
<?php

?>
      <form action="" method="POST">

        <div class="row">

          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['system'];?></span>
              </div>
              <div class="card-body">
                <?php
                  for ($i = 0; $i < sizeof($device_cap); $i++) {
                    //Just pull out SYSTEM data
                    if ($device_cap[$i] == "system") {
                      $tmp = $lv->get_node_devices($device_cap[$i]);

                      for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                        $tmp1 = $lv->get_node_device_information($tmp[$ii]);
                        $act = "<a title='XML Data' href=\"?action=dumpxml&amp;name={$tmp1['name']}\">" . $lang['xml'] . "</a>";

                        $vendor = array_key_exists('hardware_vendor', $tmp1) ? $tmp1['hardware_vendor'] : $lang['unknown'];
                        $product_name = array_key_exists('product_name', $tmp1) ? $tmp1['product_name'] : $lang['unknown'];
                        $serial = array_key_exists('hardware_serial', $tmp1) ? $tmp1['hardware_serial'] : $lang['unknown'];
                        $firmware_vender = array_key_exists('firmware_vendor', $tmp1) ? $tmp1['firmware_vendor'] : $lang['unknown'];
                        $firmware_version = array_key_exists('firmware_version', $tmp1) ? $tmp1['firmware_version'] : $lang['unknown'];
                        $firmware_release_date = array_key_exists('firmware_release_date', $tmp1) ? $tmp1['firmware_release_date'] : $lang['unknown'];
                        function Uptime() {
                          $ut = strtok( exec( "cat /proc/uptime" ), "." );
                          $days = sprintf( "%2d", ($ut/(3600*24)) );
                          $hours = sprintf( "%2d", ( ($ut % (3600*24)) / 3600) );
                          $min = sprintf( "%2d", ($ut % (3600*24) % 3600)/60  );
                          $sec = sprintf( "%2d", ($ut % (3600*24) % 3600)%60  );
                          return array( $days, $hours, $min, $sec );
                          
                        }
                        $os_info = parse_ini_file("/etc/lsb-release");
                      $ut = Uptime();
                        echo "<strong> Uptime:</strong> $ut[0] Days $ut[1] Hours $ut[2] Minutes $ut[3] Seconds <br>";
                        echo "<strong>" . $lang['host'] . ":</strong> $hn <br />";
                        echo "<strong>" . $lang['os'] . ":</strong> {$os_info['DISTRIB_DESCRIPTION']} <br />";
                        echo "<strong>" . $lang['hardware_vendor'] . ":</strong> $vendor <br />";
                        echo "<strong>" . $lang['product'] . ":</strong> $product_name <br />";
                        echo "<strong>" . $lang['serial'] . ":</strong> $serial <br />";
                        echo "<strong>" . $lang['firmware_vendor'] . ":</strong> {$tmp1['firmware_vendor']} <br />";
                        echo "<strong>" . $lang['firmware_version'] . ":</strong> $firmware_vender </br />";
                        echo "<strong>" . $lang['firmware_release_date'] . ":</strong> $firmware_release_date <br />";
                        echo "<strong>" . $lang['action'] . ":</strong> $act <br /> <br />";
                      }
                    }
                  }
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['cpu']; ?></span>
              </div>
              <div class="card-body">
                <strong><?php echo $lang['cpu_percentage']; ?>:</strong> <?php echo $cpu_percentage . "%"; ?> <br />
                <div class="progress">
                  <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $cpu_percentage . '%'; ?>" aria-valuenow="<?php echo $cpu_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div> <br />
                <?php
                echo "<strong>" . $lang['total_processor_count'] . ":</strong> {$host_node_info['cpus']} <br>";
                echo "<strong>" . $lang['processor_speed'] . ":</strong> {$host_node_info['mhz']} MHz <br>";
                echo "<strong>" . $lang['processor_nodes'] . ":</strong> {$host_node_info['nodes']} <br>";
                echo "<strong>" . $lang['processor_sockets'] . ":</strong> {$host_node_info['sockets']} <br>";
                echo "<strong>" . $lang['processor_cores'] . ":</strong> {$host_node_info['cores']} <br>";
                echo "<strong>" . $lang['processor_threads'] . ":</strong> {$host_node_info['threads']} <br>";
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>


          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['memory']; ?></span>
              </div>
              <div class="card-body">
                <strong><?php echo $lang['memory_percentage']; ?>:</strong> <?php echo $mem_percentage . "%"; ?> <br />
                <div class="progress">
                  <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $mem_percentage . '%'; ?>" aria-valuenow="<?php echo $mem_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div> <br />
                <?php
                echo "<strong>" . $lang['total_memory'] . ":</strong> " . number_format(($host_node_info['memory'] / 1048576), 2, '.', ' ') . " GB <br>";
                echo "<strong>" . $lang['used_memory'] . ":</strong> " . number_format((($host_node_info['memory'] - $mem_stats['free'] - $mem_stats['buffers'] - $mem_stats['cached']) / 1048576), 2, '.', ' ') . " GB <br>";
                echo "<strong>" . $lang['free_memory'] . ":</strong> " . number_format(($mem_stats['free'] / 1048576), 2, '.', ' ') . " GB <br>";
                echo "<strong>" . $lang['buffered_memory'] . ":</strong> " . number_format(($mem_stats['buffers'] / 1048576), 2, '.', ' ') . " GB <br>";
                echo "<strong>" . $lang['cached_memory'] . ":</strong> " . number_format(($mem_stats['cached'] / 1048576), 2, '.', ' ') . " GB <br>";
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>


          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['hypervisor']; ?></span>
              </div>
              <div class="card-body">
                <?php
                if (strlen($info) > 2)
                    $info[ strlen($info) - 2 ] = ' ';
                echo "<strong>" . $lang['hypervisor'] . ":</strong> {$ci['hypervisor_string']} <br>";
                echo "<strong>" . $lang['libvirt'] . ":</strong> {$getlibvirtversion['libvirt.major']}.{$getlibvirtversion['libvirt.minor']}.{$getlibvirtversion['libvirt.release']} <br>";
                echo "<strong>" . $lang['host_location'] . ":</strong> localhost <br>";
                echo "<strong>" . $lang['connection'] . ":</strong> $info <br>";
                echo "<strong>" . $lang['architecture'] . ":</strong> $arch <br>";
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['storage']; ?></span>
              </div>
              <div class="card-body">
                <?php
                for ($i = 0; $i < sizeof($device_cap); $i++) {
                  //Just pull out STORAGE data
                  if ($device_cap[$i] == "storage"){
                    $tmp = $lv->get_node_devices($device_cap[$i]);
                    echo "<div class='table-responsive'>" .
                      "<table class='table'>" .
                      "<tr>" .
                      "<th> " . $lang['device_name'] . "</th>" .
                      "<th> " . $lang['location'] . "</th>" .
                      "<th> " . $lang['bus'] . "</th>" .
                      "<th> " . $lang['vendor'] . "</th>" .
                      "<th> " . $lang['model'] . "</th>" .
                      "<th> " . $lang['action'] . "</th>" .
                      "</tr>";

                    for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                      $tmp1 = $lv->get_node_device_information($tmp[$ii]);
                      $act = "<a title='XML Data' href=\"?action=dumpxml&amp;name={$tmp1['name']}\">" . $lang['xml'] . "</a>";
                      $node_device = $tmp1['name'];
                      $vendor  = array_key_exists('vendor_name', $tmp1) ? $tmp1['vendor_name'] : $lang['unknown'];

                      //Pulling XML data that is not available from libvirt API
                      $deviceXML = new SimpleXMLElement($lv->get_node_device_xml($node_device, false));
                      $location = $deviceXML->capability->block;
                      $bus = $deviceXML->capability->bus;
                      $model = $deviceXML->capability->model;

                      echo "<tr>" .
                        "<td>{$tmp1['name']}</td>" .
                        "<td>$location</td>" .
                        "<td>$bus</td>" .
                        "<td>$vendor</td>" .
                        "<td>$model</td>" .
                        "<td>$act</td>" .
                        "</tr>";
                    }
                    echo "</table></div>";
                  }
                }
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['network']; ?></span>
              </div>
              <div class="card-body">
                <?php
                for ($i = 0; $i < sizeof($device_cap); $i++) {

                  //Just pull out NET data
                  if ($device_cap[$i] == "net"){
                    $tmp = $lv->get_node_devices($device_cap[$i]);

                    echo "<div class='table-responsive'>" .
                      "<table class='table'>" .
                      "<tr>" .
                      "<th> " . $lang['device_name'] . "</th>" .
                      "<th> " . $lang['interface'] . "</th>" .
                      "<th> " . $lang['driver_name'] . "</th>" .
                      "<th> " . $lang['mac_address'] . "</th>" .
                      "<th> " . $lang['network_speed'] . "</th>" .
                      "<th> " . $lang['network_state'] . "</th>" .
                      "<th> " . $lang['action'] . "</th>" .
                      "</tr>";

                    for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                      $tmp1 = $lv->get_node_device_information($tmp[$ii]);
                      $act = "<a title='XML Data' href=\"?action=dumpxml&amp;name={$tmp1['name']}\">" . $lang['xml'] . "</a>";
                      $node_device = $tmp1['name'];
                      $interface = array_key_exists('interface_name', $tmp1) ? $tmp1['interface_name'] : '-';
                      $driver = array_key_exists('capabilities', $tmp1) ? $tmp1['capabilities'] : '-';
                      $mac_address = array_key_exists('address', $tmp1) ? $tmp1['address'] : '-';

                      //Pulling XML data that is not available from libvirt API
                      $deviceXML = new SimpleXMLElement($lv->get_node_device_xml($node_device, false));
                      $net_speed = $deviceXML->capability->link[speed];
                      $net_state = $deviceXML->capability->link[state];
                      if (!$net_speed) {
                        $net_speed = "- - - - ";
                      }

                      echo "<tr>" .
                        "<td>{$tmp1['name']}</td>" .
                        "<td>$interface</td>" .
                        "<td>$driver</td>" .
                        "<td>$mac_address</td>" .
                        "<td>$net_speed</td>" .
                        "<td>$net_state</td>" .
                        "<td>$act</td>" .
                        "</tr>";
                    }
                    echo "</table></div>";
                  }
                }
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['usb']; ?></span>
              </div>
              <div class="card-body">
                <?php
                for ($i = 0; $i < sizeof($device_cap); $i++) {

                  //Just pull out USB, USB_DEVICE data
                  if ($device_cap[$i] == "usb_device"){
                    $tmp = $lv->get_node_devices($device_cap[$i]);
                    echo "<div class='table-responsive'>" .
                      "<table class='table'>" .
                      "<tr>" .
                      "<th> " . $lang['device_name'] . "</th>" .
                      "<th> " . $lang['identification'] . "</th>" .
                      "<th> " . $lang['driver_name'] . "</th>" .
                      "<th> " . $lang['vendor'] . "</th>" .
                      "<th> " . $lang['product'] . "</th>" .
                      "<th> " . $lang['action'] . "</th>" .
                      "</tr>";

                    for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                      $tmp1 = $lv->get_node_device_information($tmp[$ii]);
                      $act = "<a title='XML Data' href=\"?action=dumpxml&amp;name={$tmp1['name']}\">" . $lang['xml'] . "</a>";
                      $driver  = array_key_exists('driver_name', $tmp1) ? $tmp1['driver_name'] : $lang['unknown'];
                      $vendor  = array_key_exists('vendor_name', $tmp1) ? $tmp1['vendor_name'] : $lang['unknown'];
                      $product = array_key_exists('product_name', $tmp1) ? $tmp1['product_name'] : $lang['unknown'];

                      if (array_key_exists('vendor_id', $tmp1) && array_key_exists('product_id', $tmp1))
                        $ident = $tmp1['vendor_id'].':'.$tmp1['product_id'];
                      else
                        $ident = '-';

                      echo "<tr>" .
                        "<td>{$tmp1['name']}</td>" .
                        "<td>$ident</td>" .
                        "<td>$driver</td>" .
                        "<td>$vendor</td>" .
                        "<td>$product</td>" .
                        "<td>$act</td>" .
                        "</tr>";
                    }
                    echo "</table></div>";
                  }
                }
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
            <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
              <div class="card-header">
                <span class="card-title"><?php echo $lang['pci']; ?></span>
              </div>
              <div class="card-body">
                <?php
                for ($i = 0; $i < sizeof($device_cap); $i++) {

                  //Just pull out PCI data
                  if ($device_cap[$i] == "pci"){
                    $tmp = $lv->get_node_devices($device_cap[$i]);
                    echo "<div class='table-responsive'>" .
                      "<table class='table'>" .
                      "<tr>" .
                      "<th> " . $lang['device_name'] . "</th>" .
                      "<th> " . $lang['identification'] . "</th>" .
                      "<th> " . $lang['driver_name'] . "</th>" .
                      "<th> " . $lang['vendor'] . "</th>" .
                      "<th> " . $lang['product'] . "</th>" .
                      "<th> " . $lang['action'] . "</th>" .
                      "</tr>";

                    for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                      $tmp1 = $lv->get_node_device_information($tmp[$ii]);
                      $act = "<a title='XML Data' href=\"?action=dumpxml&amp;name={$tmp1['name']}\">" . $lang['xml'] . "</a>";
                      $driver  = array_key_exists('driver_name', $tmp1) ? $tmp1['driver_name'] : $lang['unknown'];
                      $vendor  = array_key_exists('vendor_name', $tmp1) ? $tmp1['vendor_name'] : $lang['unknown'];
                      $product = array_key_exists('product_name', $tmp1) ? $tmp1['product_name'] : $lang['unknown'];

                      if (array_key_exists('vendor_id', $tmp1) && array_key_exists('product_id', $tmp1))
                        $ident = $tmp1['vendor_id'].':'.$tmp1['product_id'];
                      else
                        $ident = '-';

                      echo "<tr>" .
                        "<td>{$tmp1['name']}</td>" .
                        "<td>$ident</td>" .
                        "<td>$driver</td>" .
                        "<td>$vendor</td>" .
                        "<td>$product</td>" .
                        "<td>$act</td>" .
                        "</tr>";
                    }
                    echo "</table></div>";
                  }
                }
                ?>
              </div>
              <div class="card-footer">
              </div>
            </div>
          </div>
        
        </div> <!-- End Row -->

      </form>
    </main>
  </div> <!--end div from navbar page -->
</div> <!-- end content navbar page -->



<!-- Hidden modal for displaying xml info -->
<div id="xml-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">XML Information </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="xmlForm" name="xml" role="form">
				<div class="modal-body">	
          <p id="message"></p>			
          <div class="form-group">
						<label for="xml_data"></label>
						<textarea name="xml_data" id="xml_data" class="form-control" rows="13"><?php echo "$xml_data";?></textarea>
          </div>
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>



<?php
require('../footer.php');
?>

<!-- Load XML modal if $xml_data is true -->

<?php if($xml_data) { ?>
  <script>
  $(window).on('load',function(){
      $('#xml-modal').modal('show');
      //$('#xml-modal textarea').html("test");
  });
  </script>
<?php } ?>


