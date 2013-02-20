<?php
/**
 * A base class to load a table as an object and validate fields,
 * against the table structure.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 13-Nov-2010
 */

abstract class SQuickRootDB implements ArrayAccess{
	
	protected $_table = null;
	protected $_primaryKey = null;
	protected $_tableInfo = null;
	protected $_data = null;
	protected $_originalData = null;
	protected $_isNew = true;
	protected $_db = null;
	protected $_id = null;
	protected $_normal = array();

	public function __construct( $keyId = null ){
		
		$this->_tableInfo = $this->_db->getTableStructure( $this->_table );

		$this->_resetData();

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
	
		$result = $this->_db->getRow( $q );
		
		if ( !empty( $result ) ){
			$this->_data = $result; 
		}

		if ( method_exists($this, '_afterLoad')){
			$this->_beforeSave();
		}

	}

	protected function _resetData(){
		$this->_data = array_fill_keys ( array_keys( $this->_tableInfo), null );
		$this->_originalData = $this->_data;
	}
	public function loadFromArray( $array ){
		$this->_resetData();
		$this->importArray( $array, false );
		$this->_originalData = $this->_data;

		if ( method_exists($this, '_afterLoad')){
			$this->_afterLoad();
		}

	}
	
	public function save(){
		
		if ( method_exists($this, '_beforeSave')){
			$this->_beforeSave();
		}

		$q = new SQuickQuery();
		$q->addTable( $this->_table );
		$q->addFields( $this->_data );

		$keys = (array) $this->_primaryKey;

		foreach ( $keys as $key ){
			$q->addWhere( $key, $this->$key );
			if ( !is_null($this->$key) )
				$this->_isNew = false;
		}

		//If we have a primary key then update otherwise insert
		if ($this->_isNew){
			$id = $this->_db->insert($q);
			$this->{$this->_primaryKey} = $id;
		}else{
			$id = $this->_db->update($q);	
		}
		
		$this->_id = $id;

		if ( !is_array( $this->_primaryKey ) ){
			$this->_data[ $this->_primaryKey ] = $id;
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

	public function importArray( $data, $useSetters = true ){
		
		foreach ( $data as $column => $value ){
			if ( $useSetters ){
				$this->$column = $value;
			}

			elseif ( array_key_exists( $column, $this->_data) ){
				$this->_data[$column] = $value;
			}
		}
	}

	/**
	 * Returns a generic simple form with all the fields as set by the table structure
	 * @return SQuickForm();
	 */
	public function generateForm(){
		$form = new SQuickForm();

		foreach ( $this->_tableInfo as $key => $info ){
			$isRequired = ($info['Null']  == 'No');
			
			preg_match('/(int|char|text)(\((\d+)\))?\s?(unsigned)?/', $info['Type'], $tmp);

			$min = null;
			$max = null;

			switch( $tmp[1] ){
				case 'int':
					$type = 'int';
					$max =  (10 ^ $tmp[3] ) - 1 ;
					break;
				case 'char':
				case 'text':
				default:
					$type = 'str';
					if ( array_key_exists( 3, $tmp )){
						$max = $tmp[3];
					}
					break;
			}

			$ff = new SQuickFormField( $key, $isRequired, $type, $min, $max );
			$ff->value = $this->$key;
			$form->addField( $ff );
		}

		return $form;
	}

	public function importFromArray( $array ){
		
		foreach ( $this->_data as $key => $data ){
			if ( array_key_exists( $key, $array ) ){
				$this->_data[ $key ] = $array[ $key ];
			}
		}

	}

	/**
	 * Use the following db instance.
	 */
	public function useDB( SQuickDB $db ){
		$this->_db = $db;
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
}

