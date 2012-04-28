<?php
#Check if configuration file exists and require it if so.  If not, die
if(!file_exists('includes/config.php')) {
	die('Cannot locate configuration file');
}
else {
	require('includes/config.php');
}
#TinyButStrong template engine
include_once('includes/tbs_class.php');
$TBS = new clsTinyButStrong;

##
#Configuration values needed from config.php
#$filepath
#
##


#If it's a straight directory grab do the directory searching stuff
if (!$_SERVER['QUERY_STRING']) {

	##Grab and convert html characters from the uri requested minus any set variables
	##/test/Hello%20World/?v=1 to '/test/Hello World/
	$request = urldecode(preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']));
	
	$workingdir = $fullpath.$request;
	
	#Only do the stuff if the directory actually exists
	if(file_exists($workingdir)) {
		#Scan the directory for all files and directories but remove server configuration files and some other unwanted entries
		#probably a better way to do this but i'm stupid
		$results = array_diff(scandir($workingdir), array('..', '.','.htaccess','index.php','includes','templates'));

		$directories = null;
		$files = null;
		
		#Go through the full results and split the entries into two arrays; files and directories
		foreach($results as $result) {
			if(is_dir($workingdir.$result)) {
				$directories[] = $result;
			}
			else {
				$files[] = $result;
			}
		}
		
		$TBS->LoadTemplate('templates/main.htm');
		if($directories) $TBS->MergeBlock('dirs', $directories);
		if($files) $TBS->MergeBlock('files', $files);
		$TBS->Show();
		
/* 		print_r($directories);
		echo "<br />";
		print_r($files); */
		
	}
	else {
		echo "Directory doesn't exist";
	}
}
#If there IS a query then parse it
else {
#tags, etc to go here
}

?>