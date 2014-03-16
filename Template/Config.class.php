<?php
/**
 * Common config for Templating.
 */
 
namespace SQuick\Template;

class Config{
	
	use \SQuick\ConfigTrait;

	public function __construct(){
		$this->setConfig( 'driver', null );
		$this->setconfig( 'cache', null );
	}	
}