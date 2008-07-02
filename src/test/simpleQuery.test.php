<?php
/**
 * Test page to test functionality of simpleQuery.
 */
require_once('../simpleQuery.class.php');
require_once('../tester.class.php');
require_once('../functions.php');
$tests = new Tester(); 


echo "\n";
/*
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

$expected = "SELECT a, DISTINCT(b), COUNT(c) FROM test, test2 WHERE a=5 AND b='This\'s a String'";
$tests->addTest($expected, $q->getSelect());

$q = new SimpleQuery();
$q->addTable('test');
$q->addLeftJoin('test2', 'a.a = b.a');
$q->addColumn('a');
$expected = "SELECT a FROM test LEFT JOIN test2 ON (a.a = b.a)";
$tests->addTest($expected, $q->getSelect());

$q = new SimpleQuery();
$q->addTable('test');
$q->addLeftJoin('test2', 'a.a = b.a');
$q->addRightJoin('test3', 'c.a = d.a');
$q->addColumn('a');
$expected = "SELECT a FROM test LEFT JOIN test2 ON (a.a = b.a) RIGHT JOIN test3 ON (c.a = d.a)";
$tests->addTest($expected, $q->getSelect());

$q->addWhere('b', 5);
$q->addWhere('a', 3);
$expected = "SELECT a FROM test LEFT JOIN test2 ON (a.a = b.a) RIGHT JOIN test3 ON (c.a = d.a) WHERE b=5 AND a=3";
$tests->addTest($expected, $q->getSelect());

$q = new SimpleQuery();
$q->addWhere('a', 5);
$q->addTable('test');
$q->addGroup('c');
$q->addGroup('d');
$q->addGroup('e');
$q->addColumn('a');
$expected = "SELECT a FROM test WHERE a=5 GROUP BY c, d, e";
$tests->addTest($expected, $q->getSelect());

$q = new SimpleQuery();
$q->addTable('test');
$q->addWhere('a', 5);
$q->addGroup('c');
$q->addHaving('a', 5);
$q->addHaving('b', 6);
$q->addColumn('a');
$expected = "SELECT a FROM test WHERE a=5 GROUP BY c HAVING a=5 AND b=6";
$tests->addTest($expected, $q->getSelect());
*/
$q = new SimpleQuery();
$q->addTable('test');
$q->addColumn('a');
$q->startWhereGroup('OR');
$q->addWhere('a', 5);
$q->addWhere('b', 6);
$q->endWhereGroup();
$q->startWhereGroup('OR');
$q->addWhere('c',8);
$q->addWhere('d', 9);
$q->endWhereGroup();
$expected = "SELECT a FROM test WHERE (a=5 OR b=6) AND (c=8 OR d=9)";
$tests->addTest($expected, $q->getSelect());

$expected = "SELECT a FROM test WHERE (a=5 AND c=6) OR (b=7 OR f=10)";
$tests->addTest($expected, $q->getSelect());

$q = new simpleQuery();
$q->addTable('test');
$q->addWhere('a', array(5, 1, 4, 7));
$q->addColumn('a');
$expected = "SELECT a FROM test WHERE a IN ('5', '1', '4', '7')";
$tests->addTest($expected, $q->getSelect());


$q = new simpleQuery();
$q->addTable('test');
$q1 = new simpleQuery();
$q1->addTable('b');
$q1->addColumn('a');
$q->addWhere('a', $q1);
$q->addColumn('a');
$expected = "SELECT a FROM test WHERE a IN (SELECT a FROM b)";
$tests->addTest($expected, $q->getSelect());


$tests->run();

var_dump( getType($q));
var_dump( getType('5'));
var_dump( getType(5));
var_dump( is_numeric('5'));
?>
