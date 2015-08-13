<?php

interface UserInterface
{
   public static function authenticate($identifier, $password);
   // public static function create($identifier, $password);
   public static function exists($identifier);
   public static function instance_from_db($record); 
}

