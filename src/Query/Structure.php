<?php
namespace TJM\DB\Query;
use InvalidArgumentException;
class Structure{
	protected $sql;
	public function __construct($opts = null){
		if(is_string($opts)){
			$this->sql = $opts;
		}elseif(is_array($opts)){
			$this->set($opts);
		}
	}
	public function __toString(){
		return $this->getSql();
	}
	public function set($set, $value = null){
		if(is_array($set)){
			foreach($set as $key=> $value){
				$this->set($key, $value);
			}
		}elseif(is_string($set)){
			return call_user_func(array($this, 'set' . ucfirst($set)), $value);
		}else{
			throw new InvalidArgumentException(static::class . '::set(): handling argument of ' . json_encode($set) . ' not supported.');
		}
	}
	protected function buildSql(){
		return $this->sql;
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
