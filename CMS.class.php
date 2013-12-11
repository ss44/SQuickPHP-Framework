<?php
/** 
 * A simple cms class that stores fields.
 * to a database, and allows easy retrival.
 *
 * It is expected that this class is extended.
 * by the sub sites.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 20-Oct-2011
 */

namespace SQuick;

interface CMSInterface{
	
	public function load( $id );
	public function save();

	public static function getContentQuery( $section );
	
}

abstract class CMS implements CMSInterface{
		
	//A 2D array of simpleForms where the key is the section name.
	protected $fields = array();
	protected $section = null;
	protected $id = null;
	protected static $_db = null;
	protected $url = null;

	public static $_contentCache = array();

	public function __construct( $section){
		self::$_db = new DB();
		$this->setSection( $section );
	}

	public function addSection( $sectionName, Form $fields ){
		$this->fields[ $sectionName ] = $fields;
	}

	public function setSection( $section ){
		$this->section = $section;
	}

	public function unserializeFields( $content ){
		$this->requireSection();

		$fields = $this->fields[ $this->section ]->getFormFields();

		//Check if its serailzied
		if ( !isSerialized( $content ) ) {
			//Throw an exception 
		}

		$fieldData = unserialize( $content );
		
		foreach ( $fieldData as $key=>$value ){
			if (array_key_exists( $key, $fields )){
				$fields[ $key ]->value = $value;
			}
		}

	}

	protected  function requireSection(){
		if (!$this->section || !array_key_exists($this->section, $this->fields))
			SQuickCMSException::InvalidSection();
	}

	public function serializeFields(){
		$this->requireSection();
				
		$fields = (array) $this->fields[ $this->section ]->getFormFields();

		$fieldData = array();
		foreach ($fields as $fieldName=>$field){
			$fieldData[ $fieldName ] = $field->clean;
		}

		return serialize( $fieldData );
	}

	public function __get( $key ){
		if (array_key_exists( $key, $this->fields) ) {
			return $this->fields[ $key ];
		}
	}

	public function getId(){
		return $this->id;
	}

	public function setId( $id ){
		$this->id = $id;
	}

	public function setURL( $url ){
		$this->url = $url;
	}

	public function getURL(){
		return $this->url;
	}
	
	public function validate( $array ){
		if (!$this->section || !array_key_exists($this->section, $this->fields))
			SQuickCMSException::InvalidSection();

		$sForm = $this->fields[$this->section];
		return $sForm->validate( $array );
	}

	public static function getContent( $section, $contentField = 'content', $limit = null, $start = null){

		$cache = &self::$_contentCache;

		if ( !array_key_exists( $section, $cache ) ){
			$q = static::getContentQuery( $section );

			if (is_numeric($limit)){
				$q->addLimit( $limit );
			}

			if (is_numeric( $start )){
				$q->addOffset( $start );
			}
			
			if ( is_null( self::$_db ) ){
				self::$_db = new DB();
			}

			$db = self::$_db;
			$contentRows = (array) $db->getAll( $q );

			foreach ( $contentRows as &$content ){
				$content = $content + unserialize( $content[ $contentField ] );
			}

			$cache[ $section ] = $contentRows;
		}

		return $cache[ $section ]; 
	}
}
/*
class SQuickCMSField{
	 
	protected $name;
	protected $isRequired;
	protected $errorMessage;
	protected $valueType;
	protected $minValue;
	protected $maxValue;

	public function setName( $name ){
		$this->name = $name;
	}

	public function getName( $name ){
		return $this->name;
	}

	public function isRequired( $bool ){
		$this->isRequired = (bool) $bool;
	}

	public function setErrorMessage( $message ){
		$this->errorMessage = $message;
	}

	public function setValueType( $valueType ){
		$this->valueType = $valueType;
	}

	public function setMinValue( $minValue ){
		$this->minValue = $minValue;
	}

	public function setMaxValue( $maxValue ){
		$this->maxValue = $maxValue;
	}
	
	public function __construct( $name, $valueType = 'str', $minValue = null, $maxValue = null, $isRequired = null, $errorMessage = null){

		$this->setName( $name );
		$this->setValueType( $valueType );
		$this->setMinValue( $minValue );
		$this->setMaxValue( $maxValue );
		$this->isRequired( $isRequired );
		$this->setErrorMessage( $errorMessage );
		
	}
	
 }*/

class SQuickCMSException extends Exception {
	
	public function __construct( $errorCode, $message ){
		parent::__construct( $errorCode, $message );
	}

	public static function InvalidSection(){
		throw new self("Invalid Section specified.", 100);
	}

	
}