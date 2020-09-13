<?php
namespace TJM\DB;
use Exception;
use TJM\DB\Query\Join;
use TJM\DB\Query\Structure;
class Query extends Structure{
	protected $alias = 'this';
	protected $command = 'SELECT';
	protected $from;
	protected $groupBy;
	protected $having;
	protected $joins;
	protected $limit;
	protected $orderBy;
	protected $params = array();
	protected $values = '*';
	protected $where;
	public function setAlias($value){
		$this->alias = $value;
	}
	public function setCommand($value){
		$this->command = $value;
	}
	public function setFrom($value){
		$this->from = $value;
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
	public function setOrderBy($value){
		$this->orderBy = $value;
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
	protected function buildSql(){
		$placeholderI = 0;
		$sql = "{$this->command}";
		if(is_array($this->values)){
			$sql .= ' ' . implode(', ', $this->values);
		}elseif($this->values){
			$sql .= ' ' . $this->values;
		}
		if($this->from){
			$sql .= " FROM {$this->from} {$this->alias}";
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
		return $sql;
	}
	public function setValues($value){
		$this->values = $value;
	}
	public function setWhere($value){
		$this->where = $value;
	}
}
