<?php
/**
* @author 		XorCo
* @date			31.27.2012
* @description	Deals with the loading, uploading, resizing and display of the picture
* @use			poll.js, polls.js, createPoll.js, auxiliary.js
*/
class Picture
{
	private $name;					// Name of the picture. Example: image.jpg
	private $link;					// Source of the image. Example: http://www.example.com/image.jpg
	private $width;					// Width in pixels of the image
	private $height;				// Height in pixels of the image
	private $marginLeft;			// MarginLeft to center the image relative to the thumbnail
	private $marginTop;				// MarginTop to center the image relative to the thumbnail
	private $type;					// According to the size of display of the picture. Possible values: icon, thumb or picture
	private $location;				// Folder of the current picture
	private $thumbSizeX;			// The thumbnail has a fixed size which is determined by its width: thumbSizeX and height: thumbSizeY
	private $thumbSizeY;			// Warning: the thumbnail represents the frame of the picture but may have different size and format than the picture itself
	private $appropriate_width;		// Width of the draggable area
	private $appropriate_height;	// Height of the draggable area
  
	/**
	* @description	Depending on the $source, the picture is going to be uploaded and/or resized.
	* @param		$source(String) 				- Example: http://www.example.com/image.jpg or /image.jpg
	* @param		$type("icon","thumb","picture")	- Determines the frame of the picture
	* @param		$appropriate_width/height		- Size of the draggable area
	* @param		$marginLeft/Top					- Margin of the picture relative to its frame
	*/
  	public function Picture($source=NULL,$type=NULL,$appropriate_width=0,$appropriate_height=0,$marginLeft=0,$marginTop=0)
  	{
	  	$this->link = $source;
	  	$this->appropriate_width = $appropriate_width;
	  	$this->appropriate_height = $appropriate_height;
	  	$this->marginLeft = $marginLeft;
	  	$this->marginTop = $marginTop;
	  	$this->location = $_SERVER['DOCUMENT_ROOT']."/FILES/IMAGES/SONDS/";
	  	
	  	// If the image is coming from an external source, it has to be saved on the server
	  	// All security verifications have been performed previously in security.js
	  	if ( strpos($this->link,"http://") !== false || strpos($this->link,"https://") !== false )
	  	{
	  		// Must come from an external source
	  		if ( strpos($this->link,C_LINKS_URL) === false )
	  		{
	  			// Picture is uploaded
	  			$this->link = $this->save($this->link);
	  		}
	  	}
	  	
	  	$this->name = basename($this->link);
	  	
	  	if ( strpos($this->link,"UPLOADS") !== false )
	  	{
	  		// Picture has been uploaded but not configured yet.
	  		$this->link = $_SERVER['DOCUMENT_ROOT']."/".$this->link;
	  		
	  		$this->loadAttributes($this->link);			// Properties of the picture are being loaded
	  		$this->createImage("picture");				// A new picture of size "picture" is being created
	  		$original = $this->link;
	  		
	  		$this->loadAttributes($this->link);			// Attributes of the resized picture are being updated
	  		$this->createImage("thumb",true);			// A new picture of size "thumb" is being created, the original is being deleted
	  		
	  		$this->loadAttributes($this->link);			// Attributes of the resized picture are being updated
	  		$this->createImage("icon",true);			// A new picture of size "icon" is being created, the original is being deleted
	  		
	  		$this->link = $original;					// The canonic link is being saved. Example: image.jpg which represents "_icon_image.jpg", "_thumb_image.jpg" or "image.jpg"
	  		$this->loadAttributes($this->link);			// Attributes of the resized picture are being updated
	  	}
	  	
	  	// When the picture is just being loaded, it is gonna get the right path in function of the display type. 
		switch($type)
		{
			case "thumb":
				$new = "_thumb_".$this->name;
				$link = $this->location.$new;
				break;
			case "icon":
				$new = "_icon_".$this->name;
				$link = $this->location.$new;
				break;
			default:
				$link = $this->location.$this->name;
				$type = "picture";
		}
		$this->type = $type;
		$this->link = $link;
		
		// Checking if the file exists, if not, a special picture is displayed
		if ( !is_file($this->link) )
		{
			if( $this->type == "picture" )
			{
				$link = $_SERVER['DOCUMENT_ROOT']."/TEMPLATE/IMAGES/boue.jpg";
			}
			else
			{
				$link = $_SERVER['DOCUMENT_ROOT']."/TEMPLATE/IMAGES/"."_".$this->type."_boue.jpg";
			}
		}
		$this->link = $link;
		
		// Gets all the information regarding display (size and margins)
		$this->loadProperties();
	}
  
	/**
	* @description	Gets the size, margins and draggable area of the picture
	*/  
	public function loadAttributes()
	{
		if ( $imgsize = getimagesize($this->link) )
		{
			$this->width = $imgsize[0];
			$this->height = $imgsize[1];
			( $this->appropriate_width == 0 ) ? $this->appropriate_width = $this->width : null;
			( $this->appropriate_height == 0 ) ? $this->appropriate_height = $this->height : null;
			
			$this->name = basename($this->link);
	  	}
	}
	
