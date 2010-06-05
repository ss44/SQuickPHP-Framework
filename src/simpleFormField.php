<?php
/*
 * A basic form field handler. Used to effectivly 
 * parse and handle form data in conunjuction with simpleForm.
 * 
 * @author Shajinder Padda <shajinder@gmail.com>
 * @created Apr 30, 2010
 */
 
 class SimpleFormField{
 	
 	protected $value = null;
 	protected $isRequired = false;
 	protected $elementName = null;
 	protected $validateArguments = array();
 	protected $clean  = null;
 	protected $errors = null;
 	
 	/**
 	 * Creates a new SimpleFormField object with basic options
 	 * 
 	 * @param String $elementName The name of the element that we want to be using.
 	 * 	This should be what the form element name is, in our REQUEST, GET or POST
 	 * 
 	 * @param Bool $required Whether or not this element is required.
 	 * 
 	 * @param Additional arguments are the same as simpleValidate(); 
 	 */
 	public function __construct( $elementName, $required = false){
		$this->elementName = $elementName;
		$this->isRequired = $required;
		
		if (func_num_args() > 2){
			$arguments = func_get_args();
			$this->validateArguments = array_slice( $arguments, 2, func_num_args()-2);
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
 				//oops($this->clean, 1);
 				//oops($this->isRequired, 1);
 				return ( (is_null($this->clean)  && !$this->isRequired) || (!is_null($this->clean)) );
 		}
 	}
 	
 	public function __set($key, $value){
 		switch ($key){
 			case 'value':
 				$this->value = $value;
 				$this->validate($value);
 				break;
 		}
 	}
 	
 	public function __toString(){
 		return (string) $this->value;
 	}
 	
 	/**
 	 * Validates the given value against our criteria.
 	 * 
 	 * @param Mixed $value The value that we want to validate
 	 */
 	protected function validate($value){
 		
 		//We need the value to be the first argument that we are supplying
 		//so need to add key to the beggining 
		if (!empty($this->validateArguments)){
 			$cleanParams = array_merge( (array) $value, $this->validateArguments );
 			
 			//Set the clean value.
 			$this->clean = call_user_func_array( 'cleanVar',  $cleanParams);
		}
		
 		
 		//If clean is null and this was a required field
 		if ( is_null($this->clean) && $this->isRequired){
 			$this->errors[] = "Value is required.";
 		}
 	}
 
 }