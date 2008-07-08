<?php
/**
 * Handles common image manipulation functions
 * Requires PHP v.5 <
 * 
 * @author Shajinder Padda <shajinder@pause.ca>
 * @created 04-July-2008
 */

class simpleImageGD2{
	protected $image = null;
	protected $imageInfo = null;

	/**
	 * 	Creates a new instance of simpleImage
	 * 
	 * @param $imagePath = '';
	 * @exception Exception exception if image not found
	 */
	public function __construct($imagePath = ''){
		if ($imagePath)
			$this->load($imagePath);
		}
	}

	/**
	 * Loads an image at given path
	 * 
	 * @param $imagePath The image to open
	 * @exception Exception Throws an exception
	 */
	public function loadImage($imagePath){
		//Throw our basic errors.
		if (!$imagePath || !is_string($imagePath) throw('Must supply a valid file path as a string');
		
		//Try actually loading the file
		if (!file_exists($imagePath)) throw('Invalid Image. Image does not exist');
		
		//File exists try reading it
		$this->imageInfo = getimagesize($imagePath);
		oops($this->imageInfo);
	}

	public function resizeImage($newWidth, $newHeight, $constrain = true, $upsize = false){
	}

	public function saveImage($imagePath){
	
	}

}

?>