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
	$blankthumb = $home.'/includes/folder.png';
	
	#Only do the stuff if the directory actually exists
	if(file_exists($workingdir)) {
		#Scan the directory for all files and directories but remove server configuration files and some other unwanted entries
		#probably a better way to do this but i'm stupid
		$results = array_diff(scandir($workingdir), array('..', '.','.htaccess','index.php','includes','templates','README.md','.git'));

		$directories = null;
		$files = null;
		
		#Go through the full results and split the entries into two arrays; files and directories
		foreach($results as $i => $result) {
			if(is_dir($workingdir.$result)) {

				
				#Find the latest added picture to use as a thumbnail for the folder
				$lastMod = 0;
				$lastModFile = '';
				$scan = $fullpath.$request.$result.'/';
				foreach (scandir($scan) as $entry) {
					if (is_file($scan.$entry) && filectime($scan.$entry) > $lastMod) {
						$lastMod = filectime($scan.$entry);
						$lastModFile = $entry;
					}
				}
				
				$directories[$i]['name'] 		= $result;
				$directories[$i]['path'] 		= $request.$result;
				
				if(!$lastModFile) {
					$directories[$i]['thumb'] 		= 'No Thumb';
					$directories[$i]['thumb_path']	= $home.'/includes/folder.png';
				}
				else {
					$directories[$i]['thumb'] 		= $lastModFile;
					$directories[$i]['thumb_path']	= $request.$result.'/'.$lastModFile;
				}
				
			}
			else {
				$files[] = $result;
			}
		}
		
		#GO THROUGH FILES AND ONLY GRAB SUPPORTED FILE TYPES
		#if(!empty($files)){
		#	foreach($files as $file) {
		#		
		#	}
		#}

		#Create the nav breadcrumbs
		$breadcrumb = null;
		if($request != $home && $request != $home.'/') {
			$breadcrumbs = array_diff(explode('/',$request), array(str_replace('/','',$home)));
			$breadcrumbs = array_filter($breadcrumbs, 'strlen');
			
			foreach($breadcrumbs as $i => $crumb) {
				$breadcrumb[$i]['name'] = $crumb;
				$breadcrumb[$i]['path'] = 'fuckall';
			}
		}
		
		
		#Load specific template
		$TBS->LoadTemplate('templates/main.htm');
		
		#Specify some page specific variables
		$page_title = '';
	
		if (is_null($breadcrumb)) $breadcrumb = array();
		$TBS->MergeBlock('bread', $breadcrumb);
		
		if (is_null($directories)) $directories = array();
		$TBS->MergeBlock('dirs', $directories);
		
		if (is_null($files)) $files = array();
		$TBS->MergeBlock('files', $files);
		
		$TBS->Show();
		
		
	}
	else {
		#Load specific template
		$TBS->LoadTemplate('templates/error.htm');
		
		#Specify some page specific variables
		$page_title = 'Oops!';
		$error = 'Sorry, that directory does not exist!';
		
		$TBS->Show();
	}
}
#If there IS a query then parse it
else {
#tags, etc to go here
}

?>