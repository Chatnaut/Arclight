
<?php
//Anonymous readonly connection

print_r ( libvirt_version() );
echo "<br>";

$conn=libvirt_connect("qemu:///system");
$res1=libvirt_list_domains($conn);
print_r($res1); 
echo "<br>";

$domain = "Win2019";
$res2 = libvirt_domain_lookup_by_name($conn, $domain);
print_r($res2); 
echo "<br>";

$res3 = libvirt_connect_get_hostname($conn);
print_r($res3); 
echo "<br>";
 
//$res5 = "L3Zhci9saWIvbGlidmlydC9pbWFnZXMva2ltam9udW4ucWNvdzI=";
//$vol =  libvirt_storagevolume_delete($res5)

 //      return $vol;
$name ="default";
$memo = libvirt_storagepool_lookup_by_name($conn, $name);
print_r($memo); 
echo "Function which return storage pool resource id"."<br>";

$mem= libvirt_storagepool_get_info($memo);
print_r($mem); 
echo "<br>";

$listpools = libvirt_list_storagepools($conn);
print_r($listpools); 
echo "Function which return all storage pools"."<br>";

$volname = "kimjonun.qcow2";
$outvolres = libvirt_storagevolume_lookup_by_name($memo, $volname);
print_r($outvolres); 
echo "Function which return storage volume resource id by its name here kimjonun.qcow2"."<br>";

$outvolname = libvirt_storagevolume_get_name($outvolres);
print_r($outvolname); 
echo "<br>";


$getvolinfo = libvirt_storagevolume_get_info($outvolres);
print_r($getvolinfo); 
echo "Function for volume information array of type, allocation and capacity"."<br>";


$capacity = 21737418240;
$resizevol = libvirt_storagevolume_resize($outvolres, $capacity);
print_r($resizevol); 
echo "Function to resize volume"."<br>";

        $hostname=libvirt_get_hostname($conn);
    echo ("hostname:$hostname\n");
    echo ("Domain count: Active ".libvirt_get_active_domain_count($conn).",Inactive ".libvirt_get_inactive_domain_count($conn).", Total ".libvirt_get_domain_count($conn)."\n");
    
    $domains=libvirt_list_domains($conn);
    foreach ($domains as $dom)
    {
        echo ("Name:\t".libvirt_domain_get_name($dom)."\n");
        echo("UUID:\t".libvirt_domain_get_uuid_string($dom)."\n");
        $dominfo=libvirt_domain_get_info($dom);
        print_r($dominfo);
    }



?>
