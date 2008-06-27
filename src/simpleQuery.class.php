<?php
/**
 * Assists in building general sql queries in an organized manner.
 * No more messing with 20 line strings, simply use this to build a query in a simple
 * organzing and readable method, without loosing any flexiblity of raw sql.
 * 
 * @author Shajinder Padda <shajinder@gmail.com>
 * @created 26-June-2008
 */

class SimpleQuery{
	class Where{
		public $field = '';
		public $value = '';
		public $operator = '';
	}
	
	public function addColumn($columnName){}
	
	public function addColumns($columns){}
	
	public function addField($fieldName, $value){}
	
	public function addFields( $fields ){}
	
	public function addTable( $table ){}
	
	public function addLeftJoin( $table, $onClause){}
	
	public function addRightJoin( $table, $onClause){}
	
	public function addJoin( $table, $onClause){}
	
	public function addWhere( $field, $value = '', $operator = ''){}
	
	public function getSelect(){}
	
	public function getInsert(){}
	
	public function getUpdate(){}
	
}
?>
