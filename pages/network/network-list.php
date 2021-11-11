<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}

// If there is no username, then we need to send them to the login
if (!isset($_SESSION['username'])){
  $_SESSION['return_location'] = $_SERVER['PHP_SELF']; //sets the return location used on login page
  header('Location: ../login.php');
}

function clean_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = str_replace(' ','',$data);
  $data = filter_var($data, FILTER_SANITIZE_STRING);
  return $data;
}


// We are now going to grab any GET/POST data and put in in SESSION data, then clear it.
// This will prevent duplicatig actions when page is reloaded.
if (isset($_GET['action'])) {
  $_SESSION['action'] = $_GET['action'];
  $_SESSION['name'] = $_GET['name'];
  $_SESSION['network'] = $_GET['network'];
  $_SESSION['xmldesc'] = $_GET['xmldesc'];
  $_SESSION['network_name'] = clean_input($_GET['network_name']); //Used for create-network and create-macvtap
  $_SESSION['mac_address'] = $_GET['mac_address']; //Used for create-network
  $_SESSION['ip_address'] = clean_input($_GET['ip_address']); //Used for create-network
  $_SESSION['subnet_mask'] = $_GET['subnet_mask']; //Used for create-network
  $_SESSION['dhcp_service'] = $_GET['dhcp_service']; //Used for create-network
  $_SESSION['dhcp_start_address'] = clean_input($_GET['dhcp_start_address']); //Used for create-network
  $_SESSION['dhcp_end_address'] = clean_input($_GET['dhcp_end_address']); //Used for create-network
  $_SESSION['interface_dev'] = $_GET['interface_dev']; //Used for create-macvtap
  $_SESSION['new_xml'] = $_GET['new_xml']; //Used for create-xml, defining network from XML
  $_SESSION['net_name'] = $_GET['net_name']; //Used for editing the XML of an interface

  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

require('../header.php');

if ($_SESSION['action']!= "") {

  $action = $_SESSION['action']; //grab the $action variable from $_SESSION
  $name = $_SESSION['name']; //will be the name of the network to perform actions on
  $network = $_SESSION['network']; //used for delete confirmation
  $xmldesc = $_SESSION['xmldesc']; //used to hold changed XML data
  $network_name = $_SESSION['network_name']; //Used for creating new virtual lan
  $mac_address = $_SESSION['mac_address']; //Used for creating new virtual lan
  $ip_address = $_SESSION['ip_address']; //Used for creating new virtual lan
  $subnet_mask = $_SESSION['subnet_mask']; //Used for creating new virtual lan
  $dhcp_service = $_SESSION['dhcp_service']; //Used for creating new virtual lan
  $dhcp_start_address = $_SESSION['dhcp_start_address']; //Used for creating new virtual lan
  $dhcp_end_address = $_SESSION['dhcp_end_address']; //Used for creating new virtual lan
  $interfaces = "<interface dev='" . $_SESSION['interface_dev'] . "' />"; //Used for creating a macvtap bridge
  $new_xml = $_SESSION['new_xml']; //Used for create-xml, defining network from XML
  $net_name = $_SESSION['net_name']; //Used for editing the XML of an interface


  unset($_SESSION['action']); //Unset the Action Variable to prevent repeats of action on page reload
  unset($_SESSION['name']); //Unset the name in case of page reload
  unset($_SESSION['network']); //Unset the name in case of page reload
  unset($_SESSION['xmldesc']);
  unset($_SESSION['network_name']); //Used for creating new virtual lan
  unset($_SESSION['mac_address']); //Used for creating new virtual lan
  unset($_SESSION['ip_address']); //Used for creating new virtual lan
  unset($_SESSION['subnet_mask']); //Used for creating new virtual lan
  unset($_SESSION['dhcp_service']); //Used for creating new virtual lan
  unset($_SESSION['dhcp_start_address']); //Used for creating new virtual lan
  unset($_SESSION['dhcp_end_address']); //Used for creating new virtual lan
  unset($_SESSION['interface_dev']); //Used for creating a macvtap bridge
  unset($_SESSION['new_xml']); //Used for create-xml, defining network from XML
  unset($_SESSION['net_name']); //Used for editing the XML of an interface

  if ($action == 'network-delete') {
    $notification = $lv->network_undefine($network) ? "" : 'Error while removing network: '.$lv->get_last_error();
  }

  if ($action == 'start') {
    $notification = $lv->set_network_active($name, true) ? "" : 'Error while starting network: '.$lv->get_last_error();
  }

  if ($action == 'stop') {
    $notification = $lv->set_network_active($name, false) ? "" : 'Error while stopping network: '.$lv->get_last_error();
  }

  if ($action == 'edit-xml') {
    $editXML = $lv->network_change_xml($net_name, $xmldesc); 
    if (!$editXML){
      $notification = "Error changing network XML: " . $lv->get_last_error();
      $notification = filter_var($notification,FILTER_SANITIZE_SPECIAL_CHARS); //Error message may contain special characters
    }
}

  if ($action == 'edit') {
    /*
    $xml = $lv->network_get_xml($name, false);
    if ($xmldesc != "") {
      $notification = $lv->network_change_xml($name, $xmldesc) ? "" : 'Error changing network definition: '.$lv->get_last_error();
    } else {
      $network_xml = 'Editing <strong>'.$name.'</strong> network XML description: <br/><br/><form action="?name='.$name.'&action=edit" method="POST">'.
        '<textarea name="xmldesc" rows="17" cols="2" style="width: 100%; margin: 0; padding: 0; border-width: 0; background-color:#ebecf1;" >'.$xml.'</textarea><br/><br/>'.
        '<input type="submit" value=" Save "></form><br/><br/>';
    }
    */
    $xml_data = $lv->network_get_xml($name, false);
    $editable_xml = true;
    $net_name = $name;
  }

  
    

  if ($action == 'dumpxml') {
    $xml_data = $lv->network_get_xml($name, false);
  }

  if ($action == 'create-network') {
    $xml = "
    <network>
      <name>$network_name</name>
      <forward mode='nat'/>
      <mac address='$mac_address'/>
      <ip address='$ip_address' netmask='$subnet_mask'>
        <dhcp>
          <range start='$dhcp_start_address' end='$dhcp_end_address'/>
        </dhcp>
      </ip>
    </network>";
    if ($dhcp_service == "disabled"){
      $xml = "
      <network>
        <name>$network_name</name>
        <forward mode='$forward_mode'/>
        <mac address='$mac_address'/>
        <ip address='$ip_address' netmask='$subnet_mask'>
        </ip>
      </network>";
    }
    $network_add = $lv->network_define_xml($xml); 
    if (!$network_add){
      $notification = "Error defining network: " . $lv->get_last_error();
      $notification = filter_var($notification,FILTER_SANITIZE_SPECIAL_CHARS); //Error message will contain special characters
    }
  }

  if ($action == "create-macvtap") {
    $xml = " 
    <network>
      <name>$network_name</name>
      <forward mode='bridge'>
        $interfaces
      </forward>
    </network>
    ";
    $network_add = $lv->network_define_xml($xml);
    if (!$network_add){
      $notification = "Error defining macvtap: " . $lv->get_last_error();
      $notification = filter_var($notification,FILTER_SANITIZE_SPECIAL_CHARS); //Error message will contain special characters
    }
  }

  if ($action == "create-xml") {
    $network_add = $lv->network_define_xml($new_xml);
    if (!$network_add){
      $notification = "Error defining network from XML: " . $lv->get_last_error();
      $notification = filter_var($notification,FILTER_SANITIZE_SPECIAL_CHARS); //Error message will contain special characters
    }
  }
}

$random_mac = $lv->generate_random_mac_addr(); //Used when creating new virtual lan

require('../navbar.php');

?>




    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4 <?php if($_SESSION['themeColor'] == "dark-edition") { echo "main-dark"; } ?> ">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
        <h3 class="h3">Networking</h3>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group mr-2">
            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#create-network-modal">Create Network</button>
            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#create-macvtap-modal"> Create Macvtap Bridge</button>
            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#create-xml-modal">Network from XML</button>
          </div>
        </div>
      </div>

      <form action="" method="POST">
        <div class="content">
          <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">

              <div class="card <?php if($_SESSION['themeColor'] == "dark-edition") { echo "card-dark"; } ?>">
                <div class="card-header">
                  <span class="card-title"></span>
                </div>
                <div class="card-body">

                  <div class="table-responsive">
                    <table class="table">
                      <thead class="text-none">
                        <th>Name</th>
                        <th>State</th>
                        <th>Gateway IP Address</th>
                        <th>IP Address Range</th>
                        <th>Forwarding</th>
                        <th>DHCP Range</th>
                        <th>Actions</th>
                      </thead>
                      <tbody>
                    <!-- start project list -->
                    <?php
                    $tmp = $lv->get_networks(VIR_NETWORKS_ALL);

                    for ($i = 0; $i < sizeof($tmp); $i++) {
                      $tmp2 = $lv->get_network_information($tmp[$i]);
                      $ip = '----';
                      $ip_range = '----';
                      $activity = $tmp2['active'] ? 'Active' : 'Inactive';
                      $dhcp = '----';
                      $forward = 'None';

                      if (array_key_exists('forwarding', $tmp2) && $tmp2['forwarding'] != 'None') {
                        if (array_key_exists('forward_dev', $tmp2))
                          $forward = $tmp2['forwarding'].' to '.$tmp2['forward_dev'];
                        else
                          $forward = $tmp2['forwarding'];
                      }

                      if (array_key_exists('dhcp_start', $tmp2) && array_key_exists('dhcp_end', $tmp2))
                        $dhcp = $tmp2['dhcp_start'].' - '.$tmp2['dhcp_end'];

                      if (array_key_exists('ip', $tmp2))
                        $ip = $tmp2['ip'];

                      if (array_key_exists('ip_range', $tmp2))
                        $ip_range = $tmp2['ip_range'];

                      $act = "<a href=\"?action=" . ($tmp2['active'] ? "stop" : "start");
                      $act .= "&amp;name=" . urlencode($tmp2['name']) . "\">";
                      $act .= ($tmp2['active'] ? "Disable " : "Enable ") . "</a>";
                      $act .= " | <a href=\"?action=dumpxml&amp;name=" . urlencode($tmp2['name']) . "\"> XML </a>";

                      if (!$tmp2['active']) {
                        $networkName = $tmp2['name'];
                        $deleteURL = "?action=network-delete&amp;network=$networkName";
                        $currentURL = $_SERVER['PHP_SELF'];
                        $act .= ' | <a href="?action=edit&amp;name='. urlencode($tmp2['name']) . '"> Edit </a>';
                        //$act .= " | <a onclick=\"networkDeleteWarning('?action=network-delete&amp;network=".$tmp2['name']."')\" href=\"#\">Delete</a>";
                        //$act .= " | <a onclick=\"networkDeleteWarning('$deleteURL','$currentURL')\" href=\"#\">Delete</a>";
                        //$act .= " | <a href=\"?action=network-delete&amp;network=$networkName\">Delete</a>";
                        //$act .= " | <a onclick=\"networkDeleteWarning('$deleteURL', '$networkName')\" href=\"#\"> Delete </a>";
                        $act .= " | <a data-href=\"?action=network-delete&amp;network=$networkName\" data-filename=\"$networkName\" data-toggle=\"modal\" data-target=\"#confirm-delete-modal\" href=\"#confirm-delete-modal\"> Delete</a>";
                      }

                      echo "<tr>" .
                        "<td>{$tmp2['name']}</td>" .
                        "<td>$activity</td>" .
                        "<td>$ip</td>" .
                        "<td>$ip_range</td>" .
                        "<td>$forward</td>" .
                        "<td>$dhcp</td>" .
                        "<td>$act</td>" .
                        "</tr>";
                    }
                    echo "</tbody></table>";
                    ?>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </form>
    </main>
  </div> 
</div> <!-- end content -->



<!-- Hidden modal for creating a virtual lan -->
<div id="create-network-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Create Network </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="createNetworkForm" name="createNetwork" role="form" action="">
				<div class="modal-body">	
          <div class="row">
            <label class="col-3 col-form-label text-right">Network Name: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="vnet0" required="required" placeholder="Enter name for new network connection " class="form-control" name="network_name" />
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">MAC Address: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" class="form-control" name="mac_address" value="<?php echo $random_mac; ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Gateway IP Address: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" class="form-control" name="ip_address" value="192.168.1.1" />
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">Subnet Mask: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="subnet_mask">
                  <option value="255.255.255.0" selected>/24 --> 255.255.255.0</option>
                  <option value="255.255.255.128">/25  -->  255.255.255.128</option>
                  <option value="255.255.255.128">/26  -->  255.255.255.192</option>
                  <option value="255.255.255.128">/27  -->  255.255.255.224</option>
                  <option value="255.255.255.128">/28  -->  255.255.255.240</option>
                  <option value="255.255.255.128">/29  -->  255.255.255.248</option>
                  <option value="255.255.255.128">/30  -->  255.255.255.252</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">DHCP Service: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="dhcp_service" onchange="dhcpChangeOptions(this)">
                  <option value="enabled" selected>enabled</option>
                  <option value="disabled">disabled</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">DHCP Starting Address: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" class="form-control" name="dhcp_start_address" value="192.168.1.2" placeholder="Enter starting IP address or none"/>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-3 col-form-label text-right">DHCP Ending Address: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" class="form-control" name="dhcp_end_address" value="192.168.1.254" placeholder="Enter ending IP address or none"/>
              </div>
            </div>
          </div>
          <input type="hidden" name="action" value="create-network">
        </div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for create a macvtap adapter -->
<div id="create-macvtap-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Create Macvtap Bridge </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="createMacvtapForm" name="createMacvtap" role="form" action="">
				<div class="modal-body">
          <div class="row">
            <label class="col-3 col-form-label text-right">Network Name: </label>
            <div class="col-6">
              <div class="form-group">
                <input type="text" value="macvtap" required="required" placeholder="Enter name for new network connection " class="form-control" name="network_name" />
              </div>
            </div>
          </div>	
          <div class="row">
            <label class="col-3 col-form-label text-right">Network Adapters: </label>
            <div class="col-6">
              <div class="form-group">
                <select class="form-control" name="interface_dev">
                  <?php
                    $device_cap = $lv->get_node_device_cap_options(); //Get Host Device Options
                    //Loop through each Host device looking for network devices
                    for ($i = 0; $i < sizeof($device_cap); $i++) {
                      //Just pull out NET data of host device capabilites
                      if ($device_cap[$i] == "net"){
                        $tmp = $lv->get_node_devices($device_cap[$i]);
                        //Loop through each network device
                        for ($ii = 0; $ii < sizeof($tmp); $ii++) {
                          $tmp1 = $lv->get_node_device_information($tmp[$ii]);
                          $interface = array_key_exists('interface_name', $tmp1) ? $tmp1['interface_name'] : '-';
                          $mac_address = array_key_exists('address', $tmp1) ? $tmp1['address'] : '-';
                          //echo "<option value=\"$interface\">" . $interface . "-" . $mac_address . "</option>";
                          echo "<option value=\"$interface\">$interface ($mac_address) </option>";
                        }
                      }
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <input type="hidden" name="action" value="create-macvtap">
        </div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for create from XML -->
<div id="create-xml-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Create Network from Libvirt XML </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="createXMLForm" name="createXML" role="form" action="">
				<div class="modal-body">
          <div class="form-group">
            <textarea name="new_xml" class="form-control" rows="13" placeholder=""></textarea>
          </div>        
          <input type="hidden" name="action" value="create-xml">
        </div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					<input type="submit" class="custom-btnshrt" id="submitmodalbt" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>


<!-- Hidden modal for deleting a network  -->
<div id="confirm-delete-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
      <div class="modal-body">				
        <p id="message"></p>
      </div>
      <div class="modal-footer">					
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a class="btn btn-warning btn-ok">Delete</a>
      </div>
		</div>
	</div>
</div>


<!-- Hidden modal for displaying and editing xml info -->
<div id="xml-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content <?php if($_SESSION['themeColor'] == "dark-edition") { echo "modal-dark"; } ?>">
			<div class="modal-header">
        <h5 class="modal-title">XML Information </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
			</div>
			<form id="xmlForm" name="xml" role="form" action="">
				<div class="modal-body">	
          <p id="message"></p>			
          <div class="form-group">
						<label for="xml_data"></label>
						<textarea name="xmldesc" id="xml_data" class="form-control" rows="13"><?php echo "$xml_data";?></textarea>
          </div>
				</div>
				<div class="modal-footer">					
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <?php
          if ($editable_xml){
            echo "<input type=\"submit\" class=\"custom-btnshrt\" id=\"submitmodalbt\" value=\"Submit\">";
            echo "<input type=\"hidden\" name=\"action\" value=\"edit-xml\">";
            echo "<input type=\"hidden\" name=\"net_name\" value=\"$net_name\">";
          }
          ?>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
  //Still needs to be setup to remove DHCP range if disabled
  function dhcpChangeOptions(selectEl) {
    let selectedValue = selectEl.options[selectEl.selectedIndex].value;
      if (selectedValue.charAt(0) === "/") {
        selectedValue = "enabled";
      }
    let subForms = document.getElementsByClassName('dhcpChange')
    for (let i = 0; i < subForms.length; i += 1) {
      if (selectedValue === subForms[i].id) {
        subForms[i].setAttribute('style', 'display:block')
      } else {
        subForms[i].setAttribute('style', 'display:none')
      }
    }
  }
</script>

<?php
require('../footer.php');
?>

<script>

  //Set variables and href for delete modal
  $('#confirm-delete-modal').on('show.bs.modal', function(e) {
    var filename = $(e.relatedTarget).data('filename');
    $(e.currentTarget).find('p[id="message"]').html("Are you sure you wish to delete <strong>" + filename + "</strong>?");
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
  });

</script>


<!-- Load show XML modal if XML requested -->
<?php if($xml_data) { ?>
  <script>
  $(window).on('load',function(){
      $('#xml-modal').modal('show');
      //$('#xml-modal textarea').html("test");
  });
  </script>
<?php } ?>