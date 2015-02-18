<?php

class Image
{	
	const FORMAT_GIF 	= 'gif';
	const FORMAT_JPEG = 'jpeg';
	const FORMAT_PNG 	= 'png';
	
	const MIME_GIF 	= 'image/gif';
	const MIME_JPEG 	= 'image/jpeg';
	const MIME_PNG 	= 'image/png';
	
	protected $__im_source;
	protected $__im_result;
	
	protected $__width_source;
	protected $__width_result;
	protected $__width_cropped;
	
	protected $__height_source;	
	protected $__height_result;	
	protected $__height_cropped;
	
	protected $__format = self::FORMAT_JPEG;	
	protected $__cropped = false;
	
	protected static $__supported_mime_types = array(
		
		self::MIME_GIF 	=> 'imagecreatefromgif',
		self::MIME_JPEG 	=> 'imagecreatefromjpeg',
		self::MIME_PNG 	=> 'imagecreatefrompng',
		
	);
	
	protected static $__supported_save_formats = array(
		
		self::FORMAT_GIF 		=> 'imagegif',
		self::FORMAT_JPEG 	=> 'imagejpeg',
		self::FORMAT_PNG 		=> 'imagepng',
		
	);
	
	public static function __is_valid($filename)
	{
		$im = static::from_file($filename);
		return $im->is_valid();
	}
	
	public static function from_file($filename)
	{
		if (!is_file($filename))
			return new static();
		
		$mime = Upload::__mime($filename);
		if (!isset(static::$__supported_mime_types[$mime]))
			return new static();
		
		$image = new static();
		$handler = static::$__supported_mime_types[$mime];
		$image->__im_source = call_user_func_array($handler, array($filename));
		$image->__width_source = imagesx($image->__im_source);
		$image->__height_source = imagesy($image->__im_source);
		return $image;
	}
	
	public static function from_resource($resource)
	{
		$image = new static();
		$image->__im_source = $resource;
		$image->__width_source = imagesx($image->__im_source);
		$image->__height_source = imagesy($image->__im_source);
		return $image;
	}
	
	public function is_valid()
	{
		return (bool) $this->__im_source;
	}
	
	public function width($value = ABLE_DEFAULT)
	{
		if ($value === ABLE_DEFAULT)
			return $this->__width_source;		
		$this->__width_result = $value;
		return $this;
	}
	
	public function height($value = ABLE_DEFAULT)
	{
		if ($value === ABLE_DEFAULT)
			return $this->__height_source;
		$this->__height_result = $value;
		return $this;
	}
	
	public function cropped($cropped)
	{
		$this->__cropped = $cropped;
		return $this;
	}
	
	public function im_source()
	{
		return $this->__im_source;
	}
	
	public function im_result()
	{
		return $this->__im_result;
	}
		
	protected function calc_dimensions()
	{
		// no dimensions => use source dimensions
		if (!$this->__width_result && !$this->__height_result)
		{
			$this->__width_result = $this->__width_source; 
			$this->__height_result = $this->__height_source;
			return;
		}
		
		// no height => calculate
		if ($this->__width_result && !$this->__height_result)
		{
			$ratio = $this->__width_result / $this->__width_source;
			$this->__height_result = (int) ($ratio * $this->__height_source);
			return;
		}
		
		// no width => calculate
		if ($this->__height_result && !$this->__width_result)
		{
			$ratio = $this->__height_result / $this->__height_source;
			$this->__width_result = (int) ($ratio * $this->__width_source);
			return;
		}
		
		if (!$this->__cropped) return;
		
		$ratio_width = $this->__width_result / $this->__width_source;
		$ratio_height = $this->__height_result / $this->__height_source;		
		$ratio_source = $this->__width_source / $this->__height_source;
		$ratio_desired = $this->__width_result / $this->__height_result;
		
		$this->__width_cropped = $this->__width_result;
		$this->__height_cropped = $this->__height_result;
		
		if ($ratio_source > $ratio_desired)
		{
			$this->__width_result = $ratio_height * $this->__width_source;
			return;
		}
		
		if ($ratio_source < $ratio_desired)
		{
			$this->__height_result = $ratio_width * $this->__height_source;
			return;
		}
	}
	
	protected function execute_crop()
	{
		if (!$this->__cropped) return;
	
		$im_resized = $this->__im_result;
		$this->__im_result = 
			imagecreatetruecolor($this->__width_cropped, 
			$this->__height_cropped);
		
		$src_x = floor(($this->__width_result - $this->__width_cropped) / 2);			
		$src_y = floor(($this->__height_result - $this->__height_cropped) / 2);	
		
		imagecopy($this->__im_result, 
			$im_resized, 0, 0, $src_x, $src_y,
			$this->__width_cropped, $this->__height_cropped);
	}
		
	public function execute()
	{
		$this->calc_dimensions();		
		
		$this->__im_result = 
			imagecreatetruecolor($this->__width_result, 
			$this->__height_result);
		
		imagecopyresampled(
			$this->__im_result, 
			$this->__im_source, 0, 0, 0, 0, $this->__width_result,
			$this->__height_result, $this->__width_source, 
			$this->__height_source);
		
		$this->execute_crop();
		
		return $this->__im_result;
	}
	
	public function save($filename, $format = null, $quality = null)
	{
		$this->execute();
		
		if ($format === null)
			$format = self::FORMAT_JPEG;
		
		if ($quality === null && $format === self::FORMAT_JPEG)
			$quality = 90;
		
		if (!isset(static::$__supported_save_formats[$format]))
			throw new Image_Format_Exception();
		
		$args = array();
		$args[] = $this->__im_result;
		$args[] = $filename;		
		if ($quality !== null)
			$args[] = $quality;
		
		$handler = static::$__supported_save_formats[$format];
		call_user_func_array($handler, $args);
	}
}

