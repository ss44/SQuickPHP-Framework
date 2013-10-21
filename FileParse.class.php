<?php
/**
 * SimpleFileParse.class.php A top level and easily extendable file parsing class which parses files in a directory. 
 * 
 * Handles basic security such as not being able to navigate outside of the top level specified folder, 
 * and return only those files which are requested. 
 * 
 * Added functionality of being able to extend to that various operations can be performed and data arrays maniupulated of various files
 * by using extended classes.
 * 
 * @Example
 * 
 * Example 1 - Get only mp3s in folder files.
 * 	$listing = new simpleFileParse( 'files' );
 * 	$listing->includeType( 'MP3' );
 *  $files = $listing->getFiles();
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 10-July-2009
 * @modified 10-July-2009
 */

namespace SQuick;

class FileParse{

	protected $folders = array();
	protected $files = array();
	protected $listing = array();
	
	protected $includeTypes = array();
	protected $excludeTypes = array();

	protected $_rootFolder = null;
	protected $_folder = null;
	
	protected $useCache = false;
	
	protected $_mode = 'simple';
	
	public function __construct($rootFolder = '.', $folder = null){
		if ($rootFolder) $this->root = $rootFolder;
		if ($folder) $this->folder = $folder;
	}
	
	/**
	 * Process the directory. We use a cache to prevent whether or not we should do this again.
	 *
	 */
	public function process(){
		if (!$this->_rootFolder || !$this->_folder) throw new Exception("Folder to parse not set.");
		
		//Open the parse 
		$dir = opendir( $this->_folder );
		$fullPath = null;
		
		while( $file = readdir( $dir )){
			
			//Ignore the back directories
			if (($file == '.' || $file == '..')) continue;
			
			$fullPath = $this->_folder .'/'.$file;
			
			if (is_dir($fullPath)){
				
				if ($this->_mode == 'simple'){
					$this->folders[] = $file;
				}else{
					$listing = scandir( $fullPath );
					$tmp = array('folders'=>0, 'files'=>0, 'total'=>0);
					
					foreach ($listing as $item){
						if ($item != '.' || $item != '..'){
							if ( is_dir( $fullPath.'/'.$item )) $tmp['folders']++;
							else $tmp['files']++;
						}
					}
					
					$tmp['total'] = $tmp['files'] + $tmp['folders'];
					$this->folders[$file] = $tmp;
				}
			}else{
				//Run the include filters
				$matchedInclude = false;
				foreach ($this->includeTypes as $include){
					//If it doesn't match this filter then find the next filter
					if (!preg_match($include, $file)){
						continue;
					}
					//File matches our filter break the loop 
					else{
						$matchedInclude = true;
						break;
					}
				}
				
				//If we had an include filters and this didn't match one of them then go to the next file.
				if ( $this->includeTypes && !$matchedInclude ) continue;
				
				//Run the exclude filters
				foreach( $this->excludeTypes as $exclude ){
					//File matches one of our exclude filters so skip the file 
					if (preg_match($exclude, $file)){
						//File doesn't match should exclude
						continue 2;
					}
				}
				
				if ($this->_mode == 'simple'){
					$this->files[] = $file;
				}else{
					$advancedInfo = null;

					//Run any filters that may exist on the file based on its extension or just on the file
					if ( method_exists($this, 'filter_file' )){
						$advancedInfo = call_user_func( array($this, 'filter_file'), $fullPath );
					}
					//if they have any type of extension on the file ie .extension then handle those also
					elseif( strpos($file, '.') !== FALSE && method_exists($this, 'filter_file_'.strtoupper( substr($file, strrpos($file, '.')+1))) ){
						$advancedInfo = call_user_func( array($this, 'filter_file_'.strtoupper( substr($file, strrpos($file, '.')+1))), $fullPath );
					}else{
						$advancedInfo = stat( $fullPath );
					}
					
					$this->files[$file] = $advancedInfo;
				}
			}
			
			$this->listing[] = $file;
		}
		
		$this->useCache = true;
	}
	
	/**
	 * Returns a listing of all files and directories, sorted by name.
	 * 
	 * @return array Listing of all arrays and files.
	 */
	public function getList(){
		if (!$this->useCache) $this->process();
		return $this->listing;
	}
	
	/**
	 * Returns an array of all files.
	 */
	public function getFiles(){
		if (!$this->useCache) $this->process();
		return $this->files;
	}
	
	/**
	 * Returns all directories
	 * 
	 * @return Array Returns an array of all folders. 
	 */
	public function getFolders(){
		 if (!$this->useCache) $this->process();
		 return $this->folders;
	}
	
	/**
	 * Exclude a given extention from the files returned.
	 * 
	 * @param String Extention to filter one. 
	 */
	public function excludeExt( $ext, $isRegEx = false ){
		$str = $isRegEx ? $ext : '/.*\.'.addcslashes(strtolower($ext), './$').'$/i';
		$this->excludeTypes[] = $str;
		array_unique( $this->excludeTypes);
		$this->useCache = false;
	}
	
	/**
	 * Include only extension types of this.
	 * 
	 * @param String Extention to filter one.
	 * @param bool $isRegEx 
	 */
	public function includeExt( $ext, $isRegEx = false ){
		$str = $isRegEx ? $ext : '/.*\.'.addcslashes(strtolower($ext), './$').'$/i';
		$this->includeTypes[] = $str;
		array_unique($this->includeTypes);
		$this->useCache = false;
	}
	
	/**
	 * Allows setting of various properties:
	 * 	rootFolder = The root folder which to navigate on.
	 *  folder = The folder which to parse, can be absolute or relative to root but can not be outside. 
	 * 
	 * @throws Exception If invalid folder is specified.
	 */
	public function __set( $key, $value ){
		switch ($key){
			case 'rootFolder':
			case 'root':
				$rootFolder = realpath( $value );
				$before = $this->_rootFolder;
				
				if (file_exists( $rootFolder ) && is_dir( $rootFolder )){
					$this->_rootFolder = $rootFolder;
					if (!$this->_folder){
						$this->_folder = $rootFolder; 
					}
					
					if ($this->_rootFolder != $before)	$this->useCache = false;
					
				}else{
					$this->_rootFolder = null;
					throw new Exception( "Invalid directory. $value");
				}
				break; 

			case 'folder':
				$folder = realpath( $this->_rootFolder. '/' . $value ) ;
				$before = $this->_folder;
				//If the folder is less then the root folder we have problems
				if ( strlen($folder < $this->_rootFolder) ){
					$this->_folder = null;
					throw new Exception("You do not have permission to access that folder");
				}
				elseif ( file_exists( $folder ) && is_dir( $folder) ){
					$this->_folder = $folder;
					if ($this->_folder != $before)	$this->useCache = false;
				}else{
					$this->_folder = null;
					throw new Exception( "Invalid directory: $value");
				}
				break;

			case 'mode':
				if (!preg_match('/^(simple|advanced)$/i', $value)) throw new Exception("Mode can only be simple or advanced");
				$before = $this->_mode;
				$this->_mode = strtolower($value);
				if ($before != $this->_mode) $this->useCache = false;
				break;
				
			default:
				throw new Exception("Invalid property $key set.");
		}
	}

	public function clearFilters(){
		$this->includeTypes = array();
		$this->excludeTypes = array();
	}
}