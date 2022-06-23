
<?php

//Anonymous readonly connection

print_r ( libvirt_version() );
echo "<br>"."<br>";

$conn=libvirt_connect("qemu:///system");
$res1=libvirt_list_domains($conn);
print_r($res1); 
echo "<br>"."<br>";

$getconinfo  = libvirt_connect_get_information($conn);
print_r($getconinfo); 
echo "Get connection information"."<br>"."<br>";

$getsysinfo = libvirt_connect_get_sysinfo($conn);
print_r($getsysinfo); 
echo "Get sytem information"."<br>"."<br>";

$nodeinfo = libvirt_node_get_info($conn);
print_r($nodeinfo); 
echo "Get node information"."<br>"."<br>";

$uri = libvirt_node_get_uri($conn);
print_r($uri); 
echo "Get URI information"."<br>"."<br>";


$alldomainres = libvirt_list_domain_resources($conn);
print_r($alldomainres); 
echo "Domain resource all"."<br>"."<br>";


$domain = "Noob";
$res2 = libvirt_domain_lookup_by_name($conn, $domain);
print_r($res2); 
echo "Domain resource ID buy its name Noob"."<br>"."<br>";

$domain = "Noob";
$res9 = libvirt_domain_lookup_by_name($conn, $domain);
print_r($res9); 
echo "Domain resource ID buy its name Noob"."<br>"."<br>";



$res3 = libvirt_connect_get_hostname($conn);
print_r($res9); 
echo "<br>"."<br>";
 
//$res5 = "L3Zhci9saWIvbGlidmlydC9pbWFnZXMva2ltam9udW4ucWNvdzI=";
//$vol =  libvirt_storagevolume_delete($res5)

 //      return $vol;
$name ="default";
$memo = libvirt_storagepool_lookup_by_name($conn, $name);
print_r($memo); 
echo "Function which return storage pool resource id"."<br>"."<br>";

$mem= libvirt_storagepool_get_info($memo);
print_r($mem); 
echo "<br>"."<br>";

$listpools = libvirt_list_storagepools($conn);
print_r($listpools); 
echo "Function which return all storage pools"."<br>"."<br>";

$volname = "kimjonun.qcow2";
$outvolres = libvirt_storagevolume_lookup_by_name($memo, $volname);
print_r($outvolres); 
echo "Function which return storage volume resource id by its name here kimjonun.qcow2"."<br>"."<br>";

$outvolname = libvirt_storagevolume_get_name($outvolres);
print_r($outvolname); 
echo "<br>"."<br>";


$getvolinfo = libvirt_storagevolume_get_info($outvolres);
print_r($getvolinfo); 
echo "Function for volume information array of type, allocation and capacity"."<br>"."<br>";

$path = "/var/lib/libvirt/images/kimjonun.qcow2";
$getstoragevolres = libvirt_storagevolume_lookup_by_path($conn, $path);
print_r($getstoragevolres); 
echo "Function is used to lookup for storage volume by it's path"."<br>"."<br>";

$capacity = 21737418240;
$resizevol = libvirt_storagevolume_resize($getstoragevolres, $capacity);
print_r($resizevol); 
echo "Function to resize volume"."<br>"."<br>";


$dominfo = libvirt_domain_get_info($res2);
print_r($dominfo); 
echo "Domain info"."<br>"."<br>";

$dominfo = libvirt_domain_get_job_info($res2);
print_r($dominfo); 
echo "Domain JOB info"."<br>"."<br>";




$mac = "52:54:00:6a:6c:66";
$win2019netinfo = libvirt_domain_get_network_info($res9, $mac);
print_r($win2019netinfo); 
echo "Domain network infor for Win2019"."<br>"."<br>";

$datoz = libvirt_version();
echo "Libvirt Version: " . $datoz['libvirt.major'] . "." . $datoz['libvirt.minor'] . "." . $datoz['libvirt.release'];

$cmd = "sudo virt-clone --original lol--auto-clone";

while (@ ob_end_flush()); // end all output buffers if any

$proc = popen($cmd, 'r');
echo '<pre>';
while (!feof($proc))
{
    echo fread($proc, 4096);
    @ flush();
}
echo '</pre>';

?>
