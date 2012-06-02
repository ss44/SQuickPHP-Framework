<?php
/*
 * A basic form field handler. Used to effectivly 
 * parse and handle form data in conunjuction with SQuickForm.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created Apr 30, 2010
 */
 
 class SQuickFormField implements ArrayAccess{
 	
 	protected $_form = null;
 	protected $value = null;
 	protected $isRequired = false;
 	protected $elementName = null;
 	protected $validateArguments = array();
 	protected $clean  = null;
 	protected $error = null;
 	protected $attributes = array();
 	protected $normalFields = array();

 	/**
 	 * Creates a new SQuickFormField object with basic options
 	 * 
 	 * @param String $elementName The name of the element that we want to be using.
 	 * 	This should be what the form element name is, in our REQUEST, GET or POST
 	 * 
 	 * @param Bool $required Whether or not this element is required.
 	 * 
 	 * @param Additional arguments are the same as SQuickValidate(); 
 	 */
 	public function __construct( $elementName, $required = false){
		$this->elementName = $elementName;
		$this->isRequired = $required;
		
		if (func_num_args() > 2){
			$arguments = func_get_args();
			$numOfArguments = func_num_args()-2;
			$this->validateArguments = array_slice( $arguments, 2, $numOfArguments);
		}
 	}
 	
 	public function __get( $key ){
 		switch ( $key ){
 			
 			case 'value':
 				return $this->value;
 				
 			case 'isRequired':
 				return $this->isRequired;
 				
 			case 'name':
 				return $this->elementName;
 			
 			case 'clean':
 				return $this->clean;	
 			case 'isValid':
 				return ( (is_null($this->clean)  && !$this->isRequired) || (!is_null($this->clean)) );

 			case 'error':
 				return $this->error;
 		}
 	}
 	
 	public function __set($key, $value){
 		switch ($key){
 			case 'value':
 				$this->value = $value;
 				$this->validate($value);
 				break;
 			case 'normalizeFrom':
 				$this->normalFrom = (array) $value;
 				break;
 		}
 	}
 	
 	public function __toString(){
 		return (string) $this->value;
 	}
 	
 	/**
 	 * Manually add an error to this form field.
 	 * 
 	 * @param $error String the error message to dispaly.
 	 */
 	public function addError( $error ){
 		$this->error = $error;
 		
 		if (isset($this->_form)){
 			$this->_form->addError( $this->elementName, $error );
 		}
 	}
 	
 	/**s
 	 * Validates the given value against our criteria.
 	 * 
 	 * @param Mixed $value The value that we want to validate
 	 */
 	protected function validate($value){
 		
 		//We need the value to be the first argument that we are supplying
 		//so need to add key to the beggining 
		if (!empty($this->validateArguments)){
			$cleanParams = array();
			$cleanParams[] = $value;
			$cleanParams = array_merge( $cleanParams, $this->validateArguments );

 			//Set the clean value.
 			$this->clean = call_user_func_array( 'cleanVar',  $cleanParams);
		}
		
		// If value is not empty but is not clean then show an error for invalid field.
		if ( is_null($this->clean) && $value){
			$this->addError("Invalid data set for this field.");
		}

 		// If clean is null and this was a required field
 		if ( is_null($this->clean) && $this->isRequired){
 			// $this->errors[] = "Value is required.";
 			$this->addError("Value is required.");
 		}
 	}
 	
 	/**
 	 * Gets a text field for this given field.
 	 * @return String that can be used to create a text field
 	 */
 	public function getTextField( $attributes = null ){
 		$str = "<input type='text' ";
 		$str .= "name='$this->elementName' ";
 		$str .= "value='".$this->value."' "; 

 		if ( is_array($attributes) ) {
 			$this->addAttribute( $attributes );
 		}
 		
 		$atr = $this->getAttrStr();
 		
 		$str .= $atr;
 		$str .= " />";
 		
 		return $str;
 	}
 	
 	
 	/**
 	 * Gets a password field for this given field.
 	 * @return String that represents an input field for a password field.
 	 */
	public function getPasswordField( $attributes = array() ){
		$str = "<input type='password' ";
 		$str .= "name='$this->elementName' ";
 		$str .= "value='".$this->value."' "; 
 		
 		if ( is_array($attributes) ) {
 			$this->addAttribute( $attributes );
 		}
 		
 		$atr = $this->getAttrStr();
 		
 		$str .= $atr;
 		$str .= " />";
 		
 		return $str;
	} 	
 	
 	/**
 	 * Prepares a select element with options from values in the normalField array
 	 *
 	 * @return String that represents a select field.
 	 */
 	public function getSelectField( $attributes = array() ){
 		$str = "<select name='$this->elementName' ";

 		if ( is_array( $attributes )){
 			$this->addAttribute( $attributes );
 		}

 		$str .= $this->getAttrStr();
 		$str .= ">";

 		foreach ( $this->normalFields as $key => $value ){
 			$str .= "<option name='{$key}' ". ( $key == $this->value ? 'SELECTED' : '' ) .">$value</option>"; 
 		}

 		$str .= '</select>';
 		
 		return $str;
 	}

 	public function getRadioFields( $attributes = array() ){
 		
		if ( is_array( $attributes )){
 			$this->addAttribute( $attributes );
 		}

 		$str = '';
 		foreach ( $this->normalFields as $key=>$field ){
 			$str .= "<label><input type='radio' name='{$this->elementName}' value='{$key}' ";
 			$str .= ($key == $this->value) ? ' CHECKED ' : '';
 			$str .= ' />';
 			$str .= "{$field}</label>";
	 	}


	 	return $str;
 	}

 	/**
 	 * Adds an attribute of given name with value.
 	 * 
 	 * @param $name | mixed If an assoc array is passed then adds multiple attributes.
 	 * @param $value
 	 * @return unknown_type
 	 */
 	public function addAttribute( $name, $value = null){
 		
 		if (is_array($name)){
 			foreach ($name as $key => $value){
 				$this->addAttribute($key, $value);
 			}
 			return;	
 		}
 		
 		
 		$this->attributes[$name][] = $value;
 	}
 	
 	public function getAttrStr(){
		$str = '';
 		
		foreach ($this->attributes as $name => $val ){
 			$str = "$name = '".join( ' ', $val )."'"; 
 		}
 		
 		return $str; 
 	}
 	

 	/**
 	 * Sets the normal fields
 	 * 
 	 * @param $fields
 	 */
 	public function setNormalFields( $fields ){
 		$this->normalFields = $fields;
 	}

 	/**
 	 * Sets the SQuick form that this formfield is linked to.
 	 * @param SQuickForm $form
 	 * @return unknown_type
 	 */
 	public function setForm( SQuickForm $form ){
 		$this->_form = $form;
 	}

 	public function offsetExists( $key ){
 		return array_key_exists( $this->attributes, $key );
 	}

 	public function offsetSet( $key, $value ){
 		$this->addAttribute( $key, $value );
 	}

 	public function offsetGet( $key ){

 		if ( $this->offsetExists( $key ) )
 			return join(' ', $this->attributes[$name] );

 		return '';
 	}

 	public function offsetUnset( $key ){
 		unset( $this->attributes[$name] );
 	}
}