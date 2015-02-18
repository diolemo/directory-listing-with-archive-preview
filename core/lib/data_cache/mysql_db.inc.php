<?php

require_once('core/lib/data_cache/__interface.php');

class DataCache implements DataCacheInterface
{
   private $db;
   private $conf = array();
   private $defaults = array(
      // the database table name
      'table' => 'able_data',
      // delete frequency (1 / delete_freq)
      'delete_freq' => 20,
   );
   
   public function __construct($conf = array())
   {
      if (!($conf instanceof MYSQL_Database))
      {
         $this->conf = array_merge($this->defaults, $conf);
         $this->db = new MySQL_Database($conf);
      }
      else
      {
         $this->conf = $this->defaults;
         $this->db = $conf;
      }
      
      if (rand(1, $this->conf['delete_freq']) === 1)
         $this->delete_expired();
   }
   
   public function delete_expired()
   {
      $now = time();
      $tbl = $this->conf['table'];
      $sql = "delete from {$tbl} where (expires != 0) 
         and (last_modified + expires < {$now})";
         
      $this->db->call($sql);
   }
   
   public function delete($name)
   {
      $tbl = $this->conf['table'];
      $sql = "delete from {$tbl} where name = ?";
      
      $this->db->call($sql, $name);
   }
     
   public function read($name)
   {
      $tbl = $this->conf['table'];
      $sql = "select value from {$tbl} where name = ?";
      
      return $this->db->value($sql, $name);
   }
   
   public function write($name, $value, $expires = 86400)
   {
      $now = time();
      $tbl = $this->conf['table'];
      $sql = "insert into {$tbl} (name, value, last_modified, expires)
         values (?, ?, {$now}, ?) on duplicate key update
         value = ?, last_modified = {$now}, expires = ?";      
      
      return $this->db->call($sql, array($name, $value, 
         $expires, $value, $expires));
   }
}

