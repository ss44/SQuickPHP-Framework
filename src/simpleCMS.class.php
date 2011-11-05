<?php
/** 
 * A simple cms class that stores fields.
 * to a database, and allows easy retrival.
 *
 * It is expected that this class is extended.
 * by the sub sites.
 * 
 * @aStor Shajinder Padda <ss@ss44.ca>
 * @created 20-Oct-2011
 */

require_once( dirname(__FILE__).'/simpleForm.class.php');
require_once( dirname(__FILE__).'/simpleFormField.class.php');

interface iSimpleCMS{
	
	public function load( $id );
	public function save();

	public static function getContentQuery( $section );
	
}
abstract class SimpleCMS extends SimpleDB implements iSimpleCMS{
		
	//A 2D array of simpleForms where the key is the section name.
	protected $fields = array();
	protected $section = null;
	protected $id = null;

	public function __construct( $section){
		parent::__construct();
		$this->setSection( $section );
	}

	public function addSection( $sectionName, SimpleForm $fields ){
		$this->fields[ $sectionName ] = $fields;
	}

	public function setSection( $section ){
		$this->section = $section;
	}

	public function unserializeFields( $content ){
		$this->requireSection();

		$fields = $this->fields[ $this->section ]->getFormFields();

		//Check if its serailzied
		if ( !isSerialzied( $content ) ) {
			//Throw an exception 
		}

		$fieldData = unserialize( $content );

		foreach ( $fieldData as $key=>$value ){
			if (array_key_exists( $key, $fields )){
				$fields[ $key ]->value = $value;
			}
		}

	}

	private  function requireSection(){
		if (!$this->section || !array_key_exists($this->section, $this->fields))
			SimpleCMSException::InvalidSection();
	}

	public function serializeFields(){
		$this->requireSection();
				
		$fields = $this->fields[ $this->section ]->getFormFields();

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


	public function validate( $array ){
		if (!$this->section || !array_key_exists($this->section, $this->fields))
			SimpleCMSException::InvalidSection();

		$sForm = $this->fields[$this->section];
		return $sForm->validate( $array );
	}

	public static function getContent( $section, $limit, $start ){

		$q = self::getContentQuery( $section );

		$q->addLimit( $limit );
		$q->addOffset( $start );

		return $this->getAll( $q );

	}
}
/*
class SimpleCMSField{
	 
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

class SimpleCMSException extends Exception {
	
	public function __construct( $errorCode, $message ){
		parent::__construct( $errorCode, $message );
	}

	public static function InvalidSection(){
		throw new self("Invalid Section specified.", 100);
	}

	
}