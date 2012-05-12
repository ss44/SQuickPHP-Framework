<?php
/**
 * Class to handle basic user authorization and access criteria.
 * 
 * @author Shajinder Padda
 * 
 * Basic table structure should be
 * 
 */


class SimpleUser extends SimpleDB{
	protected $_userTable = null; // Name of the table that we want to read user information from
	protected $_usernameField = null; //The field which we want to treat as the username field.
	protected $_passwordField = null; //The field that we want to read the password from.
	protected $_saltField = null; //The field which holds the salt code.
	
	//Rule when storing a password.
	protected $_passwordRule = null;

	protected $_username = null;
	protected $_password = null;

	//Was the user successfully logged in
	protected $_userValidated = false;
	
	//Store all user data in this array.
	protected $_userData = array();
	protected $_USERCONFIG = null;
	
	public function __construct($_CONFIG = null){
		global $__SIMPLE_CONFIG;
		parent::__construct($_CONFIG);

		$this->_USERCONFIG = (!$_CONFIG && is_array($__SIMPLE_CONFIG) && array_key_exists('SimpleUser', $__SIMPLE_CONFIG)) ? $__SIMPLE_CONFIG['SimpleUser'] : $_CONFIG;

		if (is_array($this->_USERCONFIG)){
			//Try to load settings from array
			$this->_userTable = array_key_exists('users_table', $this->_USERCONFIG) ? $this->_USERCONFIG['users_table'] : null;
			$this->_usernameField = array_key_exists('username_field', $this->_USERCONFIG) ? $this->_USERCONFIG['username_field'] : null;
			$this->_passwordField = array_key_exists('password_field', $this->_USERCONFIG) ? $this->_USERCONFIG['password_field'] : null;
			$this->_saltfield = array_key_exists('salt_field', $this->_USERCONFIG) ? $this->_USERCONFIG['salt_field'] : null;
			$this->_passwordRule = array_key_exists('password_rule', $this->_USERCONFIG) ? $this->_USERCONFIG['password_rule'] : null;
		}elseif(file_exists('site.ini') || (defined('SIMPLE_INI_FILE') && file_exists(SIMPLE_INI_FILE))){
			//If not found then check settings from config file
			$siteIni = defined(SIMPLE_INI_FILE) ? SIMPLE_INI_FILE : 'site.ini';

			$config =  parse_ini_file( $siteIni );
				
			if (array_key_exists('SU_USERS_TABLE', $config)) $this->_userTable = $config['SU_USERS_TABLE'];
			if (array_key_exists('SU_USERNAME_FIELD', $config)) $this->_usernameField = $config['SU_USERNAME_FIELD'];
			if (array_key_exists('SU_PASSWORD_FIELD', $config)) $this->_passwordField = $config['SU_PASSWORD_FIELD'];
			if (array_key_exists('SU_SALT_FIELD', $config)) $this->_saltField = $config['SU_SALT_FIELD'];
			if (array_key_exists('SU_PASSWORD_RULE', $config)) $this->_passwordRule = $config['SU_PASSWORD_RULE']
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
		
		//If a salt field is provided then we want to add the salt + password
		if ($this->_saltField){
			//Need to properly escape the password field
			$q->addWhere( 'MD5(CONCAT('. addslashes($this->_passwordField).','.$this->_saltField.'))' );	
		}else{
			$q->addWhere( $this->_passwordField, md5($password ));
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
	

	/**
	 * Allows creating of a new user in our database.
	 * 
	 * @param string $username the username of the user we are creating.
	 * @param string $password for the user that we're creating.
	 * @param array $fields Additional fields that we want to store when user is created.
	 */
	public function create( $username, $password, $fields ){
		
		//Test to make sure the user doesn't already exist.
		$q = new SimpleQuery();
		$q->addTable( $this->_userTable );
		$q->addWhere( $this->_usernameField, $username );

		if ( $this->getCount( $q ) > 0 )
			throw new SimpleUserException( 100, "Cannot create user. Username already exists.");
		
		//Test to make sure password is valid if we have password rules.
		if ( $this->_passwordRule ){
			if ( !preg_match( $this->_passwordRule, $password ) ){
				throw new SimpleUserException( 101,, "Invalid password. Password doesn't match system defined rule.");
			}
		}

		$q = new SimpleUser();
		$q->addTable( $this->_userTable );
		$q->addFields( $fields );
		$q->addField( $this->_usernameField, $username );

		//generate a salt and add it to the password if we can.
		if ( $this->_saltField ){
			//Create the user.
			$salt = randomChars( 10 );
			$password = $password + $salt;
			$q->addField( $this->_saltField, $salt  );
		}

		$q->addField( $this->_passwordField, md5($password) );
		$q->addField( $this->_saltField )
		
		return $this->insert($q);
	}

}

/**
 * Exceptions related to simple user functions.
 * 
 * Error codes are:
 * 100 : User already exists.
 * 101 : Password doesn't match our rule.
 */
class SimpleUserException extends Exception{}