<?php
/**
 * Class to handle working with paypal NVP (Name Value Pair) api
 * 
 * @author Shajinder Padda <shajinder@gmail.com>
 */

class SimplePayPal{
	
	//Main api settings we need.
	protected $_apiSigniture = null;
	protected $_apiUsername = null;
	protected $_apiPassword = null;
	protected $_apiVersion = null;
	
	
	//Information we need to handle for payments
	protected $_cardFirstName = null;	//String
	protected $_cardLastName = null;	//String
	protected $_cardExpiry = null; //Unixtimestamp.
	protected $_cardCVV2 = null;
	protected $_cardType = null;
	protected $_cardAccount = null;

	//Additional information we want to track for a user
	protected $_addressStreet = null;
	protected $_addressStreet2 = null;
	protected $_addressCity = null;
	protected $_addressState = null;
	protected $_addressZipCode = null;
	protected $_addressCountryCode = 'US';
	protected $_addressPhoneNumber = null; 
	
	//Information about a given payment.
	protected $_totalAmount = 0;
	
	//Default currency format
	protected $_currency = 'USD';
	
	
	
	/**
	 * Creates a paypal object with the proper configuration settings.
	 * 
	 * Configuration values that are expected to be set are:
	 * 	 signiture
	 * 	 username
	 * 	 password
	 * 
	 * @param $_CONFIG
	 * @return unknown_type
	 */
	public function __construct( $_CONFIG = null ){
		
		//Can be defined in the site.ini file if provided.
	}

	/**
	 * Allows user to change or set the currency to handle transacitons in. Class defaults to 
	 * using USD.

	 * @param $currency The currency to set only currencies supported by papyal can be used.
	 * @throws Throws an exception if an invalid currency type is set.
	 */
	public function setCurrency( $currency ){
		//Set the currency to uppser case. So that we can compare properly.
		$currency = strtoupper($currency);
		
		switch 	($currency){
			case 'AUD':
			case 'BRL';
			case 'CAD':
			case 'CZK':
			case 'DKK':
			case 'EUR':
			case 'HKD':
			case 'HUF':
			case 'ILS':
			case 'JFY':
			case 'MXN':
			case 'NOK':
			case 'PHP':
			case 'PLN':
			case 'GBP':
			case 'SGD':
			case 'SEK':
			case 'CHF':
			case 'TWD':
			case 'THR':
			case 'USD':
				$currency = $currency;
				break;
			default:
				throw new SimplePayPalException("Invalid payment currency set.");
		}
	}
	
	/**
	 * Performs a direct payment 
	 * @return unknown_type
	 */
	public function DirectPayment( $mode = 'authorization'){
		
		//The Fields we need to populate.
		$fields = array();
		$fields['METHOD'] = 'DoDirectPayment';
		$fields['PAYMENTACTION'] = cleanVar($mode, 'str', array('Authorization', 'Sale'));
		$fields['IPADDRSES'] = $this->ipaddress;
		
		$field['CREDITCARDTYPE'] = $this->_cardType;
		$field['ACCT'] = $this->_cardAccount;
		$field['EXPDATE'] = $this->_cardExpiry;
		$field['CVV2'] = $this->_cardCVV2;
		
		$field['FIRSTNAME'] = $this->_firstName;
		$field['LASTNAME'] = $this->_lastName;
		
		$field['STREET'] = $this->_addressStreet;
		$field['STREET2'] = $this->_addressStreet2;
		$field['CITY'] = $this->_addressCity;
		$field['STATE'] = $this->_addressState;
		$field['COUNTRYCODE'] = $this->_addressCountryCode;
		$field['ZIP'] = $this->_addressZipCode;
		
		$field['AMT'] = $this->_totalAmount;
		
		$nvpStr = self::makeNVP($field);
	}
	
	public static function makeNVP( $fields ){
		
		//Make a name value pair string.
		
	}
}

class SimplePayPalException extends Extension{}