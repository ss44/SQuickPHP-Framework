<?php
/**
 * DB Configuration class to store appropriate DB Properties
 */

namespace SQuick\DB;

class Config{

	use \SQuick\ConfigTrait;

	public function __construct(){
		$this->setConfig( 'type', 		null );
		$this->setConfig( 'name', 		null );
		$this->setConfig( 'user', 		null );
		$this->setConfig( 'password', 	null );
		$this->setConfig( 'path', 		null );
		$this->setConfig( 'flags', 		null );		
	}

	public function __get( $key ){
		return $this->getConfig( $key );
	}
	
}