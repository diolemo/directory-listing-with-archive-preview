<?php

class MYSQL_Database extends MYSQLi
{
   private $conf;
   private $defaults = array(
      // database server hostname
      'host' => '127.0.0.1',        
      // database name
      'name' => 'database_name',   
      // database password       
      'pass' => 'database_pass',
      // database connection port 
      'port' => 3306,    
      // database username                 
      'user' => 'database_user',    
      // do not show errors      
      'silent' => false,                    
   );
   
   const T_INTEGER  = 'i';
   const T_DOUBLE   = 'd';
   const T_STRING   = 's';
   const T_BLOB     = 'b';
   
   public function __construct($conf)
   {
      parent::init();
      parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
      
      $this->conf = array_merge($this->defaults, $conf);
      extract($this->conf);

      // persistant connection
      if (PHP_SAPI !== 'cli')
        $host = sprintf('p:%s', $host);

      if (!(@$this->real_connect($host, $user, $pass, $name, $port)))
        throw new Exception("mysqldb: database connect failed");
   }
   
   private static function type($value)
   {
      if (is_integer($value)) return static::T_INTEGER;
      if (is_float($value)) return static::T_INTEGER;
      if (is_string($value)) return static::T_STRING;
      return static::T_BLOB;
   }

   // output error
   public function error()
   {
      echo $this->error;
   }

   // format a date/time into timestamp
   public function timestamp($date)
   {
      return date('Y-m-d H:i:s', strtotime($date));
   }
   
   // start a new transaction
   public function start()
   {
      $this->query('start transaction');
   }
   
   // commit transaction
   public function commit()
   {
      $this->query('commit');
   }
   
   // rollback transaction
   public function rollback()
   {
      $this->query('rollback');
   }
   
   // set variable (use within transaction)
   public function set($name, $value, $type = null)
   {
      if ($type === null) 
         $type = static::type($value);               
      $sql = "set @${name} = ?";
      $this->call($sql, $type, $value);
   }
   
   // return last auto id
   public function insert_id()
   {
      $row = $this->row('select last_insert_id() as id');
      if ($row !== null) return $row['id'];
      return null;
   }
   
   // executes a raw sql query and allows 
   // for the fetching of rows 1 by 1
   public function raw_exec($sql, $type_str, $data_args=null)
   {
      $stmt = $this->prepare($sql);
      
      if ($this->error && !$this->conf['silent'])
        trigger_error($this->error, E_USER_WARNING);

      if (!$stmt)
        return null;

      // if we have no data args but appear to have
      // a type str then it is the case that the 
      // data args are given and we must guess
      // the data type for all given values
      if ($type_str !== null && $data_args === null)
      {
         $data_args = $type_str;
         $types = array();         
         if (!is_array($data_args))
            $data_args = array($data_args);
         foreach ($data_args as $arg)
            $types[] = static::type($arg);
         $type_str = implode($types);
      }

      if ($data_args !== null && $type_str !== null)
      {
         if (!is_array($data_args))
            $data_args = array($data_args);
         
         $raw_data_args = $data_args;
         $data_args = array();
         // convert the array of values to an array of references
         for ($i = 0, $c = count($raw_data_args); $i < $c; $i++)
            $data_args[$i] = &$raw_data_args[$i];
         
         array_unshift($data_args, $type_str);
         call_user_func_array(array($stmt, 'bind_param'), $data_args);
      }        
        
      if ($this->error && !$this->conf['silent'])
        trigger_error($this->error, E_USER_WARNING);
        
      $exec = $stmt->execute();

      if ($this->error && !$this->conf['silent'])
         trigger_error($this->error, E_USER_WARNING);
 
      if (!$exec)
        return null;
      
      $meta = $stmt->result_metadata();
      
      if ($meta)
      {
         $fields = $meta->fetch_fields();
         
         $bind_result_args = array();
         $row = array();      
   
         foreach ($fields as $field)
         {
            $row[($field->name)] = null;
            $bind_result_args[] = &$row[($field->name)];
         }
      
         // bind results to $result_row elements
         call_user_func_array(array($stmt, 'bind_result'), $bind_result_args);
         
         // construct handle as array of the required variables
         return array('meta' => &$meta, 'row' => &$row, 'stmt' => &$stmt);
      }
      
      $rows = $stmt->affected_rows;
      $stmt->close();
      
      return $rows;
   }
   
   public function raw_read($handle)
   {
      // case when we don't receive expected array
      if (!isset($handle['meta'])) return null;
      if ($handle['stmt']->fetch() !== true) return null;
      
      $row = array();
      
      // we have to copy as the elements
      // are references to fixed variables
      foreach ($handle['row'] as $k => $v)
        $row[$k] = $v;
      
      return $row;
   }
   
   public function raw_close($handle)
   {
      // close the meta and statement
      if (isset($handle['meta']))
        $handle['meta']->free_result();
      $handle['stmt']->close();
   }
   
   // fetch a limited number of rows (or all) as array
   public function call($sql, $type_str=null, $data_args=null, $limit=PHP_INT_MAX)
   {
      $handle = $this->raw_exec($sql, $type_str, $data_args);
      
      // return affected rows
      if (!isset($handle['meta']))
        return $handle;
      
      $fetched = 0;
      $rows = array();
   
      // loop over reading rows into an array
      while (($row = $this->raw_read($handle)) !== null 
      && $fetched++ < $limit)
      {
         $rows[] = $row;
      }
      
      $this->raw_close($handle);
      
      if (count($rows) == 0) 
        return null;
      
      return $rows;
   }
   
   // execute multiple sql statements
   public function multi($sql)
   {
      if (!$this->multi_query($sql))
         return false;
      
      while ($this->more_results())
      {
         $this->store_result();
         if (!$this->next_result()) 
            return true;
      }
   }

   // fetch all rows as array
   public function all($sql, $type_str=null, $data_args=null)
   {
      $result = $this->call($sql, $type_str, $data_args);
      if (!is_array($result)) $result = array();
      return $result;
   }

   // fetch single row as array
   public function row($sql, $type_str=null, $data_args=null)
   {
      $result = $this->call($sql, $type_str, $data_args, 1);      
      if (is_array($result) && isset($result[0]))
        return $result[0];         
      return null;
   }
   
   // fetch single field from single row
   public function field($sql, $field, $type_str=null, $data_args=null)
   {
      $result = $this->call($sql, $type_str, $data_args, 1);      
      if (is_array($result) && isset($result[0]))
        return $result[0][$field];
      return null;
   }
   
   // fetch single field from all available rows
   public function field_all($sql, $field, $type_str=null, $data_args=null)
   {
      $result = $this->all($sql, $type_str, $data_args);
      $values = array();      
      foreach ($result as $v) 
         $values[] = $v[$field];      
      return $values;
   }
   
   // fetch single field from single row (auto)
   public function value($sql, $type_str=null, $data_args=null)
   {
      $result = $this->call($sql, $type_str, $data_args, 1);      
      if (is_array($result) && isset($result[0]))
         return array_pop($result[0]);
      return null;
   }
   
   // fetch single field from all available rows (auto)
   public function value_all($sql, $type_str=null, $data_args=null)
   {
      $result = $this->all($sql, $type_str, $data_args);
      $values = array();
      foreach ($result as $v) 
         $values[] = array_pop($v);
      return $values;
   }
   
   // test for the existence of at least 1 row
   public function test($sql, $type_str=null, $data_args=null)
   {
      $result = $this->call($sql, $type_str, $data_args, 1);      
      if (is_array($result) && isset($result[0]))
        return true;
      return false;
   }
}

