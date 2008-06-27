<?php
/**
 * Test page to test functionality of simpleQuery.
 */
require_once('../simpleQuery.class.php');

$tests = array();
$q = new SimpleQuery();

echo "\n";
//1. Test Building SELECT statements

$expected = "SELECT * FROM test";
$tests[] = $q->getSelect() == $expected;


$expected = "SELECT a, b, c FROM test";
$tests[] = $q->getSelect() == $expected;


$expected = "SELECT a, DISTINCT(b), COUNT(c) FROM test, test2 WHERE (a = 5) AND (b = 'This\'s a String')";
$tests[] = $q->getSelect() == $expected;

foreach ($tests as $testNo=>$test){
	if (!$test){
		echo 'Failed Test: '. ($testNo+1) . "\n";
		$failed++;
	}	
}
echo $failed > 0 ? "Failed $failed tests\n\n" : "Passed with flying colors\n\n";
?>