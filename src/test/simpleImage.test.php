<?php
require_once('../simpleImage.class.php');
require_once('../functions.php');

for($x = 2; $x < 1000; $x = $x + 30){
	$image = new simpleImageGD2('1.jpg');
	$image->resizeImage($x, $x);
	$image->saveImage("test_$x.jpg");

	echo "<img src='test_$x.jpg' /><br />";
}
?>