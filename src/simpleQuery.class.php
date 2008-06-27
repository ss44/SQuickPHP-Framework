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
	
	public $tables = array();
	public $columns = array();
	public $fields = array();
	public $joins = array();
	public $wheres = array();
	
	public function addColumn($columnName){
		$this->columns[] = $columnName;
	}
	
	public function addColumns($columns){
		if (is_array($columns)){
			$this->columns = array_merge($this->columns, $columns);
		}else{
			$this->addColumn($columns);
		}
		
	}
	
	public function addField($fieldName, $value = ''){
		$pair = array();
		$pair['field'] = $fieldName;
		$pair['value'] = $value;
		
		$this->fields[] = $pair;
	}
	
	public function addFields( $fields ){
		if (is_array($fields)){
			foreach ($fields as $field=>$value){
				$pair = array();
				$pair['field'] = $field;
				$pair['value'] = $value;
				
				$this->fields[] = $pair;
			}
		}
	}
	
	public function addTable( $table ){
		$this->tables[] = $table;
	}
	
	public function addLeftJoin( $table, $onClause = ''){
		$pair = array();
		$pair['table'] = $table;
		$pair['on'] = $onClause;
		$pair['type'] = 'left';
		$this->joins[] = $pair;
	}
	
	public function addRightJoin( $table, $onClause){
		$pair = array();
		$pair['table'] = $table;
		$pair['on'] = $onClause;
		$pair['type'] = 'right';
		$this->rightJoins[] = $pair;
	}
	
	public function addJoin( $table, $onClause){
		$pair = array();
		$pair['table'] = $table;
		$pair['on'] = $onClause;
		$pair['type'] = 'inner';
		$this->rightJoins[] = $pair;
	}
	
	public function addWhere( $field, $value = '', $operator = '='){
		$pair = array();
		$pair['field'] = $field;
		$pair['value'] = $value;
		$pair['operator'] = $operator;
		$this->wheres[] = $pair;
	}
	
	public function getSelect(){
		$str = 'SELECT ';
		
		if (!$this->columns){
			$str .= '* ';
		}else{
			$str .= $this->prepareColumns();
		}
		
		if (!$this->tables){
			return false;
		}else{
			$str .= $this->prepareTables();
		}
		
		if ($this->wheres){
			$str .= $this->prepareWhere();
		}
		
		return $str;
	}
	
	public function getInsert(){}
	
	public function getUpdate(){}
	
	function prepareColumns(){
		return join(', ', $this->columns).' ';
	}
	
	function prepareWhere(){
		$str = ' WHERE ';
		
		$numberOfItems = count($this->wheres);
		$counter = 0;
		var_dump($numberOfItems);
		foreach ($this->wheres as $where){
			$str .= '('.$where['field'] . ' '. $where['operator'] . " '" . $where['value'] . "') ";
			$counter++;
			if ($counter != $numberOfItems){
				$str .= ' AND ';		
			}
		}
		
		return $str;
	}
	
	function prepareTables(){
		return 'FROM '.join(', ', $this->tables);
	}
	
	function prepareJoins(){}
	
	function prepareFields(){}
}
?>
