<?php
namespace TJM\DB\Query;
use TJM\DB\Query;
class GenericQuery extends Query{
	protected $alias = 'this';
	protected $command = 'SELECT';
	protected $table;
	protected $groupBy;
	protected $having;
	protected $joins;
	protected $limit;
	protected $offset;
	protected $orderBy;
	protected $values;
	protected $where;
	public function __construct($opts = null){
		if(is_string($opts)){
			$this->value = $opts;
		}else{
			$this->set($opts);
		}
	}
	public function setAlias($value){
		$this->alias = $value;
	}
	public function setCommand($value){
		$this->command = $value;
	}
	public function setGroupBy($value){
		$this->groupBy = $value;
	}
	public function setHaving($value){
		$this->having = $value;
	}
	public function setJoins($value){
		$this->joins = $value;
	}
	public function setLimit($value){
		$this->limit = $value;
	}
	public function getParameters(){
		//--must build sql if value is an array so any built in params are there
		if(!is_string($this->value) && empty($this->params)){
			$this->buildSql();
		}
		return parent::getParameters();
	}
	public function hasParameters(){
		//--must build sql if value is an array so any built in params are there
		if(!is_string($this->value) && empty($this->params)){
			$this->buildSql();
		}
		return parent::hasParameters();
	}
	public function setOffset($value){
		$this->offset = $value;
	}
	public function setOrderBy($value){
		$this->orderBy = $value;
	}
	protected function buildSql(){
		$placeholderI = 0;
		$commandType = explode(' ', $this->command);
		$commandType = strtolower($commandType[0]);
		$hasFrom = !in_array($commandType, array('insert', 'update'));
		$hasEarlyValues = $hasFrom;
		$sql = "{$this->command}";
		$values = $this->getValues();
		if($hasEarlyValues){
			if(is_array($values)){
				$sql .= ' ' . implode(', ', $values);
			}elseif($values){
				$sql .= ' ' . $values;
			}
		}
		if($this->table){
			if($hasFrom){
				$sql .= ' FROM';
			}
			$sql .= " {$this->table}";
			if($hasFrom){
				$sql .= " {$this->alias}";
			}
		}
		if(is_array($this->joins)){
			foreach($this->joins as $alias=> $join){
				if(!($join instanceof Join)){
					$join = new Join($join);
				}
				if(!is_numeric($alias)){
					$join->setAlias($alias);
				}
				$sql .= ' ' . $join;
			}
		}elseif($this->joins instanceof Join){
			$sql .= " {$this->joins}";
		}elseif(is_string($this->joins)){
			$joins = $this->joins;
			if(stripos($joins, 'JOIN ') === false){
				$joins = "INNER JOIN {$joins}";
			}
			$sql .= " {$joins}";
		}
		if(!$hasEarlyValues){
			//-! currently only supports insert, update
			$sql .= ' SET ';
			if(is_array($values)){
				$first = true;
				foreach($values as $key=> $value){
					if(!$first){
						$sql .= ', ';
					}else{
						$first = false;
					}
					$sql .= "{$key}";
					if(is_integer($value)){
						$sql .= " = {$value}";
					}elseif(is_null($value)){
						$sql .= ' = NULL';
					}else{
						$sql .= " = \"{$value}\"";
					}
				}
			}else{
				$sql .= $values;
			}
		}
		if($this->where){
			$sql .= " WHERE ";
			if(is_string($this->where)){
				$sql .= $this->where;
			}elseif(is_array($this->where)){
				$wheres = array();
				foreach($this->where as $field=> $where){
					if(is_numeric($field)){
						$wheres[] = "{$where}";
					}else{
						if(strpos($field, ' ') === false){
							$field .= ' =';
						}
						if(substr($where, 0, 1) === ':'){
							$wheres[] = "{$field} {$where}";
						}else{
							$whereKey = 'p' . ++$placeholderI;
							$wheres[] = "{$field} :{$whereKey}";
							$this->setParameter($whereKey, $where);
						}
					}
				}
				$sql .= implode(' AND ', $wheres);
			}
		}
		if($this->groupBy){
			$sql .= " GROUP BY ";
			if(is_array($this->groupBy)){
				$sql .= implode(', ', $this->groupBy);
			}else{
				$sql .= $this->groupBy;
			}
		}
		if($this->having){
			$sql .= " HAVING ";
			if(is_array($this->having)){
				//-! should function like WHERE.  keep it DRY
				$sql .= implode(' AND ', $this->having);
			}else{
				$sql .= $this->having;
			}
		}
		if($this->orderBy){
			$sql .= " ORDER BY ";
			if(is_string($this->orderBy)){
				$sql .= $this->orderBy;
			}elseif(is_array($this->orderBy)){
				$orders = array();
				foreach($this->orderBy as $order=> $direction){
					if(is_numeric($order)){
						$orders[] = "{$direction}";
					}else{
						$orders[] = "{$order} {$direction}";
					}
				}
				$sql .= implode(', ', $orders);
			}
		}
		if($this->limit){
			$sql .= " LIMIT {$this->limit}";
		}
		if($this->offset){
			$sql .= " OFFSET {$this->offset}";
		}
		return $sql;
	}
	public function setTable($value){
		$this->table = $value;
	}
	protected function getValues(){
		if(isset($this->values)){
			return $this->values;
		}elseif(strtolower($this->command) === 'select'){
			return '*';
		}
	}
	public function setValues($value){
		$this->values = $value;
	}
	public function setWhere($value){
		$this->where = $value;
	}
}
