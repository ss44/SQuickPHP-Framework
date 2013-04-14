<?php

/**
 * Abstract parent class that all SQuickDB result types will extend.
 */
abstract class SQuickDBResult implements Iterator{

	protected $_objToUse = null;

	public function setHelperObj( $className ){
		$this->_objToUse = $className;
	}

	public function getHelperObj(){
		return $this->_objToUse;
	}
}