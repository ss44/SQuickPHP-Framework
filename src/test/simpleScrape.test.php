<?php
require_once('../simpleScrape.php');
require_once('../tester.class.php');
require_once('../functions.php');

$expected = "passed";
$url = "http://scode.127.ca/test/simpleScrape.test2.php?test=";
$tests = new Tester(); 


$s = new simpleScrape( $url.'1');
$tests->addTest( $expected, $s->getPage());

$s = new simpleScrape( $url.'2', 'POST');
$tests->addTest( $expected, $s->getPage());

echo "<pre>";
$tests->run();
echo "</pre>";
?>