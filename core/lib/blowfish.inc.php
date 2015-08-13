<?php

class Blowfish
{
   // the number of iterations
   // iterations = 2 ^ COST
   const COST = 07;    
   private $cost;
   
   public static function __hash($input, $hashed = null) 
   {
      $bf = new static(static::COST);
      return $bf->hash($input, $hashed);
   }
   
   public function __construct($cost = null)
   {
      $this->cost = $cost === null ? static::COST : $cost;
   }
   
   public function hash($input, $hashed = null) 
   {
      if ($hashed === null)
      {
         $salt = $this->random_salt();
         return crypt($input, $salt);
      }
      
      return crypt($input, $hashed) === $hashed;
   }
   
   public function random_salt()
   {
      // this could be more made better
      $salt = substr(md5(microtime(true)), 0, 22);
      return sprintf('$2a$%02d$%s', $this->cost, $salt);
   }
}

