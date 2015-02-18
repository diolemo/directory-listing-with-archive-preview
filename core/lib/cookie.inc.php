<?php

abstract class Cookie
{
   public static function read($name)
   {
      if (!isset($_COOKIE[$name])) return null;
      return $_COOKIE[$name];
   }
   
   public static function set($name, $value = 1, $expires = 0, 
   $path = null, $domain = null, $secure = false, $httponly = false)
   {
      return static::write($name, $value, $expires, $path, $domain, $secure, $httponly);
   }
   
   public static function write($name, $value = 1, $expires = 0, 
   $path = null, $domain = null, $secure = false, $httponly = false)
   {
      return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
   }
}

