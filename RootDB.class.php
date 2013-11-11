<?php
/**
 * A base class to load a table as an object and validate fields,
 * against the table structure.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 13-Nov-2010
 */

namespace SQuick;

abstract class RootDB extends DataObj implements DB\ResultRow{
	
	protected $_table = null;
	protected $_primaryKey = null;
	protected $_tableInfo = null;
	protected $_originalData = null;
	protected $_isNew = true;
	protected $_id = null;
	protected $_normal = array();
	protected $_db = null;

	public static $_dbInstance = null;

	public function __construct( $keyId = null ){
		
		$this->_tableInfo = $this->_db->getTableStructure( $this->_table );

		$this->_resetData();

		if ( $keyId ){
			$this->load( array( $this->_primaryKey => $keyId ) );
		}
	}
	
	public function load( $loadParams ){
		
		$q = new Query();
		$q->addTable( $this->_table );
		
		foreach ( $loadParams as $field=>$val ){
			$q->addWhere( $field, $val );
		}
	
		$result = $this->_db->getRow( $q );
		
		if ( !empty( $result ) ){
			$this->_data = $result; 
		}

		if ( method_exists($this, '_afterLoad')){
			$this->_afterLoad();
		}

		$this->_originalData = $this->_data;
	}

	protected function _resetData(){
		$this->_data = array_fill_keys ( array_keys( $this->_tableInfo), null );
		$this->_originalData = $this->_data;
	}
	
	public function loadFromArray( $array ){
		$this->_resetData();
		$this->importFromArray( $array, false );

		if ( method_exists($this, '_afterLoad')){
			$this->_afterLoad();
		}

		$this->_originalData = $this->_data;

	}
	
	public function save(){
		
		if ( method_exists($this, '_beforeSave')){
			$this->_beforeSave();
		}

		$q = new Query();
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
			$this->_db->update($q);
			$id = $this->{$this->_primaryKey};
		}

		$this->_id = $id;

		if ( !is_array( $this->_primaryKey ) ){
			$this->_data[ $this->_primaryKey ] = $id;
		}

		if ( $this->_isNew && method_exists( $this, '_afterCreate') ){
			$this->_afterCreate();
		}elseif ( method_exists($this, '_afterSave') ) {
			$this->_afterSave();
		}

		// Update the old data history with the new data

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

			$ff = new sq\FormField( $key, $isRequired, $type, $min, $max );
			$ff->value = $this->$key;
			$form->addField( $ff );
		}

		return $form;
	}



	/**
	 * Fetches an item - from cache.
	 * @param mixed $id
	 * @param RootDB An Instance of the root db object.
	 */
	public static function fetch( $loadParams ){
		
		//@Todo load from cache if already instantiated
		$className = get_called_class();
		
		$tmp = new $className();
		$tmp->load( $loadParams );
		
		return $tmp;
	}

	/**
	 * Use the following db instance.
	 */
	public function useDB( DB $db ){
		$this->_db = $db;

		self::$_dbInstance = $db;
	}

	public static function getDBInstance(){
		return self::$_dbInstance;
	}


	public function importSQuickDBResultRow( $row ){
		$this->loadFromArray( $row );
		$this->importFromArray( $row );
	}
}

