<?php
class Libvirt {
    private $conn;
    private $last_error;
    private $allow_cached = true;
    private $dominfos = array();

    function Libvirt($debug = false) {
        if ($debug)
            $this->set_logfile($debug);
    }

    function _set_last_error() {
        $this->last_error = libvirt_get_last_error();
        return false;
    }

    function set_logfile($filename) {
        if (!libvirt_logfile_set($filename))
            return $this->_set_last_error();
        return true;
    } 

    function print_resources() {
        return libvirt_print_binding_resources();
    }

    function connect($uri = 'null') {
        $this->conn=libvirt_connect($uri, false);
        if ($this->conn==false)
            return $this->_set_last_error();
        return true;
    }

    function domain_disk_add($domain, $img, $dev, $type='scsi', $driver='raw') {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_disk_add($dom, $img, $dev, $type, $driver);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_change_numVCpus($domain, $num) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_change_vcpus($dom, $num);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_change_memory_allocation($domain, $memory, $maxmem) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_change_memory($dom, $memory, $maxmem);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_change_boot_devices($domain, $first, $second) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_change_boot_devices($dom, $first, $second);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_get_screenshot($domain, $convert = 1) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_get_screenshot_api($dom);
        if ($tmp == false)
            return $this->_set_last_error();

        $mime = $tmp['mime'];

        if ($convert && $tmp['mime'] != "image/png") {
            $image = new Imagick();
            $image->readImage($tmp['file']);
            $image->setImageFormat("png");
            $data = $image->getImageBlob();
            $mime = "image/png";
        } else {
            $fp = fopen($tmp['file'], "rb");
            $data = fread($fp, filesize($tmp['file']));
            fclose($fp);
        }
        unlink($tmp['file']);
        unset($tmp['file']);
        $tmp['data'] = $data;
        $tmp['mime'] = $mime;

        return $tmp;
    }

    function domain_get_screenshot_thumbnail($domain, $w=120) {
        $screen = $this->domain_get_screenshot($domain);

        if (!$screen)
            return false;

        $image = new Imagick();
        $image->readImageBlob($screen['data']);
        $origW = $image->getImageWidth();
        $origH = $image->getImageHeight();
        $h = ($w / $origW) * $origH;
        $image->resizeImage($w, $h, 0, 0);

        $screen['data'] = $image->getImageBlob();

        return $screen;
    }

    function domain_get_screen_dimensions($domain) {
        $screen = $this->domain_get_screenshot($domain);
        $imgFile = tempnam("/tmp", "libvirt-php-tmp-resize-XXXXXX");;

        $width = false;
        $height = false;

        if ($screen) {
            $fp = fopen($imgFile, "wb");
            fwrite($fp, $screen);
            fclose($fp);
        }
        if (file_exists($imgFile) && $screen)
            list($width, $height) = getimagesize($imgFile);

        unlink($imgFile);

        return array('height' => $height, 'width' => $width);
    }

    function domain_send_keys($domain, $keys) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_send_keys($dom, $this->get_hostname(), $keys);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_send_pointer_event($domain, $x, $y, $clicked = 1) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_send_pointer_event($dom, $this->get_hostname(), $x, $y, $clicked, true);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_disk_remove($domain, $dev) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_disk_remove($dom, $dev);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function supports($name) {
        return libvirt_has_feature($name);
    }

    function macbyte($val) {
        if ($val < 16)
            return '0'.dechex($val);

        return dechex($val);
    }

    function generate_random_mac_addr($seed=false) {
        if (!$seed)
            $seed = 1;

        if ($this->get_hypervisor_name() == 'qemu') {
            $prefix = '52:54:00';
        } else {
            if ($this->get_hypervisor_name() == 'xen') {
                $prefix = '00:16:3e';
            } else {
                $prefix = $this->macbyte(($seed * rand()) % 256).':'.
                    $this->macbyte(($seed * rand()) % 256).':'.
                    $this->macbyte(($seed * rand()) % 256);
            }
        }

        return $prefix.':'.
            $this->macbyte(($seed * rand()) % 256).':'.
            $this->macbyte(($seed * rand()) % 256).':'.
            $this->macbyte(($seed * rand()) % 256);
    }

