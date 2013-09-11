<?php
/**
 * Parent class for all db drivers. Drivers are loaded by SQuickDB based on requested DB type. Each Driver should have a similar result type.
 *
 * @author ss <ss@ss44.ca>
 * @created 13-Oct-2012
 */
namespace SQuick\DB;

abstract class Driver{
	
	protected $connection = null;
	protected $config = null;
	public  $lastInsertID = null;

	public function __construct( SQuickDBConfig $config ){
		$this->config = $config;
	}

	abstract public function getAll( SQuickQuery $q );
	abstract public function getRow( SQuickQuery $q );
	abstract public function getColumn( SQuickQuery $q );
	abstract public function getOne( SQuickQuery $q );
	abstract public function getAssoc( SQuickQuery $q );
	abstract public function getResult( SQuickQuery $q );

	abstract public function update( SQuickQuery $q );
	abstract public function insert( SQuickQuery $q );
	abstract public function delete( SQuickQuery $q );
	abstract public function upsert( SQuickQuery $q );
	abstract public function exec( SQuickQuery $q );

	abstract public function getTableStructure( $tableName );

	abstract public function connect();
} 

