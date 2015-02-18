<?php

class Content
{
   public static $__auto_render = true;
   public static $__auto_trim = true;
   public static $__captured = array();
   
   private static $__active_captures = array();
   private static $__type_set = false;
   
   public static $__content;
   public static $__title;

   public static function __init_buffers()
   {
      ob_start(); // trim
      ob_start(); // render
   }
   
   public static function __render() 
   {
      // end all open captures
      while (count(static::$__active_captures) > 0)
         static::end();
         
      if (static::$__auto_render === true) 
         static::render();
      ob_end_flush();
   }
   
   public static function __trim() 
   {
      if (static::$__auto_trim !== true) 
         return ob_end_flush();
      
      $out = ob_get_contents();
      $out = trim($out);
      ob_end_clean();
      echo $out;
   }
   
   public static function esc($content)
   {
      if ($content === null) return;
      return htmlspecialchars($content, ENT_QUOTES);
   }
   
   public static function render()
   {
      if (static::$__type_set === false)
         static::mime(Context::$conf['mime_type']);
      
      static::$__content = ob_get_contents();
      ob_clean();
      
      extract(static::$__captured);      
      require(Context::$conf['template']);
   }
      
   public static function mime($type, $encoding = ABLE_DEFAULT) 
   {
      if ($encoding === null || $encoding === false) 
         return static::mime_bin($type);
      
      if ($encoding === ABLE_DEFAULT)
         $encoding = Context::$conf['encoding'];
      
      $header = 'Content-Type: %s; charset=%s';
      $header = sprintf($header, $type, $encoding);
      static::$__type_set = true;
      header($header);
   }
   
   public static function mime_bin($type) 
   {
      $header = 'Content-Type: %s';
      $header = sprintf($header, $type);
      static::$__type_set = true;
      header($header);
   }
   
   // capture content from file
   public static function capture_file($file, $name = ABLE_DEFAULT)
   {
      ob_start();
      require($file);
      $out = ob_get_contents();
      static::$__captured[$name] = $out;
      ob_end_clean();
      return $out;      
   }
   
   // start buffer to capture content
   public static function capture($name = ABLE_DEFAULT, $value = null)
   {
      if ($value !== null)
         return static::$__captured[$name] = $value;
      
      array_push(static::$__active_captures, $name);
      ob_start();
   }
   
   // end buffer 
   public static function end()
   {
      $out = ob_get_contents();
      $name = array_pop(static::$__active_captures);
      static::$__captured[$name] = $out;
      ob_end_clean();
      return $out;
   }
}

