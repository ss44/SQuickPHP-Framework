<?php
/**
 * SQuickData exception
 * @author SSingh <ss@ss44.ca>
 */

namespace SQuick;

class DataException extends Exception{
	
	public function __construct( $errorMessage = "" ){
		
		parent::__construct( $errorMessage );
	}
}