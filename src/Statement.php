<?php
namespace TJM\DB;
use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use PDO;
use PDOStatement;
class Statement{
	protected $query;
	protected $result;
	protected $statement;
	public function __construct($opts = null){
		if(is_string($opts) || $opts instanceof Query){
			$this->setQuery($opts);
		}elseif($opts){
			$this->set($opts);
		}
	}
	public function __call($name, $args){
		if(method_exists($this->statement, $name)){
			return call_user_func_array(array($this, $name), $args);
		}else{
			throw new BadMethodCallException("Call to undefined method `Statement::{$name}()`");
		}
	}
	public function execute(array $params = null){
		if((!isset($params) || $params === true) && $this->hasParameters()){
			$params = $this->getParameters();
		}else{
			$this->setParameters($params);
		}
		//-# using bind so we can support integer values
		foreach($params as $param=> $value){
			if(is_integer($value)){
				$type = PDO::PARAM_INT;
			}elseif(is_null($value)){
				$type = PDO::PARAM_NULL;
			}elseif(is_bool($value)){
				$type = PDO::PARAM_BOOL;
			}else{
				$type = PDO::PARAM_STR;
			}
			$this->getStatement()->bindValue(':' . $param, $value, $type);
		}
		$this->setResult($this->getStatement()->execute());
		return $this->getResult();
	}
	//-! style should be PDO::FETCH_DEFAULT but requires PHP 8.07
	public function fetch(int $style = PDO::FETCH_ASSOC, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0){
		return $this->getStatement()->fetch($style, $cursorOrientation, $cursorOffset);
	}
	protected function set($set, $value = null){
		if(is_string($set)){
			$this->$set = $value;
		}elseif($set instanceof PDOStatement){
			$this->setStatement($set);
			$this->setQuery($set->queryString);
		}elseif($set instanceof Query){
			$this->setQuery($set);
		}elseif($set instanceof Statement){
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
		return $this->hasQuery() ? $this->getQuery()->getParameters() : array();
	}
	public function hasParameters(){
		return $this->hasQuery() ? $this->getQuery()->hasParameters() : false;
	}
	public function setParameters($value = null){
		$this->getQuery()->setParameters($value);
	}
	public function getQuery(){
		return $this->query;
	}
	public function hasQuery(){
		return (bool) $this->query;
	}
	public function setQuery($value = null){
		if(isset($value) && !($value instanceof Query)){
			$value = new Query($value);
		}
		$this->query = $value;
	}
	public function getResult(){
		return $this->result;
	}
	public function setResult($result = null){
		$this->result = $result;
	}
	public function getSql(){
		if($this->hasQuery()){
			return $this->getQuery()->getSql();
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
