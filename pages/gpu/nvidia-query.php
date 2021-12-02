<?php
// Comment or uncomment on the value that you want to pull from Nvidia GPU, better always keep index (the gpu_id) on top.

// All of ECC returned "[Not Supported]". So we disabled them but you can try by yourself.

$queries = array(
	"index",
	"timestamp",
	"driver_version",
	"count",
	"name",
	"serial",
	"uuid",
	"pci.bus_id",
	"pci.domain",
	"pci.bus",
	"pci.device",
	"pci.device_id",
	"pci.sub_device_id",
	"pcie.link.gen.current",
	"pcie.link.gen.max",
	"pcie.link.width.current",
	"pcie.link.width.max",
	"display_mode",
	"display_active",
	"persistence_mode",
	"accounting.mode",
	"accounting.buffer_size",
	"driver_model.current",
	"driver_model.pending",
	"vbios_version",
	"inforom.img",
	"inforom.oem",
	"inforom.ecc",
	"inforom.pwr",
	"gom.current",
	"gom.pending",
	"fan.speed",
	"pstate",
	"clocks_throttle_reasons.supported",
	"clocks_throttle_reasons.active",
	"clocks_throttle_reasons.gpu_idle",
	"clocks_throttle_reasons.applications_clocks_setting",
	"clocks_throttle_reasons.sw_power_cap",
	"clocks_throttle_reasons.hw_slowdown",
	"clocks_throttle_reasons.hw_thermal_slowdown",
	"clocks_throttle_reasons.hw_power_brake_slowdown",
	"clocks_throttle_reasons.sync_boost",
	"memory.total",
	"memory.used",
	"memory.free",
	"compute_mode",
	"utilization.gpu",
	"utilization.memory",
	"encoder.stats.sessionCount",
	"encoder.stats.averageFps",
	"encoder.stats.averageLatency",
//	"ecc.mode.current",
//	"ecc.mode.pending",
//	"ecc.errors.corrected.volatile.device_memory",
//	"ecc.errors.corrected.volatile.register_file",
//	"ecc.errors.corrected.volatile.l1_cache",
//	"ecc.errors.corrected.volatile.l2_cache",
//	"ecc.errors.corrected.volatile.texture_memory",
//	"ecc.errors.corrected.volatile.total",
//	"ecc.errors.corrected.aggregate.device_memory",
//	"ecc.errors.corrected.aggregate.register_file",
//	"ecc.errors.corrected.aggregate.l1_cache",
//	"ecc.errors.corrected.aggregate.l2_cache",
//	"ecc.errors.corrected.aggregate.texture_memory",
//	"ecc.errors.corrected.aggregate.total",
//	"ecc.errors.uncorrected.volatile.device_memory",
//	"ecc.errors.uncorrected.volatile.register_file",
//	"ecc.errors.uncorrected.volatile.l1_cache",
//	"ecc.errors.uncorrected.volatile.l2_cache",
//	"ecc.errors.uncorrected.volatile.texture_memory",
//	"ecc.errors.uncorrected.volatile.total",
//	"ecc.errors.uncorrected.aggregate.device_memory",
//	"ecc.errors.uncorrected.aggregate.register_file",
//	"ecc.errors.uncorrected.aggregate.l1_cache",
//	"ecc.errors.uncorrected.aggregate.l2_cache",
//	"ecc.errors.uncorrected.aggregate.texture_memory",
//	"ecc.errors.uncorrected.aggregate.total",
	"retired_pages.single_bit_ecc.count",
	"retired_pages.double_bit.count",
	"retired_pages.pending",
	"temperature.gpu",
	"power.management",
	"power.draw",
	"power.limit",
	"enforced.power.limit",
	"power.default_limit",
	"power.min_limit",
	"power.max_limit",
	"clocks.current.graphics",
	"clocks.current.sm",
	"clocks.current.memory",
	"clocks.current.video",
	"clocks.applications.graphics",
	"clocks.applications.memory",
	"clocks.default_applications.graphics",
	"clocks.default_applications.memory",
	"clocks.max.graphics",
	"clocks.max.sm",
	"clocks.max.memory",
);


// Generate command line and get data
$script = 'nvidia-smi --format=csv,noheader,nounits --query-gpu='.implode(',', $queries);
$output = trim(shell_exec($script));


// Converting data that return in CSV format to array
$data = explode("\n", $output);
foreach ($data as $key => $value):
	$row = explode(", ", $value);
	$gpu[] = array_combine($queries, $row);
endforeach;


// Return as JSON format only
if (isset($_GET['json'])):
	header('Content-Type: application/json');
	echo json_encode($gpu);
	exit();
endif;


// Return as a readable table (headless HTML)
echo '<pre>';
echo '<table>';
foreach ($queries as $key => $code):
	echo '<tr>';
	echo '<td>'.$code.'</td>';
	foreach ($gpu as $gpu_key => $gpu_value):
		echo '<td>'.$gpu_value[$code].'</td>';
	endforeach;
	echo '</tr>';
endforeach;
echo '</table>';


echo '<p><a href="https://github.com/eewartlu/nvidia-gpu-web-query-for-linux" target="_blank">Project: NVIDIA GPU Web Query for Linux</a></p>';
echo '<p>To get data in JSON format use '.$_SERVER['PHP_SELF'].'?json</p>';

echo '<p>For query code and explanation please check our <a href="https://github.com/eewartlu/nvidia-gpu-web-query-for-linux/wiki" target="_blank">Wiki</a> or run "nvidia-smi --help-query-gpu"</p>';
echo '</pre>';
?>
