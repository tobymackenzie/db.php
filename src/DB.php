<?php
namespace TJM\DB;
use Exception;
use PDO;
use PDOStatement;
class DB{
	protected $connection;
	protected $password;
	protected $dsn;
	protected $maxReconnectTries = 100;
	protected $reconnectErrors = [
		1317 // interrupted
		,2002 // refused
		,2006 // gone away
	];
	protected $reconnectTries = 0;
	protected $reconnectDelay = 400; // in ms
	protected $user;
	protected $options = Array(
		PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION
	);
	public function __construct($dsnOrOpts, $user = null, $password = null, $options = null){
		if($options){
			$this->options = $options;
			if(is_array($dsnOrOpts)){
				unset($dsnOrOpts['options']);
			}
		}
		if($password){
			$this->password = $password;
			if(is_array($dsnOrOpts)){
				unset($dsnOrOpts['password']);
			}
		}
		if($user){
			$this->user = $user;
			if(is_array($dsnOrOpts)){
				unset($dsnOrOpts['user']);
			}
		}
		if(is_array($dsnOrOpts)){
			foreach($dsnOrOpts as $key=> $value){
				$this->$key = $value;
			}
		}else{
			$this->dsn = $dsnOrOpts;
		}
	}
	public function getConnection(){
		if(!$this->connection){
			$this->connection = new PDO($this->dsn, $this->user, $this->password, $this->options);
		}
		return $this->connection;
	}
	protected function execute($query, $params = null){
		if(!($query instanceof Statement)){
			$query = $this->createStatment($query);
		}
		return $this->try(function() use($query, $params){
			return $query->execute($params);
		}, function() use($query, $params){
			$this->prepare($query);
			return $this->execute($query, $params);
		});
	}
	public function prepare($query){
		if(!($query instanceof Statement)){
			$query = $this->createStatment($query);
		}
		$query->setStatement($this->try(function() use($query){
			return $this->getConnection()->prepare($query->getSql());
		}));
		return $query;
	}
	public function query($query, $params = Array()){
		if(!($query instanceof Statement)){
			$query = $this->createStatment($query);
		}
		if($params){
			if(!$query->hasStatement()){
				$this->prepare($query);
			}
			$this->execute($query, $params);
			return $query;
		}else{
			return $this->try(function() use($query){
				$query->setStatement($this->getConnection()->query($query->getSql()));
				$query->setResult(true);
				return $query;
			});
		}
	}
	protected function reconnect(){
		$connected = false;
		while(!$connected && $this->reconnectTries < $this->maxReconnectTries){
			usleep($this->reconnectDelay * 1000);
			++$this->reconnectTries;
			$this->connection = null;
			try{
				if($this->getConnection()){
					$connected = true;
				}
			}catch(Exception $e){}
		}
		if(!$connected){
			throw $e;
		}
	}
	public function createStatment($query = null){
		return new Statement($query);
	}
	protected function try(Callable $do, Callable $onReconnect = null, Callable $onFail = null){
		try{
			return $do();
		}catch(Exception $e){
			if(isset($e->errorInfo) && in_array($e->errorInfo[1], $this->reconnectErrors)){
				try{
					$this->reconnect();
					if($onReconnect){
						return $onReconnect();
					}else{
						return $do();
					}
				}catch(Exception $e2){}
			}
			if($onFail){
				return $onFail();
			}else{
				throw $e;
			}
		}
	}
}
