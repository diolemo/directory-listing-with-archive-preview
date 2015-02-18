<?php

require_once('core/lib/data_cache/__interface.php');

class DataCache extends Memcache implements DataCacheInterface
{
   private $conf;   
   private $defaults = array(   
      // compress content
      'use_zlib' => true,
      // memcache server hostname
      'host' => '127.0.0.1',
      // memcache server port
      'port' => 11211,
   );
   
   public function __construct($conf = array())
   {
      $this->conf = array_merge($this->defaults, $conf);
      $res = $this->pconnect($this->conf['host'], $this->conf['port']);
      if ($res === false) throw new Exception();
   }
   
   public function delete($name)
   {
      parent::delete($name);
   }
     
   public function read($name)
   {
      return $this->get($name);
   }
   
   public function write($name, $value, $expires = 86400)
   {
      $bits = $this->conf['use_zlib'] ? MEMCACHE_COMPRESSED : 0;
      return $this->set($name, $value, $bits, $expires);
   }
}

