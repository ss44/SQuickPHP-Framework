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
 * @author Shajinder Padda <shajinder@gmail.com
 * @created 10-July-2009
 * @modified 10-July-2009
 */


class simpleFileParse{

	protected $folders = array();
	protected $files = array();
	protected $listing = array();
	
	protected $includeTypes = array();
	protected $excludeTypes = array();

	protected $rootFolder = null;
	protected $folder = null;
	
	protected $useCache = false;
	
	public function __construct($rootFolder = '.', $folder = null){
		if ($rootFolder) $this->root = $rootFolder;
		if ($folder) $this->folder = $folder;
	}
	
	
	/**
	 * Process the directory. We use a cache to prevent whether or not we should do this again.
	 *
	 */
	public function process(){
		if (!$this->rootFolder || !$this->folder) throw new Exception("Folder to parse not set.");
		
		//Open the parse 
		$dir = opendir( $this->folder );
		
		while( $file = readdir( $dir )){
			
			//Ignore the back directories
			if ( ($file == '.' || $file == '..') ) continue;
			
			if (is_dir($this->folder.'/'.$file)){
				$this->folders[] = $file;
			}else{
				//Run the include filters
				foreach ($this->includeTypes as $include){
					echo "Didn't match $include on $file<br>";
					if (!preg_match($include, $file)){
						
						continue 2;
					}
				}

				echo "file matched filters".$file."\n";
				
				//Run the exclude filters
				foreach( $this->excludeTypes as $exclude ){
					if (preg_match($exclude, $file)){
						continue 2;
					}else{
						break;
					}
				}
				
				$this->files[] = $file;
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
				if (file_exists( $rootFolder ) && is_dir( $rootFolder )){
					$this->rootFolder = $rootFolder;
					if (!$this->folder){
						$this->folder = $rootFolder; 
					}
					
				}else{
					$this->rootFolder = null;
					throw new Exception( "Invalid directory. $value");
				}
				break; 

			case 'folder':
				$folder = realpath( $this->rootFolder. '/' . $value ) ;
				
				//If the folder is less then the root folder we have problems
				if ( strlen($folder < $this->rootFolder) ){
					$this->folder = null;
					throw new Exception("You do not have permission to access that folder");
				}
				elseif ( file_exists( $folder ) && is_dir( $folder) ){
					$this->folder = $folder;
				}else{
					$this->folder = null;
					throw new Exception( "Invalid directory: $value");
				}
				break;
			default:
				throw new Exception("Invalid property $key set.");
		}
	}
	
}
?>