	/**
	* @description	Gets the size, margins and draggable area of the picture
	*/
	public function loadProperties()
	{
	  	if ( $imgsize = getimagesize($this->link) )
	  	{
	  		$this->width = $imgsize[0];
	  		$this->height = $imgsize[1];
	  		
	  		$this->thumbSizeX = $GLOBALS['PICTURES'][$this->type]['width'];			// GLOBAL(config.php)
	  		$this->thumbSizeY = $GLOBALS['PICTURES'][$this->type]['height'];		// GLOBAL(config.php)
	  		
	  		$this->marginLeft = round(($this->thumbSizeX - $this->width)/2);
	  		$this->marginTop =  round(($this->thumbSizeY - $this->height)/2);
	  
	  		$this->name = basename($this->link);
  		}
	}
	
	/**
	* @description	Creates an image of a certain size
	* @param		$thumbSize("icon","thumb","picture") - Determines the size of the picture
	* @param		$keepOriginal(boolean)				 - Determines if original must be deleted or not
	*/
  	public function createImage($thumbSize,$keepOriginal=false)
  	{
  		if ( $thumbSize == "picture" )
  		{
  			$this->thumbSizeX = $this->appropriate_width;
  			$this->thumbSizeY = $this->appropriate_height;
  		}
  		else
  		{
			$this->thumbSizeX = $GLOBALS['PICTURES'][$thumbSize]['width'];			// GLOBAL(config.php)
			$this->thumbSizeY = $GLOBALS['PICTURES'][$thumbSize]['height'];			// GLOBAL(config.php)
  		}
  		
		$dir = $this->location;
		// Loads the right image to be resized
    	( $thumbSize == "picture" ) ? $newName = $dir.$this->name 					: $newName = $dir."_".$thumbSize."_".$this->name;
    	( $thumbSize == "icon" ) 	? $newName = str_replace("_thumb_","",$newName) : NULL;
    	
    	// Making a temporary image
  		$imageOut = $dir."_temp_".$this->name;
  		
  		// If picture too small, than no resizing occurs.
		if ( $this->width <= $this->thumbSizeX && $this->height <= $this->thumbSizeY )
		{
			copy($this->link,$newName);
		}
		else
		{
			$image = imagecreatefromjpeg($this->link);  
			$imageX = imagesx($image);
			$imageY = imagesy($image);
			
			// Checking whether the image is landscape or portrait
			$rapportX = $this->width / $this->thumbSizeX;
			$rapportY = $this->height / $this->thumbSizeY; 
			if ( $rapportY <= $rapportX )
			{	// If portrait
				$thumbY = $this->thumbSizeY;
				$var = round($this->height / $this->thumbSizeY,2);
				$thumbX = round($this->width / $var); 
			}
			else	
			{	// If landscape
				$thumbX = $this->thumbSizeX;
				$var = round($this->width / $this->thumbSizeX,2);
				$thumbY = round($this->height / $var); 
			}

			$imageFinal  = imagecreatetruecolor($thumbX, $thumbY); // Will contain the resulting image
			imagecopyresampled ($imageFinal, $image, 0, 0, 0, 0, $thumbX, $thumbY, $imageX, $imageY); 
			
			// In the case of picture, the image may be cropped according to the given margins
			if ( $thumbSize == "picture" )
			{
				$this->thumbSizeX = $GLOBALS['PICTURES'][$thumbSize]['width'];
				$this->thumbSizeY = $GLOBALS['PICTURES'][$thumbSize]['height'];
				$imageCropped = imagecreatetruecolor($this->thumbSizeX, $this->thumbSizeY);
				imagecopy($imageCropped,$imageFinal,0,0,abs(round($this->marginLeft)),abs(round($this->marginTop)),$thumbX,$thumbY);
				$imageFinal = $imageCropped;
			}  
			
			$imageFinalX =  imagesx($imageFinal);
			$imageFinalY =  imagesy($imageFinal);
				   
			imagedestroy($image);
			imagejpeg($imageFinal,$imageOut);
			
			// Original picture may be deleted
			if ( file_exists($this->link) )
			{
				if ( !$keepOriginal )
				{
					unlink($this->link);
					
				}
				// The final picture receives its proper name
				rename($imageOut,$newName);
				$this->link = $newName;
			}			
		}
	}
	
	/**
	* @description	Picture is being copied in the right directory
	* @param		$source(String) - Example: http://www.example.com/
	* @output		link of the saved picture
	*/
	public function save($source)
	{
		$uniqid = "photo".uniqid(NULL,TRUE).".jpg";			// uniqid generates a unique id
		$nameFile = $_SERVER['DOCUMENT_ROOT']."/FILES/IMAGES/UPLOADS/".$uniqid;
		copyExternalFile($source,$nameFile);				// copies the image
		return "FILES/IMAGES/UPLOADS/".$uniqid;
	}
  	
	/**
	* Gets
	*/
 	public function getMarginLeft()	{	return $this->marginLeft; }
 	public function getMarginTop()	{	return $this->marginTop; }
	public function getName()		{	return $this->name; }
 	public function getWidth()		{ 	return $this->width; }
 	public function getHeight()		{	return $this->height; }	
 	public function getLink()
 	{
 		$root = $_SERVER['DOCUMENT_ROOT'];
 		$this->link = str_replace($root,"",$this->link,$number);
 		return $this->link;
 	}
 	
 	/**
 	* Sets
 	*/
 	public function setLink($var)		{	$this->link = $var;	}
 	public function setName($var)		{	$this->name = $var;	}
 	public function setLocation($var)	{	$this->location = $var;	}

}

?>
