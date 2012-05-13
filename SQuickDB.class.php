<?php
/**
 * Simple DB is a custom abstraction layer to communicate with various DB classes
 * Plans are to support
 * mysql, mysqli, postGres, and sqlLite.
 *
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 13-Dec-2008
 */

require_once(dirname(__FILE__).'/SQuickQuery.class.php');
require_once(dirname(__FILE__).'/SQuickException.class.php');

class SQuickDB{

	//If the database has a flags paramater this can be specified here. 
	//Varies depending on which database driver is used.
	protected $_dbFlags = null; 

	//Database type (mysql and sqlite are currently configured.)
	protected $_dbType = '';
	protected $_dbName = '';
	protected $_dbUser = '';
	protected $_dbPass = '';
	protected $_dbPath = '';
	protected $connection = null;
	protected $_DBCONFIG = null;

	protected $_lastID = null;

	//Some db engines use objects for thease cases we can store those classes in here.
	protected $_dbObj = null;
	
	public function __construct( $_CONFIG = null){
		
		$__SQUICK_CONFIG = null;

		if ( array_key_exists( '__SQUICK_CONFIG', $GLOBALS) )
			$__SQUICK_CONFIG = $GLOBALS['__SQUICK_CONFIG'];

		$this->_DBCONFIG = (!is_null($_CONFIG) && is_array($__SQUICK_CONFIG) && array_key_exists('SQuickDB', $__SQUICK_CONFIG)) ? $__SQuick_CONFIG['DB'] : $_CONFIG; 
		$this->connect();
	}

