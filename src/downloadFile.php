<?php

/** Authors: Jon Scherdin, Andrew Poppe */

# verify user access
if (!isset($_COOKIE['grant_repo'])) {
	header("Location: index.php");
}

require_once("base.php");

$dieMssg = "Improper filename ".APP_PATH_TEMP.$_GET['f'];
if (!isset($_GET['f']) || preg_match("/\.\./", $_GET['f']) || preg_match("/^\//", $_GET['f'])) {
	die($dieMssg);
}
$filename = APP_PATH_TEMP.$_GET['f'];
if (!file_exists($filename)) {
	die($dieMssg);
}

// JUST DOWNLOAD THE ORIGINAL FILE
displayFile($filename);

function displayFile($filename) {
	header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    header('Content-Type: application/octet-stream');
	header("Pragma: no-cache");
	header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
	header('Content-Length: ' . filesize($filename));
    
	//Clear system output buffer
	flush();

    // read the file from disk
    readfile($filename);
	//Terminate from the script
	die();
}
