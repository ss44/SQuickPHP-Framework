<?php
/**
 * Class to handle pagination and limit the number of results per page.
 * 
 * @author Shajinder Padda <shajinder@gmail.com>
 * @created May-21-2010
 */

require_once( dirname( __FILE__ ).'/simpleDB.class.php' );

class SimpleResults{
	
	//Map field names to there html names
	protected $_fieldMappings = array();
	
	//Fields that we want to hide from display.
	protected $_hiddenFields = array();
	
	//The data that we are processing.
	protected $_data = array();
	
	//Maximum number of results we want per page.
	protected $resultsPerPage = 25;
	
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
	
	protected $_totalPages = array();
	
	/**
	 * Creates a new instance of the viewer
	 * @return unknown_type
	 */
	public function __construct(){
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
	 * Sets the data that we want to process in our results table.
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
			$this->_query->addLimit( $this->resultsPerPage );
			$this->_query->addOffset( $this->resultsPerPage * $this->currentPage );
			$this->_truncatedData = $this->_db->getAll( $this->_query );
		}
		
		$this->_useCache = true;
	}
	
	public function __get( $key ){
		switch ($key){
			case 'headers':
			case 'results':
			case 'totalPages':
			case 'currentPage':
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

	public function getTotalPages(){
		return $this->_totalPages;
	}
	
	public function getCurrentPage(){
		
	}
	
	public function setResultsPerPage(){
		
	}
	
	public function setCurrentPage( $page ){
		
	}
	
	public function setOrderBy( $fieldName ){
		
	}
	
	public function getHiddenFields(){
		return $this->_hiddenFields;
	}
}