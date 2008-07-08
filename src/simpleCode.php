<?php
/**
 * Collection of commonly used functions to be included in our project.
 * @author Shajinder Padda <shajinder@gmail.com
 * @created 28-June-2008
 */

/**
 * Outputs basic debug info with / pre tags attached.
 * @param mixed $var  The variable to output.
 * @param bool $showVarDump By default uses a print_r unless specified to use var_dump
 */
function oops( $var, $showVarDump = false){
	echo '<pre>';
	if ($showVarDump){
		var_dump($var);
	}else{
		print_r($var);
	};
	echo "</pre>";
}
?><?php
/**
 * A custom db abstraction class that for now will extend pdo, however
 * in the future, may need to break off into its own class in the case
 * where pdo can not be supported. 
 *
 * @author Shajinder Padda
 * @created 1-July-2008
 */

class simpleDBPdo extends PDO{
	
	/**
	 * Returns all records from query as an array.
	 *
	 * @param string|simpleQuery $query 
	 * @return mixed Array of all matching results.
	 */
	public function getAll($query){
		$sql = is_string($query) ? $query : $query->getSelect();
		
		$statement = $this->query($sql);
		return $statement->fetchAll();
	}
}

?><?php
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
<?php
/**
 * An extension for Smarty, may not be needed but included, in the case that we do need to add custom functions to smarty.
 */

require_once('Smarty.class.php');

class SimpleTemplate extends Smarty{
	public $wrapper = '';

	function setWrapper( $wrapper ){
		
	}
}

?><?php
/**
 * A convience class to help with testing.
 */

class Tester{
	
	public $tests = array();
	
	public function addTest($expected = null, $result = null){
		$pair = array();
		$pair['expected'] = $expected;
		$pair['result'] = $result;
		$this->tests[] = $pair;
	}
	
	public function run(){
		$failed = 0;
		echo "\nRunning Tester\n";
		
		foreach ($this->tests as $case=>$test){
			if ($test['expected'] !== $test['result']){
				echo "Failed Test " . ($case + 1) . "\n";
				var_dump($test['expected']);
				var_dump($test['result']);
				$failed++; 
			}
		}
		
		if ($failed > 0){
			echo "Failed $failed tests\n\n";
		}else{
			echo "Passed ". count($this->tests) ." tests\n\n";
		}
	}
}
?>