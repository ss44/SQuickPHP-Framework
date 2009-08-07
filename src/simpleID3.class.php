<?php
/**
 * Reads and parses ID3 Tags from MP3 Files.
 *
 * @author Shajinder Padda <shajinder@gmail.com>
 * @author James Heinrich <info@getid3.org>
 *
 * @credit getid3.org module.tag.id3v2.php
 * @created 28-July-2009
 */

class simpleID3{
	
	protected $_filename;
	protected $_id3Array;
	protected $_refresh;
	
	/**
	 * Creates a new instance of a simpleID3 tag, 
	 *
	 * @param string $filename to load.
	 */
	public function __construct( $filename = null ){
		if ($filename){
			return $this->loadFile( $filename );
		}
	}
	
	public function getArray( $filename ){
		return $this->_id3Array;
	}
	
	public function parseID3(){
		$this->_id3Array['id3v1'] = $this->parseID3v1();
		$this->_id3Array['id3v2'] = $this->parseID3v24();
		
		print_r($this->_id3Array);
	}
	
	protected function parseID3v1(){
		//var_dump($this->_filename);
		if (!$this->_filename) throw new Exception("Must load a valid file, before trying to parse.");
		
		//Try to the open the file.
		$fh = fopen($this->_filename, 'r');
		
		//Get the file size
		$size = filesize($this->_filename);
		
		//128 bits from the end 
		fseek($fh, $size-128);
		
		//Read the first 3 bytes, this should be TAG
		$data = fread($fh, 3);
		
		//If it says TAG this indicates that this is a valid id3v1 tag
		if ($data != 'TAG') return false;
		
		$info = array();
		//Next 30 is the song title
		$info['title'] = fread($fh, 30);
		$info['artist'] = fread($fh, 30);
		$info['album'] = fread($fh, 30);
		$info['year'] = fread($fh, 4);
		$info['comment'] = fread($fh, 30);
		$info['genre'] = fread($fh, 1);
		
		return $info;
	}
	
	protected function parseID3v24(){
		if (!$this->_filename) throw new Exception("Must load a valid file, before trying to parse.");
		
		//Open the file
		$fh = fopen($this->_filename, 'rb');
		
		//file size
		$size = filesize($this->_filename);
		
		$header = fread($fh, 10);
		
		//If it starts with ID3 then we'll continue otherwise screw it.
		if (substr($header, 0, 3) != 'ID3') return false;

		$info = array();
		$info['version'] = ord($header{3}).'.'.ord($header{4});
		
		print_r($info);
		return $info;
	}
	
	protected function loadFile( $filename ){
		//check to see that the file exists.
		if ( file_exists($filename) ){
			//@todo we should ideally try to check if this is a valid mp3 file.
			$this->_filename = $filename;
		}
	}
	
	/**
	 * Reads and returns an array of all id3 information found in a given file.
	 * 
	 * @param String $fileName Path to the file to try and parse id3 tag information from.
	 */
	public static function getID3Array ( $fileName ){
		
	}
}
 

?>