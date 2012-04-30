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
	
	$working_dir = $FULL_PATH.$request;
	$blank_thumb = $HOME.'/includes/folder.png';
	
	#Only do the stuff if the directory actually exists
	if(file_exists($working_dir)) {
		#Scan the directory for all files and directories but remove server configuration files and some other unwanted entries
		#probably a better way to do this but i'm stupid
		$results = array_diff(scandir($working_dir), $IGNORE);

		$directories = null;
		$files = null;
		
		#Go through the full results and split the entries into two arrays; files and directories
		foreach($results as $i => $result) {
			if(is_dir($working_dir.$result)) {

				
				#Find the latest added picture to use as a thumbnail for the folder
				$last_mod = 0;
				$last_mod_file = '';
				$scan = $FULL_PATH.$request.$result.'/';
				foreach (scandir($scan) as $entry) {
					if (is_file($scan.$entry) && filectime($scan.$entry) > $last_mod) {
						$last_mod = filectime($scan.$entry);
						$last_mod_file = $entry;
					}
				}
				
				$directories[$i]['name'] 		= $result;
				$directories[$i]['path'] 		= $request.$result;
				
				if(!$last_mod_file) {
					$directories[$i]['thumb'] 		= 'No Thumb';
					$directories[$i]['thumb_path']	= $HOME.'/includes/folder.png';
				}
				else {
					$directories[$i]['thumb'] 		= $last_mod_file;
					$directories[$i]['thumb_path']	= $request.$result.'/'.$last_mod_file;
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
		$bread_crumb = null;
		if($request != $HOME && $request != $HOME.'/') {
			#Create array of all the bread_crumbs
			$bread_crumbs = array_diff(explode('/',$request), array(str_replace('/','',$HOME)));
			#Remove any empty entries before the first / and after the last
			$bread_crumbs = array_filter($bread_crumbs, 'strlen');
			
			foreach($bread_crumbs as $i => $crumb) {
				#Find the current crumb in the full request uri to generate a link for it
				##Fix the regex so it matches after the word instead of including the word, then remove .$crumb from the end of the next line... tard
				preg_match("/$crumb.*/",$request,$match);
				$bread_crumb[$i]['path'] = str_replace($match[0],'',$request).$crumb;
				
				$bread_crumb[$i]['name'] = $crumb;

			}
		}
		
		
		#Load specific template
		$TBS->LoadTemplate('templates/main.htm');
		
		#Specify some page specific variables
		$page_title = '';
	
		if (is_null($bread_crumb)) $bread_crumb = array();
		$TBS->MergeBlock('bread', $bread_crumb);
		
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