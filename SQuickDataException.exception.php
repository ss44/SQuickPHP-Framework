<?php
/**
 * SQuickData exception
 * @author SSingh <ss@ss44.ca>
 */
class SQuickDataException extends SQuickException{
	
	public function __construct( $errorMessage = "" ){
		
		parent::__construct( $errorMessage );
	}
}