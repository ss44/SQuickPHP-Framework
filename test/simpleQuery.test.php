<?php
/**
 * Test page to test functionality of simpleQuery.
 */
require_once('../simpleQuery.class.php');
require_once('../tester.class.php');
require_once('../functions.php');
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

$q = new simpleQuery();
$q->addTable('t');
$q->addTable('t2');
$q->addColumn('a');
$q->addColumn('b');
$q->addWhere('t.c=t2.c');
$expected = "SELECT a, b FROM t, t2 WHERE t.c=t2.c";
$tests->addTest($expected, $q->getSelect());

//Test Insert Commands
$q = new simpleQuery();
$q->addTable('table');
$q->addField('a', 1);
$q->addField('b', 2);
$q->addField('c', 'test');
$q->addField('d', 'testes');
$expected = "INSERT INTO table(`a`, `b`, `c`, `d`) VALUES(1, 2, 'test', 'testes')";
$tests->addTest($expected, $q->getInsert());

$exepected = "INSERT INTO table(`a`, `b`) VALUES('test11', 5)";
$tests->addTest($expected, $q->getInsert());

//Update commands
$q = new simpleQuery();
$q->addTable('table');
$q->addField('a', 'test');
$q->addField('b', 3);
$expected = "UPDATE table SET a='test', b=3";
$tests->addTest($expected, $q->getUpdate());

$q = new simpleQuery();
$q->addTable('table1');
$q->addTable('table2');
$q->addField('a', 'test');
$q->addField('b', 3);
$q->addField('c', 'testes');
$expected = "UPDATE table1, table2 SET a='test', b=3, c='testes'";
$tests->addTest($expected, $q->getUpdate());

$q = new simpleQuery();
$q->addTable('t1');
$q->addTable('t2');
$q->addTable('t3');
$q->addField('t1.a','test');
$q->addField('t2.b', 3);
$q->addField('t3.c', 'data');
$q->addWhere('t1.b=t2.a');
$q->addWhere('t2.c=t3.a');
$expected = "UPDATE t1, t2, t3 SET t1.a='test', t2.b=3, t3.c='data' WHERE t1.b=t2.a AND t2.c=t3.a";
$tests->addTest($expected, $q->getUpdate());

$q = new simpleQuery();
$q->addTable('table');
$q->addField('a', 'test');
$q->addField('b', 3);
$expected = "UPDATE table SET a='test', b=3";
$tests->addTest($expected, $q->getUpdate());

$tests->run();


?>
