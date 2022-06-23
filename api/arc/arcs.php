<script>
    // send request to create instance api
    // var form = document.getElementById('createDomainForm');
    // form.addEventListener('submit', function(e) {
    var createInstance = function() {
        var userid = '<?php echo $_SESSION['userid']; ?>';
        var uuid = '<?php echo $_SESSION['uuid']; ?>';
        var action = '<?php echo $_SESSION['action']; ?>';
        var username = '<?php echo $_SESSION['username']; ?>';
        var instance_type = '<?php echo $_SESSION['instance_type']; ?>';
        var domain_name = '<?php echo $_SESSION['domain_name']; ?>';
        var os = '<?php echo $_SESSION['os_platform']; ?>';
        var vcpu = '<?php echo $_SESSION['vcpu']; ?>';
        var cores = '<?php if (isset($_SESSION['tcores'])) {
                            echo $_SESSION['tcores'];
                        } else {
                            echo "";
                        } ?>';
        var threads = '<?php if (isset($_SESSION['tthreads'])) {
                            echo $_SESSION['tthreads'];
                        } else {
                            echo "";
                        } ?>';
        var memory = '<?php echo $_SESSION['memory']; ?>';
        var memory_unit = '<?php echo $_SESSION['memory_unit']; ?>';
        var source_file_volume = '<?php echo $_SESSION['source_file_volume']; ?>';
        var volume_image_name = '<?php echo $_SESSION['volume_image_name']; ?>';
        var volume_size = '<?php echo $_SESSION['volume_size']; ?>';
        var driver_type = '<?php echo $_SESSION['driver_type']; ?>';
        var target_bus = '<?php echo $_SESSION['target_bus']; ?>';
        var storage_pool = '<?php echo $_SESSION['storage_pool']; ?>';
        var existing_driver_type = '<?php echo $_SESSION['existing_driver_type']; ?>';
        var existing_target_bus = '<?php echo $_SESSION['existing_target_bus']; ?>';
        var source_file_cd = '<?php echo $_SESSION['source_file_cd']; ?>';
        var mac_address = '<?php echo $_SESSION['mac_address']; ?>';
        var model_type = '<?php echo $_SESSION['model_type']; ?>';
        var source_network = '<?php echo $_SESSION['source_network']; ?>';
        var dt = "<?php echo date("Y-m-d H:i:s"); ?>";

        // change protocol according to the localhost protocol
        var protocol = window.location.protocol;
        if (protocol == "https:") {
            var port = "3000";
        } else {
            var port = "3001";
        }
        //'https://localhost:3000/api/users/arcs'
        //Fetch POST request
        fetch(`${protocol}//3.111.98.248:${port}/api/users/arcs`, {
            method: 'POST',
            body: JSON.stringify({
                userid: userid,
                uuid: uuid,
                action: action,
                username: username,
                instance_type: instance_type,
                domain_name: domain_name,
                os: os,
                vcpu: vcpu,
                cores: cores,
                threads: threads,
                memory: memory,
                memory_unit: memory_unit,
                source_file_volume: source_file_volume,
                volume_image_name: volume_image_name,
                volume_size: volume_size,
                driver_type: driver_type,
                target_bus: target_bus,
                storage_pool: storage_pool,
                existing_driver_type: existing_driver_type,
                existing_target_bus: existing_target_bus,
                source_file_cd: source_file_cd,
                mac_address: mac_address,
                model_type: model_type,
                source_network: source_network,
                dt: dt
            }),
            headers: {
                Accept: 'application/json',
                'Access-Control-Allow-Origin': '*',
                'Content-Type': 'application/json',
                // Authorization: 'Bearer ' + token // if you use token
            }
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            console.log(data);
        });
    };
</script>