<?php
/**
 * Test cases for simpleDB
 * @author Shajinder Padda <shajinder@gmail.com>
 * @created 14-Dec-2008.
 */

require_once('../simpleDB.class.php');
require_once('../simpleQuery.class.php');
require_once('../functions.php');
//mysql database test cases:
$db = new SimpleDB( array('type'=>'mysql', 'path'=>'localhost:3306', 'name'=>'world', 'user'=>'shinda', 'pass'=>'ziggy'));

$q = new SimpleQuery();
$q->addTable('City');

//1. Get Row
//oops ( $db->getRow($q) );

//2. Get All
//oops( $db->getAll($q) );

//3. Get Assoc with value
//oops( $db->getAssoc($q, 'CountryCode', 'Name'));

//4. Get Assoc without value.
//oops ($db->getAssoc($q, 'CountryCode'));
?>