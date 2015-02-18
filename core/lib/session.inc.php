<?php

class Session
{   
   private static $__instance;
   private $commit_calls = array();
   private $data = array();
   private $token;      
   
   public static function __commit()
   {
      return static::$__instance->commit();
   }
   
   public static function __on_commit($function)
   {
      return static::$__instance->on_commit($function);
   }
   
   public static function __read($name)
   {
      return static::$__instance->read($name);
   }
   
   public static function __token()
   {
      return static::$__instance->token;
   }
   
   public static function __write($name, $value)
   {
      return static::$__instance->write($name, $value);
   }
   
   public static function __delete($name)
   {
      return static::$__instance->delete($name);
   }
   
   public static function create_token()
   {
      $sources = array();
      $sources[] = microtime(true);
      for ($i = 0; $i < 10; $i++)
         $sources[] = mt_rand();
      return md5(implode($sources));
   }
   
   public static function start()
   {
      $token = Cookie::read(Context::$conf['session_cookie']);
      if ($token === null) $token = static::create_token();
      Cookie::write(Context::$conf['session_cookie'], $token, 0, '/');
      return static::$__instance = new Session($token);
   }
   
   public function __construct($token) 
   {
      $this->token = $token;
      $this->reload();
   }
   
   public function commit()
   {
      for ($i = 0, $c = count($this->commit_calls); $i < $c; $i++)
         call_user_func($this->commit_calls[$i], $this);      
      Context::$cache->write($this->token, serialize($this->data), 
         Context::$conf['session_timeout']);
   }
   
   public function on_commit($function)
   {
      return $this->commit_calls[] = $function;
   }
   
   public function read($name)
   {
      if (!isset($this->data[$name])) return null;
      return $this->data[$name];
   }
   
   public function reload()
   {
      $data_str = Context::$cache->read($this->token);
      if ($data_str === false) return;
      $this->data = unserialize($data_str);
   }
   
   public function write($name, $value)
   {
      $this->data[$name] = $value;
   }
   
   public function delete($name)
   {
      if (!isset($this->data[$name])) return;
      unset($this->data[$name]);
   }
}

