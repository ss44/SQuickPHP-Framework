<?php
/**
 * A form handler that handles multiple SimpleFormFields.
 * 
 * @author Shajinder Padda <shajinder@pause.ca>
 */
class SimpleForm implements ArrayAccess{
	
	protected $formFields = array(); 
	protected $errors = array();
	
    public function __construct() {
    		
    }
    
    public function __get($key){
    	switch ($key){
    		case 'errors':
    			return $this->errors;
    	}
    }
    /**
     * Adds a field to our form.
     * 
     * @param SimpleFormField $simpleFormField A simple form field object that we want to 
     * 	add to our form validation.
     */
    public function addField( SimpleFormField $simpleFormField ){
    	$this->formFields[ $simpleFormField->name ] = $simpleFormField;
    	$this->formFields[ $simpleFormField->name ]->setform ( $this );
    }
    
   /**
    * Validates our form from an array can be filled from $_GET, $_POST or an array
    * the fields are validated against the names of the form fields.
    * 
    * @param Mixed $data An array of values that we want to validate against.
    * @return bool Whether or not the validation was successfull.
    */
	public function validate( $data ){
		
		$allValid = true;
		$errors = array();
		
		//Loop over all our form fields and validate each from the data that we got back. 
		foreach ($this->formFields as $key=>$formField){

			//Check to see if the field we are trying to access has been sent in the data and set if it has. 
			$formField->value = array_key_exists($key, $data) ? $data[$key] : null; 
			
			//If any of the fields are invalid then all is not good.
			if ( !$formField->isValid ){
				$formField->addAttribute('class', 'sff_error');
				$errors[ $key ] = $formField->error;		
				$allValid = false;
			}
							
		}
		$this->errors = $errors;
		return $allValid;		
	}
	
	public function offsetExists( $offset ){
		return array_key_exists( $offset, $this->formFields );		
	}
	
	public function offsetGet( $offset ){
		return $this->formFields[ $offset ];	
	}
	
	public function offsetSet( $offset, $value ){
		throw new Exception("Cannot directly set form objects. Use addField method."); 
	}

	public function offsetUnset( $offset ){
		unset( $this->formFields[ $offset ]);
	}
	
	/**
	 * Generates / adds fields to a SimpleQuery object.
	 * 
	 * @param SimpleQuery $query
	 * @return unknown_type
	 */
	public function generateSimpleQueryFields( SimpleQuery $query ){
		
		foreach ( $this->formFields as $key=>$field){
			$query->addField( $key, $field->clean );		
		}
	}
	
	public function addError( $field, $error ){
		$this->errors[ $field ] = $error;
	}

	public function getFormFields(){
		return $this->_formFields;
	}
}

class SimpleFormException extends Exception{
	
	public function __construct( $message = null){
		parent::__construct( $message );
	} 	
} 

?>