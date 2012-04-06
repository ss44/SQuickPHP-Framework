<?php
/**
 * Assists in building general sql queries in an organized manner.
 * No more messing with 20 line strings, simply use this to build a query in a simple
 * organzing and readable method, without loosing any flexiblity of raw sql.
 *
 * @author Shajinder Padda <shajinder@gmail.com>
 * @created 26-June-2008
 */

class SQuickQuery{

	public $tables = array();
	public $columns = array();
	public $fields = array();
	public $joins = array();
	public $wheres = array();
	public $groups = array();
	public $havings = array();
	public $orders = array();
	public $whereGroups = array();
	public $whereGroupCounter = 0;
	protected $limit = null;
	protected $offset = null;
	protected $_dbType = null;

	protected $_query = null;

	public function __construct( $query = null ){
		$this->whereGroups[0] = 'AND';
		$this->_query = $query;
	}

	public function setDBType( $dbType ){
		$this->_dbType = $dbType;

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

	public function addField($fieldName, $value = '', $escape = true){
		$pair = array();
		$pair['field'] = $fieldName;
		$pair['value'] = $value;
		$pair['escape'] = (bool) $escape;
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

	/**
	 * The order by fields to add to the select statement.
	 *
	 * @param String $field Field you want to sort by.
	 * @param Direction $direction  Direction to sort by, should be either ASC or DESC.
	 */
	public function addOrder($field, $direction = "ASC"){
		$order = arraY();
		$order['field'] = $field;
		$order['direction'] = $direction;
		$this->orders[] = $order;
	}

	public function addLimit( $value ){
		$this->limit = (is_int($value)) ? $value : null;
	}

	public function addOffset( $value ){
		$this->offset = is_int($value) ? $value : null;
	}
	
	public function getSelect(){

		if ($this->_query) return $this->_query;

		$str = 'SELECT ';

		if (!$this->columns) $str .= '* ';
		else $str .= $this->prepareColumns();

		if (!$this->tables) return false;
		else $str .= $this->prepareTables();

		if ($this->joins) $str .= ' '.$this->prepareJoins();

		if ($this->wheres) $str .= ' '.$this->prepareWhere();

		if ($this->groups) $str .= ' '.$this->prepareGroup();
		if ($this->havings) $str .= ' '.$this->prepareHaving();
		if ($this->orders) $str .= ' ' . $this->prepareOrders();

		if (!is_null($this->limit)) $str .= ' LIMIT '.$this->limit;
		if (!is_null($this->offset)) $str .= ' OFFSET '. $this->offset;
		 
		return trim($str);
	}

	public function getInsert(){
		if (!$this->tables) throw new Exception('Must set a table first.');
		if (!$this->fields) throw new Exception('No fields set');

		if ($this->_query) return $this->_query;


		$str = 'INSERT INTO ';

		//Can only insert into one table at a time so grab the last table we tried to insert
		$lastTable = end($this->tables);
		$str .= $lastTable;

		$fields = '';
		$values = '';

		$counter = count($this->fields);
		$x = 0;

		foreach ($this->fields as $pair){
			$x++;
			$field = $pair['field'];
			$value = $pair['value'];
				
			$fields .= '`'.$this->escape($field).'`';
				
			if ($pair['escape'])
			$values .= (is_numeric($value) || is_bool($value)) ? $value : '\''.$this->escape($value).'\'';
			else
			$values .= $value;
			/*
			 if (is_numeric($value))
				$values .= $value;
				elseif (is_bool($value))
				$values .= $value === true ? 1 : 0;
				else
				$values .= '\''.$this->escape($value).'\'';
				*/
			if ($x < $counter){
				$fields .= ', ';
				$values .= ', ';
			}
		}

		$str .= "($fields) VALUES($values)";
		return $str;
	}

	public function getUpdate(){
		if (!$this->tables) throw new Exception("Must set a table.");
		if (!$this->fields) throw new Exception("Must select fields.");

		if ($this->_query) return $this->_query;

		$str = 'UPDATE ';
		$str .= join(', ', $this->tables);

		if ($this->joins) $str .= ' '.$this->prepareJoins();

		$str .= ' SET ';

		$count = count($this->fields);
		$x = 0;

		foreach($this->fields as $pair){
			$x++;
				
			$field = $pair['field'];
			$value = $pair['value'];
			
			if (is_null($value))  $str .= "$field = NULL";
			elseif (is_numeric($value)) $str .= $field.'='.$value;
			else $str .= $field.'='.'\''.$this->escape($value).'\'';
				
			if ($x < $count){
				$str .= ', ';
			}
		}

		if ($this->wheres) $str .= ' '.$this->prepareWhere();

		return $str;
	}

	public function getDelete(){
		if (!$this->tables) throw new Exception("Must set a table.");

		$str = 'DELETE FROM ';
		$str .= join(', ', $this->tables);

		if ($this->wheres) $str .= ' '.$this->prepareWhere();

		return $str;
	}

	public function prepareColumns(){
		return join(', ', $this->columns).' ';
	}

	protected function prepareWhere(){
		$str = 'WHERE ';
		$boolType = 'AND';
		$numberOfItems = count($this->wheres);
		$counter = 0;
		$currentGroup = 0;
		$endedGroup = true;
		//print_r($this->wheres);
		foreach ($this->wheres as $where){
			if ($where['group'] > $currentGroup){
				$str .= '(';
				$boolType = $this->whereGroups[ $where['group'] ];
				$currentGroup = $where['group'];
				$endedGroup = false;
			}elseif($where['group'] < $currentGroup){
				$str .= ')';
				$boolType = $this->whereGroups[ $where['group']];
				$currentGroup = $where['group'];
				$endedGroup = true;
			}
			//@TODO this statement needs fixing
			if (is_null($where['value'])){
				$str .= $where['field'];

				if ($where['operator'] == '!='){
					$str .= ' IS NOT null';
				}elseif ( $where['operator'] == '='){
					$str .= ' IS null';
				}else{
					$str .= $where['operator'] . ' null';
				}
			}
			elseif (is_numeric($where['value'])) $str .= $where['field'] . $where['operator'] . $where['value'];
			elseif (is_array($where['value'])){
				//array_walk($where, 'mysql_escape_string');
				//TODO do a type check using typeof() to determine if is a real int instead checking if is numeric
				$str .= $where['field'] . ' IN (\'' . implode("', '", $where['value'] ) . '\')';
			}
			elseif ($where['value'] instanceof SQuickQuery){
				$obj = $where['value'];
				$str .= $where['field'] . ' IN (' . $where['value']->getSelect(). ')';
			}
			else $str .= $where['field'] . ' ' . $where['operator'] . " '" . $this->escape($where['value'])."'";
			$counter++;
			if ($counter != $numberOfItems){
				$str .= " $boolType ";
			}
		}

		if (!$endedGroup) $str .= ')';

		return $str;
	}

	protected function prepareTables(){
		return 'FROM '.join(', ', $this->tables);
	}

	protected function prepareJoins(){
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

	protected function prepareGroup(){
		return 'GROUP BY '. join(', ', $this->groups);

	}

	protected function prepareHaving(){
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

	protected function prepareOrders(){
		$str = 'ORDER BY ';
		$numberOfItems = count($this->orders);
		$counter = 0;

		foreach ($this->orders as $order){
			$str .= "$order[field] $order[direction]";
			$counter++;
			if ( $numberOfItems != $counter) $str .= ', ';
		}

		return $str;
	}

	public function clearColumns(){
		$this->columns = array();
	}
	
	protected function prepareFields(){}

	/**
	 * Escapes a string based on the escape method set in this class.
	 */
	protected function escape( $value ){
		
		switch ($this->_dbType){
			case 'mysql':
				return mysql_real_escape_string( $value );
			case 'sqlite3':
				return SQLite3::escapeString( $value );
			default:
				return addslashes( $value );
		}

		return $escapedValue;
	}

	public function getQuery(){
		return $this->_query;
	}

}

class SQuickQueryException extends Exception{
	public function __construct( $message = null ){
		parent::__construct( $message );	
	}
}
?>
