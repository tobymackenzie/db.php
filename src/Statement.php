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
			return call_user_func_array(Array($this, $name), $args);
		}else{
			throw new BadMethodCallException("Call to undefined method `Statement::{$name}()`");
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
		return $this->hasQuery() ? $this->getQuery()->getParameters() : Array();
	}
	public function hasParameters(){
		return $this->hasQuery() ? $this->getQuery()->hasParameters() : false;
	}
	public function setParameters($value = null){
		$this->hasQuery() ? $this->getQuery()->getParameters() : Array();
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
