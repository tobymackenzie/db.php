<?php
namespace TJM\DB;
use Exception;
class Query{
	protected $params;
	protected $sql;
	public function __construct($query = null){
		if(is_string($query)){
			$this->setSql($query);
		}else{
			$this->set($query);
		}
	}
	public function __toString(){
		return $this->getSql();
	}
	public function getParameters(){
		return $this->params ?: array();
	}
	public function hasParameters(){
		return (bool) $this->params;
	}
	public function setParameters($value = null){
		$this->params = $value;
	}
	protected function buildSql(){
		return null;
	}
	public function getSql(){
		if(is_string($this->sql)){
			return $this->sql;
		}else{
			return $this->buildSql();
		}
	}
	public function setSql($value){
		$this->sql = $value;
	}
}
