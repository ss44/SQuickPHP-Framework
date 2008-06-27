<?php
/**
 * Test page to test functionality of simpleQuery.
 */
require_once('../simpleQuery.class.php');
require_once('../tester.class.php');
$tests = new Tester(); 


echo "\n";
//1. Test Building SELECT statements
$q = new SimpleQuery();
$q->addTable('test');
$expected = "SELECT * FROM test";
$tests->addTest($expected, $q->getSelect());

$q = new SimpleQuery();
$q->addTable('test');
$q->addColumn('a');
$q->addColumn('b');
$q->addColumn('c');

$expected = "SELECT a, b, c FROM test";
$tests->addTest($expected, $q->getSelect());

$q = new SimpleQuery();
$q->addTable('test');
$q->addTable('test2');
$q->addColumns ( array('a', 'DISTINCT(b)', 'COUNT(c)'));
$q->addWhere('a', 5);
$q->addWhere('b', "This's a String");

$expected = "SELECT a, DISTINCT(b), COUNT(c) FROM test, test2 WHERE (a = 5) AND (b = 'This\'s a String')";
$tests->addTest($expected, $q->getSelect());

$tests->run();
?>
