<?php
namespace TJM\DB;
use Exception;
use TJM\DB\Query\Join;
use TJM\DB\Query\GenericQuery;
use TJM\DB\Query\ObjectQuery;
use TJM\DB\Query\Structure;
class Query extends Structure{
	protected $params = array();

	static public function create($value){
		if($value){
			if(is_string($value)){
				return new static($value);
			}elseif(is_array($value)){
				if(
					isset($value['command'])
					|| (isset($value['table']) && !(isset($value['select']) || isset($value['update']) || isset($value['delete'])))
					|| isset($value['joins'])
					|| isset($value['value'])
				){
					return new GenericQuery($value);
				}else{
					return new static($value);
				}
			}
		}
		return new static($value);
	}

	public function setParameter($name, $value){
		$this->params[$name] = $value;
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
}
