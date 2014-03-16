<?php

namespace SQuick;

trait ConfigTrait {
	
	protected $_squickConfigOptions = null;

	public function setConfig( $option, $value ){
		$this->_squickConfigOptions[ $option ] = $value; 
	}

	public function getConfig( $option ){

		if ( array_key_exists( $option, $this->_squickConfigOptions ) ){
			return $this->_squickConfigOptions[ $option ];
		}

		return null;
	}
}