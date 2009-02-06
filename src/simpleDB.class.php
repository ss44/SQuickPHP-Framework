<?php
/**
 * Simple DB is a custom abstraction layer to communicate with various DB classes
 * Plans are to support
 * mysql, mysqli, postGres, and sqlLite.
 * 
 * @author Shajinder Padda <shajinder@gmail.com> 
 * @created 13-Dec-2008
 */

require_once('simpleQuery.class.php');

class SimpleDB{
	
	protected $_dbType = '';
	protected $_dbName = '';
	protected $_dbUser = '';
	protected $_dbPass = '';
	protected $_dbPath = '';
	protected $connection = null;
	
	public function __construct( $_CONFIG = null){
		
		if (is_array($_CONFIG)){
			//Try to load settings from array
			$this->_dbType = array_key_exists('type', $_CONFIG) ? $_CONFIG['type'] : null;
			$this->_dbPath = array_key_exists('path', $_CONFIG) ? $_CONFIG['path'] : null;
			$this->_dbName = array_key_exists('name', $_CONFIG) ? $_CONFIG['name'] : null;
			$this->_dbUser = array_key_exists('user', $_CONFIG) ? $_CONFIG['user'] : null;
			$this->_dbPass = array_key_exists('pass', $_CONFIG) ? $_CONFIG['pass'] : null;
			
		}elseif(file_exists('site.ini')){	
			//If not found then check settings from config file
			$config =  parse_ini_file('site.ini');
			
			if (array_key_exists('DB_TYPE', $config)) $this->_dbType = $config['DB_TYPE'];
			if (array_key_exists('DB_PATH', $config)) $this->_dbPath = $config['DB_PATH'];
			if (array_key_exists('DB_NAME', $config)) $this->_dbName = $config['DB_NAME'];
			if (array_key_exists('DB_USER', $config)) $this->_dbUser = $config['DB_USER'];
			if (array_key_exists('DB_PASS', $config)) $this->_dbPass = $config['DB_PASS'];			
		}else{
			throw Exception('No db settings provided.');
		}
		
		//@TODO: Try loading db if unable to load throw an exception.
		switch ($this->_dbType){
			case 'mysql':
				$this->connection = mysql_connect($this->_dbPath, $this->_dbUser, $this->_dbPass);
				mysql_select_db($this->_dbName, $this->connection);
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
			default:
				throw new Exception('Invalid/Unsupported database type.');
		}
		
		if (!$this->connection) throw new Exception("Unable to connect to DB.");
	}
	
	/**
	 * Executes an insert command.
	 * 
	 * @param SimpleQuery $q Query object that contains the insert statement.
	 * @return int Returns the last insert id if available or null if not.
	 * @TODO: Set the insert_id in the simpleQuery object.
	 */
	public function insert(SimpleQuery $q){
		switch ($this->_dbType){
			case 'mysql':
				$result = mysql_query($q->getInsert(), $this->connection);
				$lastId = mysql_insert_id($this->connection);
				return $lastId;
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}
	
	/**
	 * Executes an update command from simple query.
	 * 
	 * @param SimpleQuery $q Query object that contains the update statement.
	 */
	public function update(SimpleQuery $q){
		switch ($this->_dbType){
			case 'mysql':
				$result = mysql_query($q->getUpdate(), $this->connection);
				return $result;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}
	
	/**
	 * Executes the delete command from a simple query object.
	 * 
	 * @param SimpleQuery $q Query object that contains the delete statement.
	 * @param bool $overRide If no where statements are detected, will throw an error unless override is set to true.
	 * 	This is just added protection from accidently deleting all rows in a table.
	 */
	public function delete(simpleQuery $q, $overRide = false){
		if ( !$overRide && empty($q->wheres) ) throw new Exception ("No where set for delete. Must set override to continue.");
		
		switch ($this->_dbType){
			case 'mysql':
				$result = mysql_query( $q->getDelete(), $this->connection);
				return $result;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}
	
	/**
	 * Returns the table structure for a given table as an associtive array.
	 * 
	 * @param String $tableName Name of the table who's structure you are trying to retrieve.
	 * @return array A 2D associative of the fields with field properties. 
	 */
	public function getTableStructure($tableName){
		$result = array();

		switch ($this->_dbType){
			case 'mysql':
				$r = mysql_query('Describe '.mysql_real_escape_string($tableName), $this->connection);				
				while ($r == true && $row = mysql_fetch_assoc($r)){
					$result[ $row['Field'] ] = $row;
				}
				
				return $result;
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}	
	}
	
	/**
	 * Returns a single row from the current result set.
	 *
	 * @param SimpleQuery $q Query object that contains the select statement.
	 */
	public function getRow(simpleQuery $q){
		$result = array();
		
		switch ($this->_dbType){
			case 'mysql':
				$r = mysql_query($q->getSelect(), $this->connection);
				
				if (@mysql_num_rows($r) > 0){
					$result = mysql_fetch_assoc( $r );
					return $result;
				}
				
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':	
		}
		
		return $result;
	}
	
	/**
	 * Returns all results from the current result set.
	 * 
	 * @param SimpleQuery $q Query object that contains the update statement.
	 */
	public function getAll(SimpleQuery $q ){
		$result = array();
		
		switch ($this->_dbType){
			case 'mysql':
				$r = mysql_query($q->getSelect(), $this->connection);
				if (mysql_num_rows($r) > 0){
					while($row = mysql_fetch_assoc($r)){
						$result[] = $row;
					}
					return $result;
				}
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
			
		}
		return $result;
	}
	
	/**
	 * Returns an associative array from the current result set.
	 * 
	 * @param SimpleQuery $q Query object tthat contains the update statement.
	 * @param string $key The column that should be the key for the array.
	 * @param string $value The column that will be the value of the associtive array.
	 */
	public function getAssoc(SimpleQuery $q, $key, $value = null){
		$result = array();
		
		switch ($this->_dbType){
			case 'mysql':
				$r = mysql_query($q->getSelect(), $this->connection);
				
				if (mysql_num_rows($r) > 0){
					while( $row = mysql_fetch_assoc($r)){
						if (array_key_exists($key, $row)){
							$result[ $row[$key] ] = $value && array_key_exists($value, $row) ? $row[$value] : $row;  
						}else{
							throw new Exception('Invalid key. Not found in result.');
						}
					}
					return $result;
				}
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
		return $result;
	}
	
}