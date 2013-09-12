<?php

/**
 * Abstract parent class that all SQuickDB result types will extend.
 */

namespace SQuick\DB;

abstract class Result implements \Iterator{

	protected $_objToUse = null;

	public function setHelperObj( $className ){
		$this->_objToUse = $className;
	}

	public function getHelperObj(){
		return $this->_objToUse;
	}
}