<?php

	/**
	 * This file is part of MVC Core framework
	 * (c) Matija Božić, www.matijabozic.com
	 *
	 * This Database class adds more functionality to PHP PDO.
	 * It adds new methods to PDO, but enables you to use this class joust
	 * as you would use PHP PDO object. 
	 * 
	 * Composition over inheritance!!
	 * 
	 * @package    Database
	 * @author     Matija Božić <matijabozic@gmx.com>
	 * @license    MIT - http://opensource.org/licenses/MIT
	 */
	
	namespace Core\Database;
	
	class Database
	{
		/**
		 * Database Configuration
		 * 
		 * @access  protected
		 * @var     array
		 */
		
		protected $config;
		
		/**
		 * PDO Object instance
		 * 
		 * @access  protected
		 * @var     object
		 */
		
		protected $pdoo;
		
		/**
		 * Database object constructor
		 * 
		 * @access  public
		 * @param   array  
		 * @return  void
		 */
		
		public function __construct(array $config = null)
		{
			if(isset($config)) {
				$this->config['dsn'] = "{$config['driver']}:host={$config['host']};dbname={$config['name']}";
				$this->config['username'] = $config['username'];
				$this->config['password'] = $config['password'];
				$this->config['options'] = $config['options']; 
			}
		}
		
		public function __destruct()
		{
			$this->config = null;
			$this->pdoo = null;
		}
		
				
		/**
		 * Add configuration through setter method
		 * 
		 * @access  public
		 * @param   array
		 * @return  void
		 */
		 
		public function setConfig(array $config)
		{
			$this->config['dsn']      = "{$config['driver']}:host={$config['host']};dbname={$config['name']}";
			$this->config['username'] = $config['username'];
			$this->config['password'] = $config['password'];
			$this->config['options']  = $config['options']; 
		}		
		
		/**
		 * Starts database connection, instanciates new PDO object with defined configuration
		 * 
		 * @access  public
		 * @return  void
		 */
		
		protected function connect()
		{
			if($this->pdoo) {
				return;	
			}
			
			try {
				$this->pdoo = new \PDO(
					$this->config["dsn"], 
					$this->config["username"], 
					$this->config["password"], 
					$this->config["options"]
				);
			} catch(\PDOException $e) {
				echo 'Database connection error: ' . $e->getMessage() . '<br />';
			}
		}

		/**
		 * Inserts new data into database
		 * 
		 * @access  public
		 * @param   string 
		 * @param   array 
		 * @return  bool
		 */
		
		public function insert($table, $data)
		{
			$this->connect();
			
			$cols = array();
			$pchs = array();
			
			foreach($data as $columnName => $columnValue) {
				$cols[] = $columnName;
				$pchs[] = '?';
			}
			
			$query = 'INSERT INTO ' . $table . ' (' . implode(', ', $cols) . ') ' . 'VALUES' . ' (' . implode(', ', $pchs) . ')';
			
			$params = array_values($data);
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($query);
			
			foreach($params as $key => $value) {
			    $pdos->bindValue($key, $value);
			}
			
			if($pdos->execute()) {
			    return $this->pdoo->lastInsertId();
			} 
			
			return false;
		}
		
		/** 
		 * Updates table rows
		 * 
		 * @access  public
		 * @param   string  
		 * @param   array   
		 * @param   array   
		 * @return  number of affected rows | false
		 */
		
		public function update($table, $data, $identifier)
		{
			$this->connect();
			
			$set = array();
			
			foreach ($data as $columnName => $columnValue) {
				$set[] = $columnName . ' = ?';
			}
			
			$query = 'UPDATE ' . $table . ' SET ' . implode(', ', $set) . ' WHERE ' . implode(' = ? AND ', array_keys($identifier)) . ' = ?';  
			
			$params = array_merge(array_values($data), array_values($identifier));
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($query);
			
			foreach($params as $key => $value)
			{
				$pdos->bindValue($key, $value);
			}
			
			if($pdos->execute())
			{
				return $pdos->rowCount();
			}
			
			return false;
		}
		
		/**
		 * Deletes table rows
		 * 
		 * @access  public 
		 * @param   string 
		 * @param   array 
		 * @return  number of deleted rows | false
		 */
		
		public function delete($table, $identifier)
		{
			$this->connect();
			
			$criteria = array();
			
			foreach(array_keys($identifier) as $columnName) {
				$criteria[] = $columnName . ' = ?';
			}
			
			$query = 'DELETE FROM ' . $table . ' WHERE ' . implode(' AND ', $criteria);
			
			$params = array_values($identifier);
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($query);
			
			foreach($params as $key => $value) {
				$pdos->bindValue($key, $value);
			}
			
			if($pdos->execute()) {
				return $pdos->rowCount();
			}
			
			return false;
		}
		
		/**
		 * Detects PDO Type
		 * 
		 * @access  protected
		 * @param   $value
		 * @return  constant
		 */
		
		protected function detectType($value)
		{
			if(is_string($value)) {
				return \PDO::PARAM_STR;
			}
			 
			else if(is_int($value)) {
			    return \PDO::PARAM_INT;
			}
			
			else if(is_null($value)) {
				return \PDO::PARAM_NULL;
			}
			
			else if(is_bool($value)) {
				return \PDO::PARAM_BOOL;
			}
			
			return false;
		}
		
		/**
		 * Fetches first row as Array
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 * @return  array | false
		 */
		
		public function fetchArray($sql, $params)
		{
			$this->connect();
			
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($sql);
			
			foreach($params as $key => $param) {
				$type = $this->detectType($param);
				$pdos->bindValue($key, $param, $type);
			}
			
			$pdos->execute();
			return $pdos->fetch(\PDO::FETCH_BOTH);
		}
		
		/**
		 * Fetches all rows as Array
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 * @return  array | false
		 */
		
		public function fetchAllArray($sql, $params)
		{
			$this->connect();
			
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($sql);
			
			foreach($params as $key => $param) {
				$type = $this->detectType($param);
				$pdos->bindValue($key, $param, $type);
			}
			
			$pdos->execute();
			return $pdos->fetchAll(\PDO::FETCH_BOTH);
		}		
		
		/**
		 * Fetches first row as Associative array
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 * @return  array | false
		 */
		
		public function fetchAssoc($sql, $params)
		{
			$this->connect();
			
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($sql);
			
			foreach($params as $key => $param) {
				$type = $this->detectType($param);
				$pdos->bindValue($key, $param, $type);
			}
			
			$pdos->execute();
			return $pdos->fetch(\PDO::FETCH_ASSOC);
		}
		
		/**
		 * Fetches all rows as Associative array
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 * @return  array | false
		 */
		
		public function fetchAllAssoc($sql, $params)
		{
			$this->connect();
			
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($sql);
			
			foreach($params as $key => $param) {
				$type = $this->detectType($param);
				$pdos->bindValue($key, $param, $type);
			}
			
			$pdos->execute();
			return $pdos->fetchAll(\PDO::FETCH_ASSOC);
		}		
		
		/**
		 * Fetches first row as object
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 * @return  object | false
		 */
		
		public function fetchObject($sql, $params)
		{
			$this->connect();
			
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($sql);
			
			foreach($params as $key => $param) {
				$type = $this->detectType($param);
				$pdos->bindValue($key, $param, $type);
			}
			
			$pdos->execute();
			return $pdos->fetch(\PDO::FETCH_OBJ);
		}
		
		/**
		 * Fetches all rows as object
		 * 
		 * @access  public
		 * @param   string
		 * @param   array
		 * @return  array | false
		 */
		
		public function fetchAllObject($sql, $params)
		{
			$this->connect();
			
			array_unshift($params, null);
			unset($params[0]);
			
			$pdos = $this->pdoo->prepare($sql);
			
			foreach($params as $key => $param) {
				$type = $this->detectType($param);
				$pdos->bindValue($key, $param, $type);
			}
			
			$pdos->execute();
			return $pdos->fetchAll(\PDO::FETCH_OBJ);
		}
		
		// Use PDO methods

		public function beginTransaction()
		{
			$this->connect();
			return $this->pdoo->beginTransaction();
		}
		
		public function commit()
		{
			$this->connect();
			return $this->pdoo->commit();
		}
		
		public function errorCode()
		{
			$this->connect();
			return $this->pdoo->errorCode();
		}
		
		public function errorInfo()
		{
			$this->connect();
			return $this->pdoo->errorInfo();
		}
		
		public function exec($statement)
		{
			$this->connect();
			return $this->pdoo->exec($statement);
		}
		
		public function getAttribute($attribute)
		{
			$this->connect();
			return $this->pdoo->getAttribute($attribute);
		}
		
		public function getAvailableDrivers()
		{
			$this->connect();
			return $this->pdoo->getAvailableDrivers();
		}
		
		public function inTransaction()
		{
			$this->connect();
			return $this->pdoo->inTransaction();
		}
		
		public function lastInsertId()
		{
			$this->connect();
			return $this->pdoo->lastInsertId();
		}
		
		public function prepare($statement, $driver_options = array())
		{
			$this->connect();
			return $this->pdoo->prepare($statement, $driver_options);
		}
		
		public function query($statement, $fetchStyle = \PDO::FETCH_OBJ)
		{
			$this->connect();
			return $this->pdoo->query($statement, $fetchStyle);
		}
		
		public function quote($string, $parameter_type = \PDO::PARAM_STR)
		{
			$this->connect();
			return $this->pdoo->quote($string, $parameter_type);
		}
		
		public function rollBack()
		{
			$this->connect();
			return $this->pdoo->rollBack();
		}
		
		public function setAttribute($attribute, $value)
		{
			$this->connect();
			return $this->pdoo->setAttribute($attribute, $value);
		}
	}

?>