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
	//protected $newImage = null;
	
	/**
	 * 	Creates a new instance of simpleImage
	 * 
	 * @param $imagePath = '';
	 * @exception Exception exception if image not found
	 */
	public function __construct($imagePath = ''){
		if ($imagePath){
			
			return $this->loadImage($imagePath);
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
		if (!$imagePath || !is_string($imagePath)) throw new Exception('Must supply a valid file path as a string');
		
		//Try actually loading the file
		if (!file_exists($imagePath)) throw new Exception('Invalid Image. Image does not exist');
		
		//File exists try reading it
		$info = getimagesize($imagePath);
		$this->imageInfo = $info;
		//oops($info);
		
		switch ($info['mime']){
			case 'image/jpeg':
				$this->image = imagecreatefromjpeg( $imagePath );
				break;
			case 'image/gif':
				$this->image = imagecreatefromgif( $imagePath );
				break;
			case 'image/png':
				$this->imagecreatefrompng($imagePath);
				break;
			default :
				return false;//throw new Exception('Unrecognized image file: '. $imagePath);
				break;
		}
		
		return true;
	}

	public function resizeImage($newWidth, $newHeight, $constrain = true, $magnify = false){
		if (!$this->image) throw new Exception('Must load a valid image first.');
		
		$oldImage = $this->image;
		
		if ($constrain){
			//Get the factor by which to change by
			$factor = $this->imageInfo[0] > $this->imageInfo[1] ? $newWidth / $this->imageInfo[0] : $newHeight / $this->imageInfo[1];
		}else{
			$factor = 1;
		}
		
		//TODO: Implement magnify rule
		$height = $this->imageInfo[1] * $factor;
		$width =  $this->imageInfo[0] * $factor;
		$newImage = imagecreatetruecolor($width, $height);
		
		imagecopyresized( $newImage, $oldImage, 0, 0, 0, 0, $width, $height, $this->imageInfo[0], $this->imageInfo[1]);
		$this->image = $newImage;
	}
	

	public function saveImage($imagePath, $type = 'jpeg'){
		if (!$imagePath) throw new Exception('Must specify a valid image path');
		
		//TODO: Hande other types
		switch ($type){
			case 'jpeg':
				imagejpeg( $this->image, $imagePath );
				break;
			case 'gif':
				imagegif($this->image, $imagePath);
				break;
			case 'png':
				imagepng($this->image, $imagePath);
				break;
				
		}
	}
	
	public function outputToScreen(){
		//Outputs an image to the screen
		header("Content-Type: image/png");
		imagepng($this->image);
	}

}

?>