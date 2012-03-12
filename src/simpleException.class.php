<?php
/**
 * Extends exception to enable framework specific specific exception handling.
 *
 * @author Shajinder Padda <ss@ss44.ca>
 * @created 30-Jan-2011
 */

 class SQuickException extends Exception{
 	public function __construct( $errorMessage = "" ){
 		parent::__construct( $errorMessage );
 	}
 }