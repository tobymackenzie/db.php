<?php
namespace TJM\DB\Query;
class Join extends Structure{
	protected $alias;
	protected $on;
	protected $table;
	protected $type = 'INNER';
	public function __construct($opts = null){
		if(is_string($opts) && preg_match('/^[\w_]+$/', $opts)){
			$this->table = $opts;
		}else{
			parent::__construct($opts);
		}
	}
	public function setAlias($value){
		$this->alias = $value;
	}
	public function setOn($value){
		$this->on = $value;
	}
	protected function buildSql(){
		$type = $this->type ?: 'JOIN';
		if(stripos($type, 'join') === false){
			$type .= " JOIN";
		}
		$sql = $type;
		$sql .= " {$this->table}";
		if($this->alias){
			$sql .= " {$this->alias}";
		}
		if($this->on){
			$sql .= " ON {$this->on}";
		}
		return $sql;
	}
	public function setTable($value){
		$this->table = $value;
	}
	public function setType($value){
		$this->type = $value;
	}
}
