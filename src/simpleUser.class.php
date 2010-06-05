<?php
/**
 * Class to handle basic user authorization and access criteria.
 * 
 * @author Shajinder Padda
 * 
 */

//require_once( dirname(__FILE__).'/SimpleDB.class.php');
//require_once( dirname(__FILE__).'/SimpleQuery.class.php');

class SimpleUser extends SimpleDB{
	protected $_userTable = null; // Name of the table that we want to read user information from
	protected $_usernameField = null; //The field which we want to treat as the username field.
	protected $_passwordField = null; //The field that we want to read the password from.
	protected $_saltField = null; //The field which holds the salt code.
	
	protected $_username = null;
	protected $_password = null;

	//Was the user successfully logged in
	protected $_userValidated = false;
	
	//Store all user data in this array.
	protected $_userData = array();
	
	public function __construct($_CONFIG = null){
		global $__SIMPLE_CONFIG;

		$this->_CONFIG = (!$_CONFIG && is_array($__SIMPLE_CONFIG) && array_key_exists('SimpleUser', $__SIMPLE_CONFIG)) ? $__SIMPLE_CONFIG['SimpleUser'] : $_CONFIG;

		if (is_array($this->_CONFIG)){
			//Try to load settings from array
			$this->_userTable = array_key_exists('users_table', $this->_CONFIG) ? $this->_CONFIG['users_table'] : null;
			$this->_usernameField = array_key_exists('username_field', $this->_CONFIG) ? $this->_CONFIG['username_field'] : null;
			$this->_passwordField = array_key_exists('password_field', $this->_CONFIG) ? $this->_CONFIG['password_field'] : null;
			$this->_saltfield = array_key_exists('salt_field', $this->_CONFIG) ? $this->_CONFIG['salt_field'] : null;
		}elseif(file_exists('site.ini') || (defined('SIMPLE_INI_FILE') && file_exists(SIMPLE_INI_FILE))){
			//If not found then check settings from config file
			$siteIni = defined(SIMPLE_INI_FILE) ? SIMPLE_INI_FILE : 'site.ini';

			$config =  parse_ini_file( $siteIni );
				
			if (array_key_exists('SU_USERS_TABLE', $config)) $this->_userTable = $config['SU_USERS_TABLE'];
			if (array_key_exists('SU_USERNAME_FIELD', $config)) $this->_usernameField = $config['SU_USERNAME_FIELD'];
			if (array_key_exists('SU_PASSWORD_FIELD', $config)) $this->_passwordField = $config['SU_PASSWORD_FIELD'];
			if (array_key_exists('SU_SALT', $config)) $this->_saltField = $config['SU_SALT'];
		}else{
			throw new SimpleUserException('No SimpleUser settings provided.');
		}
	}
	
	public function isLoggedIn(){
		return $this->_userValidated;
	}

	/**
	 * Attempt to log a user in using the given credentials. 
	 * @param String $username The username
	 * @param String $password The password. 
	 * @return bool true or false 
	 */
	public function login($username, $password){

		$q = new SimpleQuery();
		$q->addTable( $this->_userTable );
		$q->addWhere( $this->_usernameField, $username);
		
		//@TODO Handle salt fields. 
		//If a salt field is provided then we want to add the salt + password
		if ($this->_saltField){
			//$q->addWhere($this->_passwordField,)	
		}else{
			$q->addWhere( $this->_passwordField, md5($this->password ));
		}
		
		$this->_userData = $this->getRow($q);
		
		if ( !empty($this->_userData)) {
			$this->_userValidated = true;
			return true;
		}
		
		return false;
	}
	
	public function __get($key){
		if (array_key_exists($key, ($this->_userData)))
			return $this->_userData[$key];
	}
	
}

class SimpleUserException extends Exception{}