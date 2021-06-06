#!/usr/bin/php
<?php
// This script runs a snapshot of arg1 (arg1 being a VM name listed in 'virsh --list all') and pops the parent snapshot into the backup dir set below (usually /mnt/backups in our case)

// Grab the first argument from the command line
$virtualMachine = newVM;

// Check for an argument passed on the commmand line
if (empty($virtualMachine)) {
    echo "You need to specify the name of a virtual machine or this script will not know what you want to backup \n";
    exit();
}

// Let the terminal know which VM we're merging

echo "\n OK, I'm merging backing files for $virtualMachine for you...\n \n";

$commandOutput = shell_exec("virsh -r domblklist $virtualMachine | awk '/^[shv]d[a-z][[:space:]]+/ {print $2}'");

echo "Found the following block devices: \n $commandOutput \n";

// Turn the list of block devices into an array

$blockDevices = explode("\n", $commandOutput);

// Clean up the array and remove any blanks - we don't want these!

$blockDevices = array_filter($blockDevices);

// Remove any lines in the array that are a '-' as these are basically a drive (say a cd-rom) that doesn't have any media in it

$filter = "-";

$blockDevices = array_filter($blockDevices, function ($element) use ($filter) { return ($element != $filter); } ); 

foreach ($blockDevices as $blockDevice) {
	// Blockpull each block device and wait until it's complete
	$blockPull = shell_exec("virsh blockpull $virtualMachine $blockDevice --verbose --wait");

	// Strip out the new lines in the name - these cause issues with later shell commands
	$blockPull = str_replace(array("\n", "\r"), ' ', $blockPull);

	echo "$blockPull";

	// Get the dirname of the file - we use this to construct the command that deletes the now unecessary backing files (scary!)
	$dirName = dirname("$blockDevice");
	$baseName = basename("$blockDevice");
	$info = pathinfo($blockDevice);
	$fileName =  basename($blockDevice,'.'.$info['extension']);
	echo "dirname = $dirName \n";
	echo "baseName = $baseName\n";
	echo "filename = $fileName\n";
	$deleteBacking = shell_exec("\n\n\n\nfind $dirName -type f ! -name $baseName -name $fileName\\* -exec rm -rf {} \;\n\n\n\n");
	echo "OK, I'm all done and have deleted the old backing files for you - check that if everything works please!\n";
}

?>