    function domain_nic_add($domain, $mac, $network, $model=false) {
        $dom = $this->get_domain_object($domain);

        if ($model == 'default')
            $model = false;

        $tmp = libvirt_domain_nic_add($dom, $mac, $network, $model);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_nic_remove($domain, $mac) {
        $dom = $this->get_domain_object($domain);

        $tmp = libvirt_domain_nic_remove($dom, $mac);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_connection() {
        return $this->conn;
    }

    function get_lib_version() {
        return libvirt_version();
    }

    function get_hostname() {
        return libvirt_connect_get_hostname($this->conn);
    }

    function get_domain_object($nameRes) {
        if (is_resource($nameRes))
            return $nameRes;

        $dom=@libvirt_domain_lookup_by_name($this->conn, $nameRes);
        if (!$dom) {
            $dom=@libvirt_domain_lookup_by_uuid_string($this->conn, $nameRes);
            if (!$dom)
                return $this->_set_last_error();
        }

        return $dom;
    }

    function get_xpath($domain, $xpath, $inactive = false) {
        $dom = $this->get_domain_object($domain);
        $flags = 0;
        if ($inactive)
            $flags = VIR_DOMAIN_XML_INACTIVE;

        $tmp = libvirt_domain_xml_xpath($dom, $xpath, $flags);
        if (!$tmp)
            return $this->_set_last_error();

        return $tmp;
    }

    function get_cdrom_stats($domain, $sort=true) {
        $dom = $this->get_domain_object($domain);

        $buses =  $this->get_xpath($dom, '//domain/devices/disk[@device="cdrom"]/target/@bus', false);
        $disks =  $this->get_xpath($dom, '//domain/devices/disk[@device="cdrom"]/target/@dev', false);

        $ret = array();
        for ($i = 0; $i < $disks['num']; $i++) {
            $tmp = libvirt_domain_get_block_info($dom, $disks[$i]);
            if ($tmp) {
                $tmp['bus'] = $buses[$i];
                $ret[] = $tmp;
            } else {
                $this->_set_last_error();
            }
        }

        if ($sort) {
            for ($i = 0; $i < sizeof($ret); $i++) {
                for ($ii = 0; $ii < sizeof($ret); $ii++) {
                    if (strcmp($ret[$i]['device'], $ret[$ii]['device']) < 0) {
                        $tmp = $ret[$i];
                        $ret[$i] = $ret[$ii];
                        $ret[$ii] = $tmp;
                    }
                }
            }
        }

        unset($buses);
        unset($disks);

        return $ret;
    }

    function get_disk_stats($domain, $sort=true) {
        $dom = $this->get_domain_object($domain);

        $buses =  $this->get_xpath($dom, '//domain/devices/disk[@device="disk"]/target/@bus', false);
        $disks =  $this->get_xpath($dom, '//domain/devices/disk[@device="disk"]/target/@dev', false);
        // create image as: qemu-img create -f qcow2 -o backing_file=RAW_IMG OUT_QCOW_IMG SIZE[K,M,G suffixed]

        $ret = array();
        for ($i = 0; $i < $disks['num']; $i++) {
            $tmp = libvirt_domain_get_block_info($dom, $disks[$i]);
            if ($tmp) {
                $tmp['bus'] = $buses[$i];
                $ret[] = $tmp;
            } else {
                $this->_set_last_error();
            }
        }

        if ($sort) {
            for ($i = 0; $i < sizeof($ret); $i++) {
                for ($ii = 0; $ii < sizeof($ret); $ii++) {
                    if (strcmp($ret[$i]['device'], $ret[$ii]['device']) < 0) {
                        $tmp = $ret[$i];
                        $ret[$i] = $ret[$ii];
                        $ret[$ii] = $tmp;
                    }
                }
            }
        }

        unset($buses);
        unset($disks);

        return $ret;
    }

    function get_nic_info($domain) {
        $dom = $this->get_domain_object($domain);

        $macs =  $this->get_xpath($dom, '//domain/devices/interface[@type="network"]/mac/@address', false);
        if (!$macs)
            return $this->_set_last_error();

        $ret = array();
        for ($i = 0; $i < $macs['num']; $i++) {
            $tmp = libvirt_domain_get_network_info($dom, $macs[$i]);
            if ($tmp)
                //$ret[] = $tmp;
                //Added for arclight because it is not working correctly
                $ret[] = array(
                							'mac' => $macs[$i],
                							'network' => $tmp[$i],
                							'nic_type' => $tmp[$nic_type]
                							);
            else
                $this->_set_last_error();
        }

        return $ret;
    }

    function get_domain_type($domain) {
        $dom = $this->get_domain_object($domain);

        $tmp = $this->get_xpath($dom, '//domain/@type', false);
        if ($tmp['num'] == 0)
            return $this->_set_last_error();

        $ret = $tmp[0];
        unset($tmp);

        return $ret;
    }

    function get_domain_emulator($domain) {
        $dom = $this->get_domain_object($domain);

        $tmp =  $this->get_xpath($dom, '//domain/devices/emulator', false);
        if ($tmp['num'] == 0)
            return $this->_set_last_error();

        $ret = $tmp[0];
        unset($tmp);

        return $ret;
    }

    function get_network_cards($domain) {
        $dom = $this->get_domain_object($domain);

        $nics =  $this->get_xpath($dom, '//domain/devices/interface[@type="network"]', false);
        if (!is_array($nics))
            return $this->_set_last_error();

        return $nics['num'];
    }

    function get_disk_capacity($domain, $physical=false, $disk='*', $unit='?') {
        $dom = $this->get_domain_object($domain);
        $tmp = $this->get_disk_stats($dom);

        $ret = 0;
        for ($i = 0; $i < sizeof($tmp); $i++) {
            if (($disk == '*') || ($tmp[$i]['device'] == $disk))
                if ($physical)
                    $ret += $tmp[$i]['physical'];
                else
                    $ret += $tmp[$i]['capacity'];
        }
        unset($tmp);

        return $this->format_size($ret, 2, $unit);
    }

    function get_disk_count($domain) {
        $dom = $this->get_domain_object($domain);
        $tmp = $this->get_disk_stats($dom);
        $ret = sizeof($tmp);
        unset($tmp);

        return $ret;
    }

    function format_size($value, $decimals, $unit='?') {
        /* Autodetect unit that's appropriate */
        if ($unit == '?') {
            /* (1 << 40) is not working correctly on i386 systems */
            if ($value > 1099511627776)
                $unit = 'T';
            else if ($value > (1 << 30))
                $unit = 'G';
            else if ($value > (1 << 20))
                $unit = 'M';
            else if ($value > (1 << 10))
                $unit = 'K';
            else
                $unit = 'B';
        }

        $unit = strtoupper($unit);

        switch ($unit) {
        case 'T': return number_format($value / (float)1099511627776, $decimals, '.', ' ').' TB';
        case 'G': return number_format($value / (float)(1 << 30), $decimals, '.', ' ').' GB';
        case 'M': return number_format($value / (float)(1 << 20), $decimals, '.', ' ').' MB';
        case 'K': return number_format($value / (float)(1 << 10), $decimals, '.', ' ').' kB';
        case 'B': return $value.' B';
        }

        return false;
    }

    function get_uri() {
        $tmp = libvirt_connect_get_uri($this->conn);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_domain_count() {
        $tmp = libvirt_domain_get_counts($this->conn);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_storagepools() {
        $tmp = libvirt_list_storagepools($this->conn);
        if ($tmp)
            sort($tmp, SORT_NATURAL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function list_domain_snapshots($domain) {
      $tmp = libvirt_list_domain_snapshots($domain);
      return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight

    function get_storagepool_res($res) {
        if ($res == false)
            return false;
        if (is_resource($res))
            return $res;

        $tmp = libvirt_storagepool_lookup_by_name($this->conn, $res);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_storagepool_info($name) {
        if (!($res = $this->get_storagepool_res($name)))
            return false;

            $xml = libvirt_storagepool_get_xml_desc($res);

        $path = libvirt_storagepool_get_xml_desc($res, '/pool/target/path');
        if (!$path)
            return $this->_set_last_error();
        $perms = libvirt_storagepool_get_xml_desc($res, '/pool/target/permissions/mode');
        if (!$perms)
            return $this->_set_last_error();
        $otmp1 = libvirt_storagepool_get_xml_desc($res, '/pool/target/permissions/owner');
        if (!is_string($otmp1))
            return $this->_set_last_error();
        $otmp2 = libvirt_storagepool_get_xml_desc($res, '/pool/target/permissions/group');
        if (!is_string($otmp2))
            return $this->_set_last_error();
        $tmp = libvirt_storagepool_get_info($res);
        $tmp['xml'] = $xml;
        $tmp['active'] = libvirt_storagepool_is_active($res);
        $tmp['path'] = $path;
        $tmp['permissions'] = $perms;
        $tmp['id_user'] = $otmp1;
        $tmp['id_group'] = $otmp2;
        if ($tmp['active']) {
            $tmp['volume_count'] = sizeof( libvirt_storagepool_list_volumes($res) );
        } else {
            $tmp['volume_count'] = 0;
        }

        return $tmp;
    }

    function storagepool_get_volume_information($pool, $name=false) {
        if (!is_resource($pool))
            $pool = $this->get_storagepool_res($pool);
        if (!$pool)
            return false;

        $out = array();
        $tmp = libvirt_storagepool_list_volumes($pool);
        for ($i = 0; $i < sizeof($tmp); $i++) {
            if (($tmp[$i] == $name) || ($name == false)) {
                $r = libvirt_storagevolume_lookup_by_name($pool, $tmp[$i]);
                $out[$tmp[$i]] = libvirt_storagevolume_get_info($r);
                $out[$tmp[$i]]['path'] = libvirt_storagevolume_get_path($r);
                unset($r);
            }
        }

        return $out;
    }


    function storagepool_define_xml($xml) {
      $ret = libvirt_storagepool_define_xml($this->conn, $xml);
      return ($ret) ? $ret : $this->_set_last_error();
    } //Added for arclight

   
    function storagepool_undefine($res) {
      $ret = libvirt_storagepool_undefine($res);
      return ($ret) ? $ret : $this->_set_last_error();
    } //Added for arclight


    function storagepool_destroy($res) {
      $ret = libvirt_storagepool_destroy($res);
      return ($ret) ? $ret : $this->_set_last_error();
    } //Added for arclight


    function storagepool_create($res) {
      $ret = libvirt_storagepool_create($res);
      return ($ret) ? $ret : $this->_set_last_error();
    } //Added for arclight

    function storagepool_refresh($res) {
      $ret = libvirt_storagepool_refresh($res);
      return ($ret) ? $ret : $this->_set_last_error();
    } //Added for arclight


    function storagevolume_delete($path) {
        $vol = libvirt_storagevolume_lookup_by_path($this->conn, $path);
        if (!libvirt_storagevolume_delete($vol))
            return $this->_set_last_error();

        return true;
    }

    //Storage volume resize
    function storagevolume_resize($path, $size) {
        $vol = libvirt_storagevolume_lookup_by_path($this->conn, $path);
        if (!libvirt_storagevolume_resize($vol, $size))
            return $this->_set_last_error();

        return true;
    }


    function translate_volume_type($type) {
        if ($type == 1)
            return 'Block device';

        return 'File image';
    }

    function translate_perms($mode) {
        $mode = (int)$mode;

        $tmp = '---------';

        for ($i = 2; $i >=0 ; $i--) {
            $bits = $mode % 10;
            $mode /= 10;
            if ($bits & 4)
                $tmp[ ($i * 3) ] = 'r';
            if ($bits & 2)
                $tmp[ ($i * 3) + 1 ] = 'w';
            if ($bits & 1)
                $tmp[ ($i * 3) + 2 ] = 'x';
        }


        return $tmp;
    }

    function parse_size($size) {
        $unit = $size[ strlen($size) - 1 ];

        $size = (int)$size;
        switch (strtoupper($unit)) {
        case 'T': $size *= 1099511627776;
        break;
        case 'G': $size *= 1073741824;
        break;
        case 'M': $size *= 1048576;
        break;
        case 'K': $size *= 1024;
        break;
        }

        return $size;
    }

    function storagevolume_create($pool, $name, $capacity, $allocation, $type) {
        $pool = $this->get_storagepool_res($pool);

        $capacity = $this->parse_size($capacity);
        $allocation = $this->parse_size($allocation);

        $xml = "<volume>\n".
        "  <name>$name</name>\n".
        "  <capacity>$capacity</capacity>\n".
        "  <allocation>$allocation</allocation>\n".
        "  <target><format type='" . $type . "' /></target>\n".
        "</volume>";

        $tmp = libvirt_storagevolume_create_xml($pool, $xml);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }


 
    function storagevolume_create_xml_from($pool_name, $original_volume_path, $original_volume_name, $clone_name) {
        $pool = $this->get_storagepool_res($pool_name);
        $original_volume = libvirt_storagevolume_lookup_by_path($this->conn, $original_volume_path);

        $original_filename = pathinfo($original_volume_name, PATHINFO_FILENAME);
        $original_extension = pathinfo($original_volume_name, PATHINFO_EXTENSION);
        
        if (strtolower($original_extension) == "img"){
            $format_type = "raw";
        } else {
            $format_type = strtolower($original_extension);
        }

        $xml = "
        <volume>
            <name>$clone_name</name>
            <target>
                <format type='$format_type'/>
            </target>
        </volume>
        ";

        $tmp = libvirt_storagevolume_create_xml_from($pool, $xml, $original_volume);
        return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight



    function get_hypervisor_name() {
        $tmp = libvirt_connect_get_information($this->conn);
        $hv = $tmp['hypervisor'];
        unset($tmp);

        switch (strtoupper($hv)) {
        case 'QEMU': $type = 'qemu';
        break;
        case 'XEN': $type = 'xen';
        break;

        default:
        $type = $hv;
        }

        return $type;
    }

    function get_connect_information() {
        $tmp = libvirt_connect_get_information($this->conn);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_change_xml($domain, $xml) {
        $dom = $this->get_domain_object($domain);

        if (!($old_xml = libvirt_domain_get_xml_desc($dom, NULL)))
            return $this->_set_last_error();
        if (!libvirt_domain_undefine($dom))
            return $this->_set_last_error();
        if (!libvirt_domain_define_xml($this->conn, $xml)) {
            $this->last_error = libvirt_get_last_error();
            libvirt_domain_define_xml($this->conn, $old_xml);
            return false;
        }

        return true;
    }

    function network_change_xml($network, $xml) {
        $net = $this->get_network_res($network);

        if (!($old_xml = libvirt_network_get_xml_desc($net, NULL))) {
            return $this->_set_last_error();
        }
        if (!libvirt_network_undefine($net)) {
            return $this->_set_last_error();
        }
        if (!libvirt_network_define_xml($this->conn, $xml)) {
            $this->last_error = libvirt_get_last_error();
            libvirt_network_define_xml($this->conn, $old_xml);
            return false;
        }

        return true;
    }

   
    function network_define_xml($xml) {
      $tmp = libvirt_network_define_xml($this->conn, $xml);
      return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight


    function network_undefine($network) {
      $net = libvirt_network_get($this->conn, $network);
      $tmp = libvirt_network_undefine($net);
      return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight


    function translate_storagepool_state($state) {
        switch ($state) {
        case 0: return 'Not running';
        break;
        case 1: return 'Building pool';
        break;
        case 2: return 'Running';
        break;
        case 3: return 'Running degraded';
        break;
        case 4: return 'Running but inaccessible';
        break;
        }

        return 'Unknown';
    }

    function get_domains() {
        $tmp = libvirt_list_domains($this->conn);
        if ($tmp)
            sort($tmp, SORT_NATURAL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_domain_by_name($name) {
        $tmp = libvirt_domain_lookup_by_name($this->conn, $name);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_networks($type = VIR_NETWORKS_ALL) {
        $tmp = libvirt_list_networks($this->conn, $type);
        if ($tmp)
            sort($tmp, SORT_NATURAL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_nic_models() {
        return array('default', 'rtl8139', 'e1000', 'pcnet', 'ne2k_pci', 'virtio');
    }

    function get_network_res($network) {
        if ($network == false)
            return false;
        if (is_resource($network))
            return $network;

        $tmp = libvirt_network_get($this->conn, $network);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_network_bridge($network) {
        $res = $this->get_network_res($network);
        if ($res == false)
            return false;

        $tmp = libvirt_network_get_bridge($res);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_network_active($network) {
        $res = $this->get_network_res($network);
        if ($res == false)
            return false;

        $tmp = libvirt_network_get_active($res);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function set_network_active($network, $active = true) {
        $res = $this->get_network_res($network);
        if ($res == false)
            return false;

        if (!libvirt_network_set_active($res, $active ? 1 : 0))
            return $this->_set_last_error();

        return true;
    }

    function get_network_information($network) {
        $res = $this->get_network_res($network);
        if ($res == false)
            return false;

        $tmp = libvirt_network_get_information($res);
        if (!$tmp)
            return $this->_set_last_error();
        $tmp['active'] = $this->get_network_active($res);
        return $tmp;
    }

    function get_network_xml($network) {
        $res = $this->get_network_res($network);
        if ($res == false)
            return false;

        $tmp = libvirt_network_get_xml_desc($res, NULL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_node_devices($dev = false) {
        $tmp = ($dev == false) ? libvirt_list_nodedevs($this->conn) : libvirt_list_nodedevs($this->conn, $dev);
        if ($tmp)
            sort($tmp, SORT_NATURAL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_node_device_res($res) {
        if ($res == false)
            return false;
        if (is_resource($res))
            return $res;

        $tmp = libvirt_nodedev_get($this->conn, $res);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_node_device_caps($dev) {
        $dev = $this->get_node_device_res($dev);

        $tmp = libvirt_nodedev_capabilities($dev);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_node_device_cap_options() {
        $all = $this->get_node_devices();

        $ret = array();
        for ($i = 0; $i < sizeof($all); $i++) {
            $tmp = $this->get_node_device_caps($all[$i]);

            for ($ii = 0; $ii < sizeof($tmp); $ii++)
                if (!in_array($tmp[$ii], $ret))
                    $ret[] = $tmp[$ii];
        }

        return $ret;
    }

    function get_node_device_xml($dev) {
        $dev = $this->get_node_device_res($dev);

        $tmp = libvirt_nodedev_get_xml_desc($dev, NULL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_node_device_information($dev) {
        $dev = $this->get_node_device_res($dev);

        $tmp = libvirt_nodedev_get_information($dev);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_get_name($res) {
        return libvirt_domain_get_name($res);
    }

    function domain_get_info($dom) {
        if (!$this->allow_cached)
            return libvirt_domain_get_info($dom);

        $domname = libvirt_domain_get_name($dom);
        if (!array_key_exists($domname, $this->dominfos)) {
            $info = libvirt_domain_get_info($dom);
            $this->dominfos[$domname] = $info;
        }

        return $this->dominfos[$domname];
    }

    function get_last_error() {
        return $this->last_error;
    }

    function domain_get_xml($domain, $get_inactive = false) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_get_xml_desc($dom, $get_inactive ? VIR_DOMAIN_XML_INACTIVE : 0);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }


    function network_get_xml($network) {
        $net = $this->get_network_res($network);
        if (!$net)
            return false;

        $tmp = libvirt_network_get_xml_desc($net, NULL);
        return ($tmp) ? $tmp : $this->_set_last_error();;
    }


    function domain_get_id($domain, $name = false) {
        $dom = $this->get_domain_object($domain);
        if ((!$dom) || (!$this->domain_is_running($dom, $name)))
            return false;

        $tmp = libvirt_domain_get_id($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_get_interface_stats($nameRes, $iface) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_interface_stats($dom, $iface);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_get_memory_stats($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_memory_stats($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_start($dom) {
        $dom=$this->get_domain_object($dom);
        if ($dom) {
            $ret = libvirt_domain_create($dom);
            $this->last_error = libvirt_get_last_error();
            return $ret;
        }

        $ret = libvirt_domain_create_xml($this->conn, $dom);
        $this->last_error = libvirt_get_last_error();
        return $ret;
    }

    function domain_define($xml) {
        $tmp = libvirt_domain_define_xml($this->conn, $xml);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_destroy($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_destroy($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_reboot($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_reboot($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_suspend($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_suspend($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight

    function domain_resume($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_resume($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight


    function domain_update_device($domain, $xml, $flags=false) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_update_device($res, $xml);
        return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight




    function domain_get_name_by_uuid($uuid) {
        $dom = libvirt_domain_lookup_by_uuid_string($this->conn, $uuid);
        if (!$dom)
            return false;
        $tmp = libvirt_domain_get_name($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_shutdown($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_shutdown($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_snapshot_create($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_snapshot_create($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    } //Added for arclight

    function domain_undefine($domain) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;

        $tmp = libvirt_domain_undefine($dom);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_is_running($domain, $name = false) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;
        $tmp = $this->domain_get_info($dom);
        if (!$tmp)
            return $this->_set_last_error();
        $ret = ( ($tmp['state'] == VIR_DOMAIN_RUNNING) || ($tmp['state'] == VIR_DOMAIN_BLOCKED) );
        unset($tmp);
        return $ret;
    }

    function domain_is_paused($domain, $name = false) {
        $dom = $this->get_domain_object($domain);
        if (!$dom)
            return false;
        $tmp = $this->domain_get_info($dom);
        if (!$tmp)
            return $this->_set_last_error();
        $ret = ( ($tmp['state'] == VIR_DOMAIN_PAUSED) );
        unset($tmp);
        return $ret;
    }

    function domain_state_translate($state) {
        switch ($state) {
        case VIR_DOMAIN_RUNNING:  return 'running';
        case VIR_DOMAIN_NOSTATE:  return 'nostate';
        case VIR_DOMAIN_BLOCKED:  return 'blocked';
        case VIR_DOMAIN_PAUSED:   return 'paused';
        case VIR_DOMAIN_SHUTDOWN: return 'shutdown';
        case VIR_DOMAIN_SHUTOFF:  return 'shutoff';
        case VIR_DOMAIN_CRASHED:  return 'crashed';
        }

        return 'unknown';
    }

    function domain_get_vnc_port($domain) {
        $tmp = $this->get_xpath($domain, '//domain/devices/graphics/@port', false);
        $var = (int)$tmp[0];
        unset($tmp);

        return $var;
    }

    function domain_get_arch($domain) {
        $domain = $this->get_domain_object($domain);

        $tmp = $this->get_xpath($domain, '//domain/os/type/@arch', false);
        $var = $tmp[0];
        unset($tmp);

        return $var;
    }

    function domain_get_description($domain) {
        $tmp = $this->get_xpath($domain, '//domain/description', false);
        $var = $tmp[0];
        unset($tmp);

        return $var;
    }

    function domain_get_clock_offset($domain) {
        $tmp = $this->get_xpath($domain, '//domain/clock/@offset', false);
        $var = $tmp[0];
        unset($tmp);

        return $var;
    }

    function domain_get_feature($domain, $feature) {
        $tmp = $this->get_xpath($domain, '//domain/features/'.$feature.'/..', false);
        $ret = ($tmp != false);
        unset($tmp);

        return $ret;
    }

    function domain_get_boot_devices($domain) {
        $tmp = $this->get_xpath($domain, '//domain/os/boot/@dev', false);
        if (!$tmp)
            return false;

        $devs = array();
        for ($i = 0; $i < $tmp['num']; $i++)
            $devs[] = $tmp[$i];

        return $devs;
    }

    function _get_single_xpath_result($domain, $xpath) {
        $tmp = $this->get_xpath($domain, $xpath, false);
        if (!$tmp)
            return false;

        if ($tmp['num'] == 0)
            return false;

        return $tmp[0];
    }

    function domain_get_multimedia_device($domain, $type, $display=false) {
        $domain = $this->get_domain_object($domain);

        if ($type == 'console') {
            $type = $this->_get_single_xpath_result($domain, '//domain/devices/console/@type');
            $targetType = $this->_get_single_xpath_result($domain, '//domain/devices/console/target/@type');
            $targetPort = $this->_get_single_xpath_result($domain, '//domain/devices/console/target/@port');

            if ($display)
                return $type.' ('.$targetType.' on port '.$targetPort.')';
            else
                return array('type' => $type, 'targetType' => $targetType, 'targetPort' => $targetPort);
        } else if ($type == 'input') {
            $type = $this->_get_single_xpath_result($domain, '//domain/devices/input/@type');
            $bus  = $this->_get_single_xpath_result($domain, '//domain/devices/input/@bus');

            if ($display)
                return $type.' on '.$bus;
            else
                return array('type' => $type, 'bus' => $bus);
        } else if ($type == 'graphics') {
            $type = $this->_get_single_xpath_result($domain, '//domain/devices/graphics/@type');
            $port = $this->_get_single_xpath_result($domain, '//domain/devices/graphics/@port');
            $autoport = $this->_get_single_xpath_result($domain, '//domain/devices/graphics/@autoport');

            if ($display)
                return $type.' on port '.$port.' with'.($autoport ? '' : 'out').' autoport enabled';
            else
                return array('type' => $type, 'port' => $port, 'autoport' => $autoport);
        } else if ($type == 'video') {
            $type  = $this->_get_single_xpath_result($domain, '//domain/devices/video/model/@type');
            $vram  = $this->_get_single_xpath_result($domain, '//domain/devices/video/model/@vram');
            $heads = $this->_get_single_xpath_result($domain, '//domain/devices/video/model/@heads');

            if ($display)
                return $type.' with '.($vram / 1024).' MB VRAM, '.$heads.' head(s)';
            else
                return array('type' => $type, 'vram' => $vram, 'heads' => $heads);
        } else {
            return false;
        }
    }

    function domain_get_host_devices_pci($domain) {
        $xpath = '//domain/devices/hostdev[@type="pci"]/source/address/@';

        $dom  = $this->get_xpath($domain, $xpath.'domain', false);
        $bus  = $this->get_xpath($domain, $xpath.'bus', false);
        $slot = $this->get_xpath($domain, $xpath.'slot', false);
        $func = $this->get_xpath($domain, $xpath.'function', false);

        $devs = array();
        for ($i = 0; $i < $bus['num']; $i++) {
            $d = str_replace('0x', '', $dom[$i]);
            $b = str_replace('0x', '', $bus[$i]);
            $s = str_replace('0x', '', $slot[$i]);
            $f = str_replace('0x', '', $func[$i]);
            $devid = 'pci_'.$d.'_'.$b.'_'.$s.'_'.$f;
            $tmp2 = $this->get_node_device_information($devid);
            $devs[] = array('domain' => $dom[$i], 'bus' => $bus[$i],
                'slot' => $slot[$i], 'func' => $func[$i],
                'vendor' => $tmp2['vendor_name'],
                'vendor_id' => $tmp2['vendor_id'],
                'product' => $tmp2['product_name'],
                'product_id' => $tmp2['product_id']);
        }

        return $devs;
    }

    function _lookup_device_usb($vendor_id, $product_id) {
        $tmp = $this->get_node_devices(false);
        for ($i = 0; $i < sizeof($tmp); $i++) {
            $tmp2 = $this->get_node_device_information($tmp[$i]);
            if (array_key_exists('product_id', $tmp2)) {
                if (($tmp2['product_id'] == $product_id)
                    && ($tmp2['vendor_id'] == $vendor_id))
                    return $tmp2;
            }
        }

        return false;
    }

    function domain_get_host_devices_usb($domain) {
        $xpath = '//domain/devices/hostdev[@type="usb"]/source/';

        $vid = $this->get_xpath($domain, $xpath.'vendor/@id', false);
        $pid = $this->get_xpath($domain, $xpath.'product/@id', false);

        $devs = array();
        for ($i = 0; $i < $vid['num']; $i++) {
            $dev = $this->_lookup_device_usb($vid[$i], $pid[$i]);
            $devs[] = array('vendor_id' => $vid[$i], 'product_id' => $pid[$i],
                'product' => $dev['product_name'],
                'vendor' => $dev['vendor_name']);
        }

        return $devs;
    }

    function domain_get_host_devices($domain) {
        $domain = $this->get_domain_object($domain);

        $devs_pci = $this->domain_get_host_devices_pci($domain);
        $devs_usb = $this->domain_get_host_devices_usb($domain);

        return array('pci' => $devs_pci, 'usb' => $devs_usb);
    }

    function domain_set_feature($domain, $feature, $val) {
        $domain = $this->get_domain_object($domain);

        if ($this->domain_get_feature($domain, $feature) == $val)
            return true;

        $xml = $this->domain_get_xml($domain, true);
        if ($val) {
            if (strpos('features', $xml))
                $xml = str_replace('<features>', "<features>\n<$feature/>", $xml);
            else
                $xml = str_replace('</os>', "</os><features>\n<$feature/></features>", $xml);
        } else {
            $xml = str_replace("<$feature/>\n", '', $xml);
        }

        return $this->domain_change_xml($domain, $xml);
    }

    function domain_set_clock_offset($domain, $offset) {
        $domain = $this->get_domain_object($domain);

        if (($old_offset = $this->domain_get_clock_offset($domain)) == $offset)
            return true;

        $xml = $this->domain_get_xml($domain, true);
        $xml = str_replace("<clock offset='$old_offset'/>", "<clock offset='$offset'/>", $xml);

        return $this->domain_change_xml($domain, $xml);
    }

    function domain_set_description($domain, $desc) {
        $domain = $this->get_domain_object($domain);

        $description = $this->domain_get_description($domain);
        if ($description == $desc)
            return true;

        $xml = $this->domain_get_xml($domain, true);
        if (!$description) {
            $xml = str_replace("</uuid>", "</uuid><description>$desc</description>", $xml);
        } else {
            $tmp = explode("\n", $xml);
            for ($i = 0; $i < sizeof($tmp); $i++)
                if (strpos('.'.$tmp[$i], '<description'))
                    $tmp[$i] = "<description>$desc</description>";

            $xml = join("\n", $tmp);
        }

        return $this->domain_change_xml($domain, $xml);
    }

    function host_get_node_info() {
        $tmp = libvirt_node_get_info($this->conn);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function host_get_node_cpu_stats() {
        $tmp = libvirt_node_get_cpu_stats($this->conn);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function host_get_node_cpu_stats_for_each_cpu() {
        $tmp = libvirt_node_get_cpu_stats_for_each_cpu($this->conn,1);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function host_get_node_mem_stats() {
        $tmp = libvirt_node_get_mem_stats($this->conn);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function domain_is_active($domain) {
        $dom = $this->get_domain_object($domain);
        return libvirt_domain_is_active($dom);
    }

    function get_nwfilters() {
        $tmp = libvirt_list_all_nwfilters($this->conn);
        if ($tmp)
            sort($tmp, SORT_NATURAL);
        return ($tmp) ? $tmp : $this->_set_last_error();
    }

    function get_nwfilter_xml($uuid) {
        $nwfilter = libvirt_nwfilter_lookup_by_uuid_string($this->conn, $uuid);
        if (!$nwfilter)
            return $this->_set_last_error();
        return libvirt_nwfilter_get_xml_desc($nwfilter);
    }


    //list all snapshots for domain
    function domain_snapshots_list($domain) {
    	$tmp = libvirt_list_domain_snapshots($domain);
    	return ($tmp) ? $tmp : $this->_set_last_error();
    }

    //get snapshot description
		function domain_snapshot_get_info($domain, $name) {
			$domain = $this->get_domain_object($domain);
			$tmp = $this->get_xpath($domain, '//domain/metadata/snapshot'.$name, false);
			$var = $tmp[0];
			unset($tmp);
			return $var;

		}

    //delete snapshot and metadata
		function domain_snapshot_delete($domain, $name) {
			//$this->snapshot_remove_metadata($domain, $name);
			$name = $this->domain_snapshot_lookup_by_name($domain, $name);
			$tmp = libvirt_domain_snapshot_delete($name);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

    //get resource number of snapshot
		function domain_snapshot_lookup_by_name($domain, $name) {
			$domain = $this->get_domain_object($domain);
			$tmp = libvirt_domain_snapshot_lookup_by_name($domain, $name);
			return ($tmp) ? $tmp : $this->_set_last_error();
		}

    //revert domain to snapshot state
    function domain_snapshot_revert($domain, $name) {
    	$name = $this->domain_snapshot_lookup_by_name($domain, $name);
    	$tmp = libvirt_domain_snapshot_revert($name);
    	return ($tmp) ? $tmp : $this->_set_last_error();
    }

    //get domain xml info
    function domain_snapshot_get_xml($domain, $name) {
    	$name = $this->domain_snapshot_lookup_by_name($domain, $name);
    	$tmp = libvirt_domain_snapshot_get_xml($name);
    	return ($tmp) ? $tmp : $this->_set_last_error();
    }

    //Returns 1 for enabled and false for disabled.
    function domain_get_autostart($res) {
      $tmp = libvirt_domain_get_autostart($res);
    	return ($tmp) ? $tmp : $this->_set_last_error();
    }

    //Setting the autostart essential places a shortcut of domain in /etc/libvirt/qemu/autostart/
    function domain_set_autostart($res, $int = 0) {
      $tmp = libvirt_domain_set_autostart($res, $int);
    	return ($tmp) ? $tmp : $this->_set_last_error();
    }



}
?>
