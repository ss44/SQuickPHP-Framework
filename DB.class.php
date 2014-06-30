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
use SQuick\DB as sdb; 

// Cache the table structure
$_SQuickDBTableStructures = array();

class DB{

	protected $_config = null;
	protected $connection = null;
	
	protected $_driver = null;

	protected $_lastID = null;

	//Some db engines use objects for thease cases we can store those classes in here.
	protected $_dbObj = null;


	public function __construct( sdb\Config $config = null ){

		if ( is_null( $config ) ){
			$config = Config::getConfig('DB', 'SQuick\DB\Config');
		}

		$this->_config = $config;

		$this->connect();
	}

	/**
	 * Executes an insert command.
	 *
	 * @param Query $q Query object that contains the insert statement.
	 * @return int Returns the last insert id if available or null if not.
	 * @TODO: Set the insert_id in the Query object.
	 */
	public function insert(Query $q){
		if (is_null($this->connection)) $this->connect();
		$this->queryChanges( $q );
		
		return $this->_driver->insert( $q );
	}

	/**
	 * Executes an update command from SQuick query.
	 *
	 * @param Query $q Query object that contains the update statement.
	 */
	public function update(Query $q){
		$this->queryChanges( $q );
		return $this->_driver->update($q);
	}

	/**
	 * Attempts to update record if it already exists otherwise inserts it.
	 * @param Query $q Query to update and or insert.
	 */
	public function upsert( Query $q ){
		$this->queryChanges( $q );

		return $this->_driver->upsert( $q );
	}

	/**
	 * Executes the delete command from a SQuick query object.
	 *
	 * @param Query $q Query object that contains the delete statement.
	 * @param bool $overRide If no where statements are detected, will throw an error unless override is set to true.
	 * 	This is just added protection from accidently deleting all rows in a table.
	 */
	public function delete(Query $q, $overRide = false){
		if (is_null($this->connection)) $this->connect();
		if ( !$overRide && empty($q->wheres) ) throw new DBException ("No where set for delete. Must set override to continue.");
		$this->queryChanges( $q );

		return $this->_driver->delete($q);
	}

	public function exec( Query $q ){
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
	 * @param Query $q Query object that contains the select statement.
	 */
	public function getRow(\SQuick\Query $q){
		if (!$this->connection) 
			$this->connect();
		
		$this->queryChanges( $q );
		
		return $this->_driver->getRow($q);
	}

	/**
	 * Returns all results from the current result set.
	 *
	 * @param Query $q Query object that contains the update statement.
	 */
	public function getAll(Query $q ){
		if (is_null($this->connection) || is_null( $this->_dbObj ) ){
			$this->connect();
		}

		$this->queryChanges( $q );

		return $this->_driver->getAll( $q );
	}

	public function getResult( Query $q ){
		return $this->_driver->getResult( $q );
	}

	/**
	 * Gets the column from a result set.
	 *
	 * @param Query $q The SQuick query object that we want to iterate.
	 * @param String $column The column that we want to retrieve.
	 */
	public function getColumn( Query $q, $column ){
		if (is_null($this->connection) || is_null( $this->_dbObj ) ){
			$this->connect();
		}
		
		$old = $q;
		$q = clone $old;
				
		$this->queryChanges( $q );

		// $q->addColumn( $column );

		return $this->_driver->getColumn( $q, $column );
	}

	public function getOne( Query $q){
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
	 * @param Query $q
	 */
	public function getCount( Query $q ){
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
	 * @param Query $q Query object tthat contains the update statement.
	 * @param string $key The column that should be the key for the array.
	 * @param string $value The column that will be the value of the associtive array.
	 */
	public function getAssoc(Query $q, $key, $value = null){
		$result = array();
		$this->queryChanges( $q );
		return $this->_driver->getAssoc($q, $key, $value );
	}
	
	public function escape( $var ){
		return $this->_driver->escape( $var );
	}

	protected function connect(){

		//Try loading db if unable to load throw an exception.
		switch ( $this->_config->getConfig('type') ){
			
			case 'mysql':
			case 'mysqli':
				$this->_driver = new sdb\DriverMySQLi( $this->_config, $this );
				break;
			
			case 'sqlite3':
				$this->_driver = new sdb\DriverSQLLite3(  $this->_config, $this );
				break;
				
			default:
				throw new DBException('Invalid/Unsupported database type.');
		}
	}

	/**
	 * Returns the last insert id if available.
	 * @return int the Last Insert ID set in the database.
	 */
	public function getLastInsertID(){
		return $this->_lastID;
	}

	protected function queryChanges( Query $q ){
		$q->setDBType( $this->_config->type );
		$q->setEscape( array($this, 'escape') );
	}

	/**
	 * Retrieves the enum properties for a given field.
	 * @param String The table containing the ENUM field.
	 * @param String The field who'se enum to retrieve.
	 */
	public function getEnum( $table, $field ){

		$table = $this->getTableStructure( $table );

		if ( !array_key_exists( $field, $table ) ){
			throw new DBException("Field $field does not exist");
		}

		return $this->_driver->parseEnum( $table[ $field ] );
		
	}
}