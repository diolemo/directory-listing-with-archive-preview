<?php

class Feedback
{
   private static $queue = array();
   private static $has_callback = false;
   
   public static function read() 
   {
      if (defined('ABLE_TERMINATED')) return null;
      $feedback = Context::$session->read('able_feedback');
      if ($feedback === null) $feedback = array();
      $feedback = array_merge($feedback, static::$queue);
      if (count($feedback) === 0) return null;
      Context::$session->delete('able_feedback');
      static::$queue = array();
      return $feedback;
   }
   
   public static function set($feedback)
   {
      static::write($feedback);
   }
   
   public static function write($feedback) 
   {
      static::$queue[] = $feedback;
      
      if (static::$has_callback) return;
      static::$has_callback = true;
      
      Context::$session->on_commit(function($session) {
         $session->write('able_feedback', static::$queue);
      });
   }
}

