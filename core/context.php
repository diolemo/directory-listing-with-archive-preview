<?php

class Context
{
   const ENV_DEVELOPMENT = 32767;
   const ENV_TESTING     = 7;
   const ENV_PRODUCTION  = 0;

   public static $cache;
   public static $conf;
   public static $db;
   public static $session;
}

