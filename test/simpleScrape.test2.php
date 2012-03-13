<?php
require_once('simpleCode.php');

$testLevel = isset($_GET['test']) ? $_GET['test'] : 1;
$passedTest = false; 
switch ($testLevel){
	

	/**
	 * Page should only be accessed if user is running firefox browser.
	 */
	case 1:
		if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/firefox/i', $_SERVER['HTTP_USER_AGENT'])) $passedTest = true;		
		break;
	//Page should only be accessed if user passes a POST
	case 2:
		if (isset($_POST['code']) && $_POST['code'] == 5) $passedTest = true;
		break;
}

if (!$passedTest) echo "bot";
else echo "passed";
?>