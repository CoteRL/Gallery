<?php
#Check if configuration file exists and require it if so.  If not, die
if(!file_exists('config.php')) {
	die('Cannot locate configuration file');
}
else {
	require('config.php');
}

##Grab and convert html characters from the uri requested minus any set variables
##/gallery/thumb/Hello%20World/?v=1 to '/gallery/thumb/Hello World/
$request = urldecode(preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']));
##Strip $HOME & $THUMB_DIR from the beginning of the request
if (substr($request, 0, strlen("$HOME$THUMB_DIR")) == "$HOME$THUMB_DIR") {
	$picRequest = substr($request, strlen("$HOME$THUMB_DIR"), strlen($request) );
}
#Absolute path to actual picture
$picFile = $WEB_ROOT.$HOME.$PIC_DIR.$picRequest;

#Check to make sure the picture exists
if (file_exists($picFile)) {
	$thumbFile = $WEB_ROOT.$HOME.$THUMB_DIR.$picRequest;
	#Strip down the request to just an absolute directory path so we can create it if not present.
	$thumbDir = preg_replace(';/([^/]*)$;', '', "$WEB_ROOT$HOME$THUMB_DIR$picRequest");
	
	if(!is_dir($thumbDir)){
		mkdir($thumbDir, 0755, true);
	}
	$size = getimagesize($picFile);
	$width = $size[0];
	$height = $size[1];
	$type = $size[2];
	
	#1 = GIF, 2 = JPG, 3 = PNG
	switch ($type) {
		case 1:
			$img = imagecreatefromgif ($picFile);
			break;
		case 2:
			$img = imagecreatefromjpeg ($picFile);
			break;
		case 3:
			$img = imagecreatefrompng ($picFile);
			break;
		default:
			$img = imagecreatetruecolor (100,100);
			break;
	}
	$cache_x = 100;
	$cache_y = 100;
	$thumb = imagecreatetruecolor($cache_x, $cache_y);
	imagecopyresampled($thumb, $img, 0, 0, 0, 0, $cache_x, $cache_y, $width, $height);
	imagedestroy($img);

	ob_start();
	switch ($type) {
		case 1:
			imagegif($thumb);
			break;
		case 2:
			imagejpeg($thumb);
			break;
		case 3:
			imagepng($thumb);
			break;
		default:
			imagejpeg($thumb);
			break;
	}
	$cache_image = ob_get_contents();
	ob_end_clean();
	imagedestroy($thumb);

	file_put_contents($thumbFile, $cache_image);

	switch ($type) {
		case 1:
			header ('Content-Type: image/gif');
			break;
		case 2:
			header ('Content-Type: image/jpeg');
			break;
		case 3:
			header ('Content-Type: image/png');
			break;
		default:
			header ('Content-Type: image/jpeg');
			break;
	}
	echo $cache_image;
}
else {
	echo "<br>The file $picFile doesn't exist";
}
?>
