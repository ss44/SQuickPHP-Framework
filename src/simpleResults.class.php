<?php
/**
 * Class to handle pagination and limit the number of results per page.
 * 
 * @author Shajinder Padda <shajinder@gmail.com>
 * @created May-21-2010
 */

require_once( dirname( __FILE__ ).'/simpleDB.class.php' );

class SimpleResults{
	
	//Use this value as the primary key.
	protected $_primaryKey = null;
	
	//Map field names to there html names
	protected $_fieldMappings = array();
	
	//Fields that we want to hide from display.
	protected $_hiddenFields = array();
	
	//The data that we are processing.
	protected $_data = array();
	
	//Maximum number of results we want per page.
	protected $_resultsPerPage = 25;
	
	//Rather then running the proccess method each we can track when we should run it
	//by using this cache flag. If no changes were made then its safe to assume that we want
	//to use the cache.
	protected $_useCache = false;
		
	//If a SimpleQuery object is passed then we'll want to keep that here.
	protected $_query = null;
	
	//The data we want to use 
	protected $_truncatedData;

	//Instance of db that we can use.
	protected $_db = null; 
	
	//Total Pages that were returned.
	protected $_totalPages = 1;
	
	protected $_currentPage = 1;
	
	/**
	 * Creates a new instance of the viewer
	 * @return unknown_type
	 */
	public function __construct(){
		global $__SIMPLE_CONFIG;

		$this->_db = new SimpleDB();
		
	}
	
	/**
	 * Adds a field to our table. Uses headerColumn to determine the clean name for a field.
	 * Fields are displayed in the order they are added. Field names are unique and in the case
	 * where fields are created twice the later data is used.
	 * 
	 * @param String $fieldName
	 * @param String $headerColumn
	 */
	public function addField( $fieldName, $headerColumn){
		$this->_fieldMappings[$fieldName] = (String) $headerColumn;
	}
	
	/**
	 * Field that we want to hide.
	 * 
	 * @param $fieldName The name of the field.
	 */
	public function hideField( $fieldName ){
		$this->_hiddenFields[] = $fieldName;	
	}
	
	/**
	 * Sets the data that we want to 9 in our results table.
	 * 
	 * @param $data Data that we want to process.
	 * 
	 * @return unknown_type
	 */
	public function setData( &$data ){
		//If its an instance of simple query then load data from that object
		if ( $data instanceof SimpleQuery ){
			$this->_data = null;
			$this->_query = clone $data;
		}else{
			$this->_query = null;
			$this->_data = $data;
		}
	}
	
	protected function process(){
		//@NOTE For now rely completely on the fact that class needs the $_GET
		
		//If they are using the query then we just need to add a limit in the query
		if ($this->_query instanceof SimpleQuery){
			$this->_totalPages = ceil( $this->_db->getCount( $this->_query ) / $this->_resultsPerPage );
			$this->_query->addLimit( $this->_resultsPerPage );
			$this->_query->addOffset( $this->_resultsPerPage * ($this->_currentPage - 1) );
			$this->_truncatedData = $this->_db->getAll( $this->_query );
		}
				
		$this->_useCache = true;
	}
	
	public function __get( $key ){
		switch ($key){
			case 'headers':
				return $this->_fieldMappings;
			case 'results':
				if (!$this->_useCache){
					$this->process();
				}
				return $this->_truncatedData;
			case 'totalPages':
				return $this->_totalPages;
			case 'currentPage':
				return $this->_currentPage;
			case 'primaryKey':
				return $this->_primaryKey;
				
		}
	}
	
	public function __set( $key, $value ){
		
		switch ($key){
			case 'resultsPerPage':
				$this->_resultsPerPage = cleanVar( $value, 'int', 1, 999999999 );
				break;
			case 'currentPage':
				$tmpVal = cleanVar ( $value, 'int', 1, 99999999 );
				$this->_currentPage = is_null($tmpVal ) ? 1 : $tmpVal;
				break;
			case 'primaryKey':
				$this->_primaryKey = $value;
				break;
		}
	}
	
	/**
	 * Returns the data mapping array.
	 * 
	 * @return Array of the mapped headers.
	 */
	public function getHeaders(){
		return $this->_fieldMappings;
	}
	
	public function getResults(){
		//If we are using the cache'd results then just return the data
		if  ($this->_useCache){
			return $this->_truncatedData;
		}
		
		//Otherwise proccess the data array and then return
		$this->process();
		return $this->_truncatedData;
	}

	public function setOrderBy( $fieldName ){
		
	}
	
	public function getHiddenFields(){
		return $this->_hiddenFields;
	}
}