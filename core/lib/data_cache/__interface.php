<?php

interface DataCacheInterface
{
   // the constructor with optional config
   public function __construct($conf = array());
   
   // delete item with <name>
   public function delete($name);
   // load item with <name> 
   public function read($name);
   // store item with <name>, <value> for <expires> seconds
   public function write($name, $value, $expires = 0);
}

