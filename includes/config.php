<?php

#Set the full directory to the gallery root
$FULL_PATH = '/var/www/localhost/dev';

#Set the name of the root gallery folder
$HOME = '/gallery';

#Supported picture file formats
$SUPPORTED_PICS = array('jpg','jpeg','png','gif');

#Enter your Google Analytics account and domain.  If analytics isn't needed, leave both blank
$ANALYTIC_ACCOUNT = '';
$ANALYTIC_DOMAIN = '';

#Main page title
$MAIN_TITLE = 'Gallery';

#Files and folders to ignore
$IGNORE[0] = '.';
$IGNORE[1] = '..';
$IGNORE[2] = '.htaccess';
$IGNORE[3] = 'index.php';
$IGNORE[4] = 'includes';
$IGNORE[5] = 'templates';
$IGNORE[6] = 'README.md';
$IGNORE[7] = '.git';

#array('..', '.','.htaccess','index.php','includes','templates','README.md','.git')
?>