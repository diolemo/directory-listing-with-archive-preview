<?php

// call the __init() method once all libs load
$__able_lib_callbacks[] = 'SQLoader::__init';

abstract class SQLoader
{
   public static $__cached = array();
   
   public static function __init()
   {
      $file = implode(DIRECTORY_SEPARATOR, array(
         Context::$conf['sqloader_dir'], '__sqloader_cache.php'));
      if (is_file($file)) require($file);
   }
   
   // emulate a boolean full text search 
   // * [^a-z0-9\-] => used as wildcard
   // @match the columns to match against
   // @against the search terms as a string
   public static function fulltext($match, $against)
   { 
      if (!is_array($match)) $match = array($match);
      $raw_terms = explode(' ', $against);
      $sql_conds = array();
      
      // convert each term to sql like
      for ($i = 0, $c = count($raw_terms); $i < $c; $i++) 
      {
         $bool = true;
         $term = trim($raw_terms[$i]);
         $cond = array();
         
         if (strlen($term) === 0) continue;
         
         // check for + or - at the start
         if (preg_match('#^[\+\-]#', $term))
         {
            $bool = $term[0] === '+';
            $term = substr($term, 1);
         }
         
         // convert any non-standard character to wildcard
         // * does not match the standard fulltext behaviour
         // * this also prevents sql injection
         $term = preg_replace('#[^a-z0-9\-]#i', '%', $term);
         
         // loop over each column in the match array
         for ($i2 = 0, $c2 = count($match); $i2 < $c2; $i2++)
            // generate the sql logic for one column
            $cond[] = $bool ? " {$match[$i2]} like '%{$term}%' ": 
               " {$match[$i2]} not like '%{$term}%' ";
         
         // require that all columns exclude when -term
         $cond = implode(($bool ? 'or' : 'and'), $cond);
         $sql_conds[] = "({$cond})";
      }
      
      if (count($sql_conds) === 0) return 1;
      return implode(' and ', $sql_conds);
   }
   
   // @svars replaces ${{name}} with the
   // value specified in svars[name]
   public static function load($name, $svars = array())
   {
      return static::read($name, $svars);
   }
   
   // @svars replaces ${{name}} with the
   // value specified in svars[name]
   public static function read($name, $svars = array())
   {
      if (isset(static::$__cached[$name]))
         return static::$__cached[$name];
      
      $filename = sprintf('%s.sql', $name);
      $file = implode(DIRECTORY_SEPARATOR, array(
         Context::$conf['sqloader_dir'], $filename));
      
      if (!is_file($file)) throw new Exception();      
      $src = file_get_contents($file);
      
      // svars in the form ${{name}}
      foreach ($svars as $k => $v)
         $src = str_replace("\${{{$k}}}", $v, $src);
      
      return $src;
   }
}

