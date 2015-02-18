<?php

class Date
{
   public $timestamp;
   
   private static $__periods = array(
      // the values for year and month are for 365 and 30 days respectively
      array('name' => 'year', 'name_plural' => 'years', 'divisor' => 31536000),
      array('name' => 'month', 'name_plural' => 'months', 'divisor' => 2592000),
      array('name' => 'day', 'name_plural' => 'days', 'divisor' => 86400),
      array('name' => 'hour', 'name_plural' => 'hours', 'divisor' => 3600),
      array('name' => 'minute', 'name_plural' => 'minutes', 'divisor' => 60),
      array('name' => 'second', 'name_plural' => 'seconds', 'divisor' => 1),
   );
   
   public function __construct($timestamp)
   {
      $this->timestamp = $timestamp;
   }
   
   public function relative()
   {
      $difference = time() - $this->timestamp;
      if ($difference === 0) return 'now';
      $absolute = abs($difference);
      
      for ($i = 0, $c = count(static::$__periods); $i < $c; $i++) 
      {
         $divisor = static::$__periods[$i]['divisor'];
         
         if ($absolute >= $divisor)
         {
            $rounded = (int) round($absolute / $divisor);
            $name = ($rounded === 1 ? static::$__periods[$i]['name'] : 
               static::$__periods[$i]['name_plural']);
            
            return ($difference > 0 ? 
               sprintf('%s %s ago', $rounded, $name) : 
               sprintf('%s %s from now', $rounded, $name)); 
         }
      }
   }
}

