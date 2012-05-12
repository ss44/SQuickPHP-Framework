<?php
/**
 * Handles common image manipulation functions
 * Requires PHP v.5 <
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 04-July-2008
 */
require_once( dirname(__FILE__).'/SQuickException.class.php' );

class SQuickImage{
	protected $image = null;
	protected $imageInfo = null;
	//protected $newImage = null;
	
	/**
	 * 	Creates a new instance of SQuickImage
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
	 * @param String $imagePath The image to open
	 * @exception Exception Throws an exception
	 */
	public function loadImage($imagePath){
		//Throw our basic errors.
		if (!$imagePath || !is_string($imagePath)) 
			throw new Exception('Must supply a valid file path as a string');
		
		//Try actually loading the file
		if (!file_exists($imagePath)) 
			throw new Exception('Invalid Image. Image does not exist');
		
		//File exists try reading it
		$info = getimagesize($imagePath);
		
		if (!$info) 
			throw new Exception("Invalid Image. Not a valid image file.");

		$this->imageInfo = $info;
		
		$imageContents = file_get_contents( $imagePath );
		$this->image = imagecreatefromstring( $imageContents );
		
		return true;
	}

	/**
	 * Resizes an image to fit into the desired dimensions.
	 * @param Array $options =
	 *	maxWidth : int Max width to use.
	 *	maxHeight : int Max height to use.
	 *	constrain[true] : bool Whether or not to constrain an image.
	 *	magnify[faslse] : bool Whether or not to zoom in on the image if it doesn't fit in this size.
	 */	 
	public function resize( $params ){
		if (!$this->image) 
			throw new Exception('Must load a valid image first.');

		$options = array(
			'newWidth' => null,
			'newHeight' => null,
			'constrain' => true,
			'magnify' => false,
		);

		$options = (array) $params + $options;

		if ( is_null($options['newWidth']) && is_null($options['newHeight']) )
			throw SQuickException::missingParam( 'Must specify either one of newWidth or newHeight');
		
		$oldImage = $this->image;
		
		if ( $options['constrain'] ) {
			//Get the factor by which to change by
			
			// If they specify both max with and or height, determine which one is greater.
			if ( !is_null( $options['newWidth'] ) && !is_null( $options['newHeight'] ) ){
				$factor = $this->imageInfo[0] > $this->imageInfo[1] ? $options['newWidth'] / $this->imageInfo[0] : $options['newHeight'] / $this->imageInfo[1];
			}elseif ( !is_null($options['newWidth']) ){
				$factor = $options['newWidth'] / $this->imageInfo[0];
			}else{
				$factor = $options['newHeight'] / $this->imageInfo[1];
			}
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

		public function getImageInfo(){
			return $this->imageInfo;
		}
}


class SQuickImageException extends SQuickException{

}

?>