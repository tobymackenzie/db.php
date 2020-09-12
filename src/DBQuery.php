<?php
namespace TJM\DB;
use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use PDO;
use PDOStatement;
class DBQuery{
	protected $params;
	protected $query;
	protected $result;
	protected $statement;
	public function __construct($opts = null){
		if(is_string($opts)){
			$this->setQuery($opts);
		}elseif($opts){
			$this->set($opts);
		}
	}
	public function __call($name, $args){
		if(method_exists($this->statement, $name)){
			return call_user_func_array(Array($this, $name), $args);
		}else{
			throw new BadMethodCallException("Call to undefined method `DBQuery::{$name}()`");
		}
	}
	public function execute(array $params = null){
		if($params === true && $this->hasParameters()){
			$params = $this->getParameters();
		}else{
			$this->setParameters($params);
		}
		$this->setResult($this->getStatement()->execute($params));
		return $this->getResult();
	}
	public function fetch(int $style = null, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0){
		return $this->getStatement()->fetch($style, $cursorOrientation, $cursorOffset);
	}
	protected function set($set, $value = null){
		if(is_string($set)){
			$this->$set = $value;
		}elseif($set instanceof PDOStatement){
			$this->setStatement($set);
			$this->setQuery($set->queryString);
		}elseif($set instanceof DBQuery){
			$this->setQuery($set->getQuery());
			$this->setResult($set->getResult());
			$this->setStatement($set->getStatement());
		}elseif(is_array($set)){
			foreach($set as $key=> $value){
				$this->set($key, $value);
			}
		}else{
			throw new InvalidArgumentException(static::class . '::set(): handling argument of ' . json_encode($set) . ' not supported.');
		}
	}
	public function getParameters(){
		return $this->params ?: Array();
	}
	public function hasParameters(){
		return (bool) $this->params;
	}
	public function setParameters($value = null){
		$this->params = $value;
	}
	public function getQuery(){
		return $this->query;
	}
	public function setQuery($value = null){
		$this->query = $value;
	}
	public function getResult(){
		return $this->result;
	}
	public function setResult($result = null){
		$this->result = $result;
	}
	public function getSql(){
		if($this->query){
			return $this->query;
		}elseif($this->statement){
			return $this->statement->queryString;
		}else{
			return null;
		}
	}
	public function getStatement(){
		return $this->statement;
	}
	public function hasStatement(){
		return (bool) $this->statement;
	}
	public function setStatement(PDOStatement $statement = null){
		$this->statement = $statement;
	}
}
