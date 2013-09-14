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

	public function __construct( Config $config ){
		$this->config = $config;
	}

	abstract public function getAll( \SQuick\Query $q );
	abstract public function getRow( \SQuick\Query $q );
	abstract public function getColumn( \SQuick\Query $q );
	abstract public function getOne( \SQuick\Query $q );
	abstract public function getAssoc( \SQuick\Query $q );
	abstract public function getResult( \SQuick\Query $q );

	abstract public function update( \SQuick\Query $q );
	abstract public function insert( \SQuick\Query $q );
	abstract public function delete( \SQuick\Query $q );
	abstract public function upsert( \SQuick\Query $q );
	abstract public function exec( \SQuick\Query $q );

	abstract public function getTableStructure( $tableName );

	abstract public function connect();
} 
