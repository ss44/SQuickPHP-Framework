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
	public $groups = array(); 
	public $havings = array(); 
	public $whereGroups = array();
	public $whereGroupCounter = 0;
	
	public function __construct(){
		$this->whereGroups[0] = 'AND';
	}
	
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
		$this->joins[] = $pair;
	}
	
	public function addJoin( $table, $onClause){
		$pair = array();
		$pair['table'] = $table;
		$pair['on'] = $onClause;
		$pair['type'] = 'inner';
		$this->joins[] = $pair;
	}
	
	public function addWhere( $field, $value = '', $operator = '='){
		$pair = array();
		$pair['field'] = $field;
		$pair['value'] = $value;
		$pair['operator'] = $operator;
		$pair['group'] = $this->whereGroupCounter;
		$this->wheres[] = $pair;
	}
	
	public function startWhereGroup( $type = 'OR' ){
		$this->whereGroupCounter++;
		$this->whereGroups[$this->whereGroupCounter] = $type;
	}
	
	public function endWhereGroup(){
		$this->whereGroupCounter = $this->whereGroupCounter == 0 ? 0 : $this->whereGroupCounter-1;
	}
	
	public function addGroup($group){
		$this->groups[] = $group;
	}
	
	public function addGroups($groups){}
	
	public function addHaving($field, $value, $operator = '='){
		$pair = array();
		$pair['field'] = $field;
		$pair['value'] = $value;
		$pair['operator'] = $operator;
		$this->havings[] = $pair;
	}
	
	public function addHavings($fields){}
	
	public function addOrderBy($field){}
	
	public function getSelect(){
		$str = 'SELECT ';
		
		if (!$this->columns) $str .= '* ';
		else $str .= $this->prepareColumns();
		
		if (!$this->tables) return false;
		else $str .= $this->prepareTables();
		
		if ($this->joins) $str .= ' '.$this->prepareJoins();

		if ($this->wheres) $str .= ' '.$this->prepareWhere();
		
		if ($this->groups) $str .= ' '.$this->prepareGroup();
		if ($this->havings) $str .= ' '.$this->prepareHaving();
		return trim($str);
	}
	
	function getInsert(){}
	
	function getUpdate(){}
	
	function prepareColumns(){
		return join(', ', $this->columns).' ';
	}
	
	function prepareWhere(){
		$str = 'WHERE ';
		$boolType = 'AND';
		$numberOfItems = count($this->wheres);
		$counter = 0;
		$currentGroup = 0;
		print_r($this->wheres);
		foreach ($this->wheres as $where){
			if ($where['group'] > $currentGroup){
				$str .= '(';
				$boolType = $this->whereGroups[ $where['group'] ];
				$currentGroup = $where['group'];
			}elseif($where['group'] < $currentGroup){
				$str .= ')';
				$boolType = $this->whereGroups[ $where['group']]; 
				$currentGroup = $where['group'];
			}
			if (is_numeric($where['value'])) $str .= $where['field'] . $where['operator'] . $where['value'];
			elseif (is_array($where['value'])){
				//array_walk($where, 'mysql_escape_string');
				//TODO do a type check using typeof() to determine if is a real int instead checking if is numeric
				$str .= $where['field'] . ' IN (\'' . implode("', '", $where['value'] ) . '\')';
			}
			elseif (is_a($where['value'], SimpleQuery)){
				$obj = $where['value'];
				$str .= $where['field'] . ' IN (' . $where['value']->getSelect(). ')';
			}
			else $str .= $where['field'] . $where['operator'] . "'" . mysql_escape_string($where['value'])."'";
			$counter++;
			if ($counter != $numberOfItems){
				$str .= " $boolType ";		
			}
		}
		
		return $str;
	}
	
	function prepareTables(){
		return 'FROM '.join(', ', $this->tables);
	}
	
	function prepareJoins(){
		$str = '';

		foreach ($this->joins as $join){
			switch ($join['type']){
				case 'left':
					$str .= 'LEFT ';
					break;
				case 'right':
					$str .= 'RIGHT ';
					break;
				case 'outer':
					$str .= 'OUTER ';
					break;
				case 'inner':
				case 'default':
					$str .= 'INNER ';
					break;
			}
			
			$str .= 'JOIN '. $join['table']. ' ON (' .$join['on'] . ') ';

		}
		
		return trim($str);
	}
	
	function prepareGroup(){
		return 'GROUP BY '. join(', ', $this->groups);
		
	}
	
	function prepareHaving(){
		$str = 'HAVING ';
		$numberOfItems = count($this->havings);
		$counter = 0;
		
		foreach ($this->havings as $having){
			$str .= $having['field'] . $having['operator'] . (is_numeric($having['value']) ? $having['value'] : "'" . mysql_escape_string($having['value']) . "'") ;
			$counter ++;
			if ($numberOfItems != $counter) $str .= ' AND ';
		}
		return $str;
	}
	function prepareFields(){}
}
?>
