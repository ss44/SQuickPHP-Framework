<?php
/**
 * A base class to load a table as an object and validate fields,
 * against the table structure.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 13-Nov-2010
 */

abstract class SQuickControllerDB extends SQuickDB{
	
	protected $_table = null;
	protected $_primaryKey = null;
	protected $_tableInfo = null;
	protected $_data = null;
	protected $_isNew = true;
	
	public function __construct( $keyId = null ){
		parent::__construct();
		
		$this->_tableInfo = $this->getTableStructure( $this->_table );
		$this->_data = array_fill_keys ( array_keys( $this->_tableInfo), null );

		if ( $keyId ){
			$this->load( array( $this->_primaryKey => $keyId ) );
		}


	}
	
	public function load( $loadParams ){
		
		$q = new SQuickQuery();
		$q->addTable( $this->_table );
	
		foreach ( $loadParams as $field=>$val ){
			$q->addWhere( $field, $val );
		}
	
		$result = $this->getRow( $q );

		if ( !empty( $result ) ){
			$this->_data = $result; 
		}
	}
	
	public function save(){
		
		if ( method_exists($this, '_beforeSave')){
			$this->_beforeSave();
		}

		$q = new SQuickQuery();
		$q->addTable( $this->_table );
		$q->addFields( $this->_data );

		//If we have a primary key then update otherwise insert
		if ($this->_isNew){
			$id = $this->insert($q);
		}else{
			$id = $this->update($q);	
		}
		
		if ( method_exists($this, '_afterSave') ) {
			$this->_afterSave();
		}
		
	}
	
	public function __set( $key, $value ){
		if (!array_key_exists( $key, $this->_data )) die("Invalid $key. Does not exist in data");
		if (!array_key_exists( $key, $this->_tableInfo )) die("Invalid $key. Does not exist in data");
		
		//If a _setVarName method exists run that
		$methodName = "_set{$value}";
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
		if (!array_key_exists( $key, $this->_data )) 
			throw new SQuickDataException("Invalid $key. Does not exist in data");

		return $this->_data[ $key ];
	}
}

class SQuickDataException extends SQuickException{
	
	public function __construct( $errorMessage = "" ){
		
		parent::__construct( $errorMessage );
	}

}