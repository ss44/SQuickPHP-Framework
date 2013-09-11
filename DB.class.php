<?php
/**
 * Simple DB is a custom abstraction layer to communicate with various DB classes
 * Plans are to support
 * mysql, mysqli, postGres, and sqlLite.
 *
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 13-Dec-2008
 */

namespace SQuick;

// Cache the table structure
$_SQuickDBTableStructures = array();

class DB{


	protected $_sdbConfig = null;
	protected $connection = null;
	protected $_DBCONFIG = null;

	protected $_driver = null;

	protected $_lastID = null;

	//Some db engines use objects for thease cases we can store those classes in here.
	protected $_dbObj = null;
	
	public function __construct( $_CONFIG = null){
		$__SQUICK_CONFIG = null;

		if ( array_key_exists( '__SQUICK_CONFIG', $GLOBALS) )
			$__SQUICK_CONFIG = $GLOBALS['__SQUICK_CONFIG'];

		$this->_DBCONFIG = (!is_null($_CONFIG) && is_array($__SQUICK_CONFIG) && array_key_exists('SQuickDB', $__SQUICK_CONFIG)) ? $__SQuick_CONFIG['DB'] : $_CONFIG; 
		$this->_sdbConfig = new DBConfig();

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
		
		return $this->_driver->insert( $q );
	}

	/**
	 * Executes an update command from SQuick query.
	 *
	 * @param SQuickQuery $q Query object that contains the update statement.
	 */
	public function update(SQuickQuery $q){
		$this->queryChanges( $q );
		return $this->_driver->update($q);
	}

	/**
	 * Attempts to update record if it already exists otherwise inserts it.
	 * @param SQuickQuery $q Query to update and or insert.
	 */
	public function upsert( SQuickQuery $q ){
		$this->queryChanges( $q );

		return $this->_driver->upsert( $q );
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
		if ( !$overRide && empty($q->wheres) ) throw new SQuick\DBException ("No where set for delete. Must set override to continue.");
		$this->queryChanges( $q );

		return $this->_driver->delete($q);
	}

	public function exec( SQuickQuery $q ){
		return $this->_driver->exec( $q );
	}

	/**
	 * Returns the table structure for a given table as an associtive array.
	 *
	 * @param String $tableName Name of the table who's structure you are trying to retrieve.
	 * @return array A 2D associative of the fields with field properties.
	 */
	public function getTableStructure($tableName){
		global $_SQuickDBTableStructures;

		if ( !array_key_exists( $tableName, (array) $_SQuickDBTableStructures) ){
			$_SQuickDBTableStructures[ $tableName ] = $this->_driver->getTableStructure( $tableName );
		}

		return $_SQuickDBTableStructures[ $tableName ];
	}

	/**
	 * Returns a single row from the current result set.
	 *
	 * @param SQuickQuery $q Query object that contains the select statement.
	 */
	public function getRow(SQuickQuery $q){
		if (!$this->connection) 
			$this->connect();
		
		$this->queryChanges( $q );
		
		return $this->_driver->getRow($q);
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

		return $this->_driver->getAll( $q );
	}

	public function getResult( SQuickQuery $q ){
		return $this->_driver->getResult( $q );
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

		return $this->_driver->getColumn();
	}

	public function getOne( SQuickQuery $q){
		if (is_null($this->connection) || is_null( $this->_dbObj ) ){
			$this->connect();
		}
		
		$old = $q;
		$q = clone $old;
				
		$this->queryChanges( $q );
		$q->addLimit(1);

		return $this->_driver->getOne($q);

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
		return $this->_driver->getAssoc($q);
	}
	
	protected function connect(){
		if (is_array($this->_DBCONFIG)){
			//Try to load settings from array
			$this->_sdbConfig->type = array_key_exists('type', $this->_DBCONFIG) ? $this->_DBCONFIG['type'] : null;
			$this->_sdbConfig->path = array_key_exists('path', $this->_DBCONFIG) ? $this->_DBCONFIG['path'] : null;
			$this->_sdbConfig->name = array_key_exists('name', $this->_DBCONFIG) ? $this->_DBCONFIG['name'] : null;
			$this->_sdbConfig->user = array_key_exists('user', $this->_DBCONFIG) ? $this->_DBCONFIG['user'] : null;
			$this->_sdbConfig->pass = array_key_exists('password', $this->_DBCONFIG) ? $this->_DBCONFIG['pass'] : null;
			$this->_sdbConfig->flags = array_key_exists('flags', $this->_DBCONFIG) ? $this->_DBCONFIG['flags'] : null;
		}elseif(defined('SQUICK_INI_FILE') && file_exists(SQUICK_INI_FILE)){
			//If not found then check settings from config file
			$siteIni = SQUICK_INI_FILE;
			$config = loadSQuickIniFile( $siteIni );

			$dbSettings = array_key_exists('DB', $config) ? $config['DB'] : array();

			if (array_key_exists('type', $dbSettings)) $this->_sdbConfig->type = $dbSettings['type'];
			if (array_key_exists('path', $dbSettings)) $this->_sdbConfig->path = $dbSettings['path'];
			if (array_key_exists('name', $dbSettings)) $this->_sdbConfig->name = $dbSettings['name'];
			if (array_key_exists('user', $dbSettings)) $this->_sdbConfig->user = $dbSettings['user'];
			if (array_key_exists('password', $dbSettings)) $this->_sdbConfig->pass = $dbSettings['password'];
			if (array_key_exists('flags', $dbSettings)) $this->_sdbConfig->flags = $config['flags'];	
		}else{
			throw new SQuickDBException('No db settings provided.');
		}

		//Try loading db if unable to load throw an exception.
		switch ($this->_sdbConfig->type){
			case 'mysql':
				$this->_driver = new SQuickDBDriverMySQL( $this->_sdbConfig );
				break;
			case 'sqlite3':
				$this->_driver = new SQuickDBDriverSQLLite3(  $this->_sdbConfig );
				break;
			default:
				throw new SQuickDBException('Invalid/Unsupported database type.');
		}
	}

	/**
	 * Returns the last insert id if available.
	 * @return int the Last Insert ID set in the database.
	 */
	public function getLastInsertID(){
		return $this->_lastID;
	}

	protected function queryChanges( SQuickQuery $q ){
		$q->setDBType( $this->_sdbConfig->type );
	}
}