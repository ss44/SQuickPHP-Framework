<?php

/**
 * Abstract parent class that all SQuickDB result types will extend.
 */

namespace SQuick\DB;

abstract class Result implements \Iterator{

	protected $_objToUse = null;
	protected $_keyField = null;

	protected $_db = null;

	protected $result;

	public function __construct( \SQuick\DB $db, $result ){
		$this->_db = $db;
		$this->result = $result;
	}

	public function setHelperObj( $className ){
		$this->_objToUse = $className;
	}

	public function getHelperObj(){
		return $this->_objToUse;
	}

	public function setKeyField( $keyField ){
		$this->_keyField = $keyField;
	}
	
}