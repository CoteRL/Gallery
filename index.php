<?php
###################
$pageMax = 25;    #max images per page
###################

#Check if configuration file exists and require it if so.  If not, die
if(!file_exists('config.php')) {
	die('Cannot locate configuration file');
}
else {
	require('config.php');
}
#TinyButStrong template engine
include_once('tbs_class.php');
$TBS = new clsTinyButStrong;


$home = substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-10); 					#.com/somedir/gallery/index.php TO /somedir/gallery
$homePath = substr($_SERVER['SCRIPT_FILENAME'],0,strlen($_SERVER['SCRIPT_FILENAME'])-10); 	#/var/www/localhost/htdocs/somedir/gallery/index.php TO /var/www/localhost/htdocs/somedir/gallery

$request = urldecode(preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']));					#/somedir/gallery/Hello%20World/?v=1 TO /somedir/gallery/Hello World/

$dirRequest = substr($request,strlen($home),strlen($request));								#/somedir/gallery/Hello World/ TO /Hello World/

$picPath 	= $homePath.$_CFG['PIC_DIR'];													#/var/www/localhost/htdocs/somedir/gallery/pics
$imageDir 	= $home.$_CFG['PIC_DIR'].$dirRequest;											#/somedir/gallery/pics

$workingDir = $picPath.$dirRequest;															#/var/www/localhost/htdocs/somedir/gallery/pics/Hello World/

#Only do the stuff if the directory actually exists
if(file_exists($workingDir)) {
	$directories = array();
	$images = array();
	$videos = array();

	$dirScan = new DirectoryIterator($workingDir);
	$d = 0;
	$i = 0;
	$v = 0;
	foreach ($dirScan as $fileInfo) {
		if ($fileInfo->isDot()) continue;
		if ($fileInfo->isDir()) {
			$directories[$d]['name'] 		= $fileInfo->getFilename();
			$directories[$d]['path'] 		= $request.$fileInfo->getFilename().'/';
			
			$lastMod = 0;
			$lastModFile = '';
			$subDir = $workingDir.$fileInfo->getFilename();
			$subScan = new DirectoryIterator($subDir);
			foreach ($subScan as $subInfo) {
				if ($subInfo->isDot()) continue;
				if ($subInfo->isDir()) continue;
				if ($subInfo->isFile()) {
					if (in_array(strtolower($subInfo->getExtension()), $_CFG['SUPPORTED_PICS']) && filectime($subInfo->getPathname()) > $lastMod) {
						$lastMod = filectime($subInfo->getPathname());
						#$lastModFile = $subInfo->getFilename();
						$lastModFileThumb = $home.$_CFG['THUMB_DIR'].substr($subInfo->getPathname(),strlen($homePath.$_CFG['PIC_DIR']),strlen($subInfo->getPathname()));
					}
				}
			}
			if(!$lastModFileThumb) {
				$directories[$d]['thumb']	= $home.$_CFG['NO_THUMB'];
			}
			else {
				$directories[$d]['thumb']	= $lastModFileThumb;
			}
			$lastModFileThumb = NULL;
			$d++;
			continue;
		}
		if ($fileInfo->isFile()) {
			if (in_array(strtolower($fileInfo->getExtension()), $_CFG['SUPPORTED_PICS'])) {
				$images[$i]['name'] 	= $fileInfo->getFilename();
				$images[$i]['path'] 	= $imageDir.$fileInfo->getFilename();
				$images[$i]['thumb'] 	= $home.$_CFG['THUMB_DIR'].$dirRequest.$fileInfo->getFilename();
				$i++;
				continue;
			}
			if (in_array(strtolower($fileInfo->getExtension()), $_CFG['SUPPORTED_VIDS'])) {
				$videos[$v]['name']		= $fileInfo->getFilename();
				$v++;
				continue;
			}
		}
	}
	asort($directories);
	asort($images);
	#asort($videos);
	
	
	if (!isset($_GET['page'])) {
		$page=1;
	}
	else {
		$page = $_GET['page'];
	}
	$pages = ceil(count($images) / 25);
	$pagination = array();
	$p = 1;
	do {
		$pagination[] = $p;
		$p++;
	} while ($p <= $pages);

	$offset = ($page - 1) * $pageMax;
	$images = array_slice($images, $offset, $pageMax);
	
	#Create the nav breadcrumbs
	$bread_crumb = null;
	if ($request != $home && $request != $home.'/') {			
		$home2 = str_replace('/','',$home);
		if ( preg_match_all(';/([^/]*);', $request, $regs) ) {
			for ($i=0; $i<count($regs[0]); $i++) {
				if ($regs[1][$i] != $home2 && $regs[1][$i] != '') {
					$name = $regs[1][$i];
					$href = '';
					
					for ($j=0; $j<$i+1; $j++) {
						$href .= $regs[0][$j];
					}
					$bread_crumb[$i]['path'] = $href.'/';
					$bread_crumb[$i]['name'] = $name;
				}
			}
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
	
	if (is_null($images)) $images = array();
	$TBS->MergeBlock('images', $images);
	
	if (is_null($videos)) $videos = array();
	$TBS->MergeBlock('videos', $videos);
	
	if (is_null($pagination)) $pagination = array();
	$TBS->MergeBlock('pagination', $pagination);
	
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
?>