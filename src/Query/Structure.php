<?php
namespace TJM\DB\Query;
use InvalidArgumentException;
class Structure{
	protected $value;
	public function __construct($value = null){
		if($value){
			$this->value = $value;
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
			return $this;
		}elseif(is_string($set)){
			$call = 'set' . ucfirst($set);
			if(method_exists($this, $call)){
				return call_user_func(array($this, $call), $value);
			}elseif(is_array($this->value)){
				$this->value[$set] = $value;
				return $this;
			}
		}
		throw new InvalidArgumentException(static::class . '::set(): handling argument of ' . json_encode($set) . ' not supported.');
	}
	protected function buildSql(){
		if(is_array($this->value)){
			$sql = '';
			foreach($this->value as $key=> $value){
				if($sql){
					$sql .= ' ';
				}
				if(is_numeric($key)){
					$sql .= $value;
				}else{
					$sql .= "{$key} {$value}";
				}
			}
			return $sql;
		}else{
			return $this->value;
		}
	}
	public function getSql(){
		if(is_string($this->value)){
			return $this->value;
		}else{
			return $this->buildSql();
		}
	}
	public function setSql($value){
		return $this->setValue($value);
	}
	public function setValue($value){
		$this->value = $value;
		return $this;
	}
}
