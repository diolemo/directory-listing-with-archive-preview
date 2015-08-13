<?php

// call the __init() method once all libs load
$__able_lib_callbacks[] = 'Upload::__init';

class Upload
{
   public $file;
   public $name;
   
   protected static $__source;
   
   public static function __init()
   {
      static::$__source = array();      
      foreach ($_FILES as $name => $file)
      {
         if (!is_file($file['tmp_name'])) continue;
         static::$__source[$name] = new static();
         static::$__source[$name]->file = $file['tmp_name'];
         static::$__source[$name]->name = $file['name'];
      }
   }
   
   public static function has($name)
   {
      return isset(static::$__source[$name]);
   }
   
   public static function data($name = null)
   {
      if ($name === null) return static::$__source;
      return static::$__source[$name];
   }
   
   public static function __mime($file)
   {
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      return $finfo->file($file);
   }
   
   public function mime()
   {
      return static::__mime($this->file);
   }
}

