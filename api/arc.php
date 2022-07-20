<script>
    const createInstance = function() {
        const userid = '<?php echo $_SESSION['userid']; ?>';
        const uuid = null;
        const action = '<?php echo $_SESSION['action']; ?>';
        const username = '<?php echo $_SESSION['username']; ?>';
        const instance_type = '<?php echo $_SESSION['instance_type']; ?>';
        const domain_name = '<?php echo $_SESSION['domain_name']; ?>';
        const os = '<?php echo $_SESSION['os_platform']; ?>';
        const vcpu = '<?php echo $_SESSION['vcpu']; ?>';
        const cores = '<?php if (isset($_SESSION['tcores'])) {
                            echo $_SESSION['tcores'];
                        } else {
                            echo "";
                        } ?>';
        const threads = '<?php if (isset($_SESSION['tthreads'])) {
                                echo $_SESSION['tthreads'];
                            } else {
                                echo "";
                            } ?>';
        const memory = '<?php echo $_SESSION['memory']; ?>';
        const memory_unit = '<?php echo $_SESSION['memory_unit']; ?>';
        const source_file_volume = '<?php echo $_SESSION['source_file_volume']; ?>';
        const volume_image_name = '<?php echo $_SESSION['volume_image_name']; ?>';
        const volume_size = '<?php echo $_SESSION['volume_size']; ?>';
        const driver_type = '<?php echo $_SESSION['driver_type']; ?>';
        const target_bus = '<?php echo $_SESSION['target_bus']; ?>';
        const storage_pool = '<?php echo $_SESSION['storage_pool']; ?>';
        const existing_driver_type = '<?php echo $_SESSION['existing_driver_type']; ?>';
        const existing_target_bus = '<?php echo $_SESSION['existing_target_bus']; ?>';
        const source_file_cd = '<?php echo $_SESSION['source_file_cd']; ?>';
        const mac_address = '<?php echo $_SESSION['mac_address']; ?>';
        const model_type = '<?php echo $_SESSION['model_type']; ?>';
        const source_network = '<?php echo $_SESSION['source_network']; ?>';
        const token = localStorage.getItem('token');

        try {
            axios.post(`/api/v1/instance/createinstance`, {
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
                }, {
                    headers: {
                        'Access-Control-Allow-Origin': '*',
                        'Authorization': 'Bearer ' + token
                    }
                })
                .then(function(response) {
                    if (response.status == 200 && response.data.success == 1) {
                        // alert('Instance created successfully');
                        console.log(response);
                        // window.location.href = '/arc/instances';
                    } else {
                        console.log(response);
                    }
                })
        } catch (err) {
            console.log(err);
        }
    };


    //arclight events
    const createEvent = function(){
        const description = '<?php echo $description; ?>';
        const hostuuid = '<?php echo $host_uuid ?>';
        const domainuuid = '<?php echo $domain_uuid; ?>';
        const userid = '<?php echo $userid; ?>';
        const token = localStorage.getItem('token');

            try{
                axios.post(`/api/v1/event/arc_event`, {
                        description: description,
                        host_uuid: hostuuid,
                        domain_uuid: domainuuid,
                        userid: userid,
                    }, {
                        headers: {
                            'Access-Control-Allow-Origin': '*',
                            'Authorization': 'Bearer ' + token
                        }
                    })
                    .then(function(response) {
                        if (response.status == 200 && response.data.success == 1) {
                            // alert('Instance created successfully');
                            console.log(response);
                            // window.location.href = '/arc/instances';
                        } else {
                            console.log(response);
                        }
                    })
            }catch(err){
                console.log(err);

            }
    }
</script>