	/**
	 * Executes an insert command.
	 *
	 * @param SQuickQuery $q Query object that contains the insert statement.
	 * @return int Returns the last insert id if available or null if not.
	 * @TODO: Set the insert_id in the SQuickQuery object.
	 */
	public function insert(SQuickQuery $q){
		if (is_null($this->connection)) $this->connect();
		$this->queryChanges( $q );
	
		switch ($this->_dbType){
			case 'mysql':
				$result = mysql_query($q->getInsert(), $this->connection);
				
				if (!$result){
					throw new SQuickDBException( mysql_error( $this->connection ));
				}
				
				$this->_lastID = mysql_insert_id($this->connection);
				return $this->_lastID;
			
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				$r = $this->_dbObj->exec( $q->getInsert() );
				
				if ($r === false){
					throw new SQuickDBException( $this->_dbObj->lastErrorMsg() );
				}
				$this->_lastID = $this->_dbObj->lastInsertRowID();
				return $this->_lastID;
				
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}

	/**
	 * Executes an update command from SQuick query.
	 *
	 * @param SQuickQuery $q Query object that contains the update statement.
	 */
	public function update(SQuickQuery $q){
		$this->queryChanges( $q );

		switch ($this->_dbType){
			case 'mysql':
				if (is_null($this->connection)) $this->connect();
				$result = mysql_query($q->getUpdate(), $this->connection);
				return $result;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				$result = $this->_dbObj->exec( $q->getUpdate() );
				return $result;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}

	/**
	 * Attempts to update record if it already exists otherwise inserts it.
	 * @param SQuickQuery $q Query to update and or insert.
	 */
	public function upsert( SQuickQuery $q ){
		$this->queryChanges( $q );

		switch ($this->_dbType){
			case 'mysql':
				if (is_null($this->connection)) $this->connect();
				
				try{
					$row = $this->getRow( $q );

					if (empty($row)){
						return $this->insert($q);
					}
					
					//Otherwise update.
					return $this->update($q);
				}
				//Throw errors up.
				catch( Exception $ex ){
					throw $ex;
				}
				break;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				$row = $this->getRow( $q );

				if (empty($row)){
					return $this->insert($q);
				}

				return $this->update($q);

			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}

	/**
	 * Executes the delete command from a SQuick query object.
	 *
	 * @param SQuickQuery $q Query object that contains the delete statement.
	 * @param bool $overRide If no where statements are detected, will throw an error unless override is set to true.
	 * 	This is just added protection from accidently deleting all rows in a table.
	 */
	public function delete(SQuickQuery $q, $overRide = false){
		if (is_null($this->connection)) $this->connect();
		if ( !$overRide && empty($q->wheres) ) throw new SQuickDBException ("No where set for delete. Must set override to continue.");
		$this->queryChanges( $q );

		switch ($this->_dbType){
			case 'mysql':
				$result = mysql_query( $q->getDelete(), $this->connection);
				return $result;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				$result = $this->_dbObj->exec( $q->getDelete() );
				return $result;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}

	public function exec( SQuickQuery $q ){
		if (is_null($this->connection)) $this->connect();

		switch ($this->_dbType){
			case 'mysql':
				$result = mysql_query( $q->getQuery(), $this->connection);
				return $result;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				$result = $this->_dbObj->exec( $q->getQuery() );
				return $result;
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
				if (is_null($this->connection)) $this->connect();
				
				$r = mysql_query('Describe '.mysql_real_escape_string($tableName), $this->connection);
				while ($r == true && $row = mysql_fetch_assoc($r)){
					$result[ $row['Field'] ] = $row;
				}

				return $result;

			case 'sqlite3':
				
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}
	}

	/**
	 * Returns a single row from the current result set.
	 *
	 * @param SQuickQuery $q Query object that contains the select statement.
	 */
	public function getRow(SQuickQuery $q){
		if (!$this->connection) $this->connect();
		$this->queryChanges( $q );
		
		$result = array();

		switch ($this->_dbType){
			case 'mysql':
				$r = mysql_query($q->getSelect(), $this->connection);
				if ($r === false ){
					throw new SQuickDBException( "Unable to perform query: " . mysql_error() );
				}
				
				if (@mysql_num_rows($r) > 0){
					$result = mysql_fetch_assoc( $r );
				}
				return $result;

			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				
				$r = $this->_dbObj->query( $q->getSelect() );
				$result = $r->fetchArray( SQLITE3_ASSOC );

				return $result;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
		}

		return $result;
	}

	/**
	 * Returns all results from the current result set.
	 *
	 * @param SQuickQuery $q Query object that contains the update statement.
	 */
	public function getAll(SQuickQuery $q ){
		if (is_null($this->connection) || is_null( $this->_dbObj ) ){
			$this->connect();
		}

		$this->queryChanges( $q );
		$result = array();

		switch ($this->_dbType){
			case 'mysql':
				
				$r = mysql_query($q->getSelect(), $this->connection);

				if ($r === false ){
					throw new SQuickDBException( "Unable to perform query: " . mysql_error() );
				}

				if ($r !== false && mysql_num_rows($r) > 0){
					while($row = mysql_fetch_assoc($r)){
						$result[] = $row;
					}
					return $result;
				}
				break;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				
				$r = $this->_dbObj->query( $q->getSelect() );
				
				if ($r->numColumns() && $r->columnType(0) != SQLITE3_NULL) { 
					while( $row = $r->fetchArray( SQLITE3_ASSOC ) ){
						$result[] = $row;
					}
				}
				return $result;	
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
					
		}
		return $result;
	}

	/**
	 * Gets the column from a result set.
	 *
	 * @param SQuickQuery $q The SQuick query object that we want to iterate.
	 * @param String $column The column that we want to retrieve.
	 */
	public function getColumn( SQuickQuery $q, $column ){
		if (is_null($this->connection) || is_null( $this->_dbObj ) ){
			$this->connect();
		}
		
		$old = $q;
		$q = clone $old;
				
		$this->queryChanges( $q );
		$q->addColumn( $column );

		$result = array();

		switch ($this->_dbType){
			case 'mysql':
				
				$r = mysql_query($q->getSelect(), $this->connection);

				if (mysql_num_rows($r) > 0){
					while ($row = mysql_fetch_assoc($r)){
						$result[] = $row[ $column ];
					}
				}
				return $result;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				
				$r = $this->_dbObj->query( $q->getSelect() );
				
				if ($r->numColumns()) { 
					while( $row = $r->fetchArray( SQLITE3_NUM ) ){
						$result[] = $row[ 0 ];
					}
				}
				return $result;
		}
		
		return $result;
	}
	
	/**
	 * Returns a count of rows of the current query.
	 * @param SQuickQuery $q
	 */
	public function getCount( SQuickQuery $q ){
		if (is_null($this->connection) || is_null( $this->_dbObj ) ){
			$this->connect();
		}
		$this->queryChanges( $q );

		$tmpQuery = clone $q; 
		$tmpQuery->clearColumns();
		$tmpQuery->addColumn( 'COUNT(*)');
		
		$tmpData = $this->getRow($tmpQuery);
		
		return reset($tmpData);
	}
	
	/**
	 * Returns an associative array from the current result set.
	 *
	 * @param SQuickQuery $q Query object tthat contains the update statement.
	 * @param string $key The column that should be the key for the array.
	 * @param string $value The column that will be the value of the associtive array.
	 */
	public function getAssoc(SQuickQuery $q, $key, $value = null){
		$result = array();
		$this->queryChanges( $q );

		switch ($this->_dbType){
			case 'mysql':
				if (is_null($this->connection)) $this->connect();
				
				$r = mysql_query($q->getSelect(), $this->connection);

				if (mysql_num_rows($r) > 0){
					while( $row = mysql_fetch_assoc($r)){
						if (array_key_exists($key, $row)){
							$result[ $row[$key] ] = $value && array_key_exists($value, $row) ? $row[$value] : $row;
						}else{
							throw new SQuickDBException('Invalid key. Not found in result.');
						}
					}
					return $result;
				}
				break;
			case 'sqlite3':
				if (is_null($this->_dbObj)) $this->connect();
				
				$r = $this->_dbObj->query( $q->getSelect() );
				
				if ($r->numColumns()) { 
					while ($row = $r->fetchArray(SQLITE3_ASSOC)){
						if (array_key_exists($key, $row)){
							$result[ $row[$key] ] = $value && array_key_exists($value, $row) ? $row[$value] : $row;
						}else{
							throw new SQuickDBException('Invalid key. Not found in result.');
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
	
	protected function connect(){
		if (is_array($this->_DBCONFIG)){
			//Try to load settings from array
			$this->_dbType = array_key_exists('type', $this->_DBCONFIG) ? $this->_DBCONFIG['type'] : null;
			$this->_dbPath = array_key_exists('path', $this->_DBCONFIG) ? $this->_DBCONFIG['path'] : null;
			$this->_dbName = array_key_exists('name', $this->_DBCONFIG) ? $this->_DBCONFIG['name'] : null;
			$this->_dbUser = array_key_exists('user', $this->_DBCONFIG) ? $this->_DBCONFIG['user'] : null;
			$this->_dbPass = array_key_exists('password', $this->_DBCONFIG) ? $this->_DBCONFIG['pass'] : null;
			$this->_dbFlags = array_key_exists('flags', $this->_DBCONFIG) ? $this->_DBCONFIG['flags'] : null;
			oops('here');
		}elseif(defined('SQUICK_INI_FILE') && file_exists(SQUICK_INI_FILE)){
			//If not found then check settings from config file
			$siteIni = SQUICK_INI_FILE;
			$config = loadSQuickIniFile( $siteIni );
			$dbSettings = array_key_exists('DB', $config) ? $config['DB'] : array();

			if (array_key_exists('type', $dbSettings)) $this->_dbType = $dbSettings['type'];
			if (array_key_exists('path', $dbSettings)) $this->_dbPath = $dbSettings['path'];
			if (array_key_exists('name', $dbSettings)) $this->_dbName = $dbSettings['name'];
			if (array_key_exists('user', $dbSettings)) $this->_dbUser = $dbSettings['user'];
			if (array_key_exists('password', $dbSettings)) $this->_dbPass = $dbSettings['password'];
			if (array_key_exists('flags', $dbSettings)) $this->_dbFlags = $config['flags'];	
		}else{
			throw new SQuickDBException('No db settings provided.');
		}

		//@TODO: Try loading db if unable to load throw an exception.
		switch ($this->_dbType){
			case 'mysql':
				$this->connection = mysql_connect($this->_dbPath, $this->_dbUser, $this->_dbPass);
				mysql_select_db($this->_dbName, $this->connection);
				break;
			case 'sqlite3':
				if (!class_exists('SQLite3')){
					throw new SQuickDBException("SQuickDB requires SQLite3 class to exist");
				}
				
				$this->_dbObj = new SQLite3( $this->_dbName, $this->_dbFlags );
				$this->connection = true;
				break;
			case 'mysqli':
			case 'postgres':
			case 'sqlite':
			
				
			default:
				throw new SQuickDBException('Invalid/Unsupported database type.');
		}

		if (!$this->connection) throw new SQuickDBException("Unable to connect to DB.");
		
	}

	/**
	 * Returns the last insert id if available.
	 * @return int the Last Insert ID set in the database.
	 */
	public function getLastInsertID(){
		return $this->_lastID;
	}

	protected function queryChanges( SQuickQuery $q ){
		$q->setDBType( $this->_dbType );
	}
}

class SQuickDBException extends SQuickException{
	public function __construct( $message = null){
		parent::__construct( $message );
	}
}