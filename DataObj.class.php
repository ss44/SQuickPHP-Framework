<?php
/**
 * Trait to handle basic data access methodologies implemented in data classes
 * where _get looks for a method if available, and normal properties.
 */

namespace SQuick;

abstract class DataObj implements \ArrayAccess{
	
	protected $_data = array();
	protected $_normal = array();

	public function __set( $key, $value ){

		if (!array_key_exists( $key, $this->_data ) ){
			throw new DataException( "Invalid $key. Does not exist in data" );	
		} 

		if (!array_key_exists( $key, $this->_tableInfo ) ){
			throw new DataException( "Invalid $key. Does not exist in data");
		}
		

		//If a _set_var_name method exists run that
		$methodName = "_set_{$key}";
		
		if ( method_exists( $this, $methodName ) ){
			$cleanValue = $this->$methodName( $value );
		}
		//@TODO Cast the value based on what's expected in the db struction
		else{
			// 
			$cleanValue = $value;
		}
		
		$this->_data[ $key ] = $cleanValue;
	}
	
	public function __get( $key ){
		
		if (!array_key_exists( $key, $this->_data ) ) 
			throw new DataException("Invalid $key. Does not exist in data");


		$methodName = "_get_{$key}";

		// If the user method exists then lets do somethings
		if ( method_exists( $this, $methodName ) ){
			return call_user_func( array( $this, $methodName ) );
		}
		
		return $this->_data[ $key ];
	}
	public function offsetExists( $key ){
		return array_key_exists( $key, $this->_normal );
	}

	public function offsetGet( $key ){
		$key = 'normal_'.strtolower( $key );

		if ( method_exists( $this, $key) ){
			return call_user_func( array($this, $key) );
		}
	}


	public function offsetSet( $key, $value ){
		
	}

	public function offsetUnset( $key ){

	}


	public function importFromArray( $array ){
		
		foreach ( $this->_data as $key => $data ){
			if ( array_key_exists( $key, $array ) ){
				$this->_data[ $key ] = $array[ $key ];
			}
		}

	}

}