<?php
namespace TJM;
use Exception;
use PDO;
use PDOStatement;
use TJM\DB\Query;
use TJM\DB\Statement;

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
	protected $options = array(
		PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION
	);

	//--ssh tunnel
	protected $sshID; //--ssh 'user@server' to connect to.  Don't need user if specified in ssh config
	protected $sshDBConnection = '127.0.0.1:3306'; //--remote `IP:Port` to connect to from remote server
	protected $tunnel;

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
	public function __destruct(){
		$this->closeTunnel();
	}
	public function getConnection(){
		if(!$this->connection){
			$this->openTunnel();
			$this->connection = new PDO($this->dsn, $this->user, $this->password, $this->options);
		}
		return $this->connection;
	}
	protected function execute($statement, $params = null){
		if(!($statement instanceof Statement)){
			$statement = $this->createStatment($statement);
		}
		return $this->try(function() use($statement, $params){
			return $statement->execute($params);
		}, function() use($statement, $params){
			$this->prepare($statement);
			return $this->execute($statement, $params);
		});
	}
	public function prepare($statement){
		if(!($statement instanceof Statement)){
			$statement = $this->createQueryStatement($statement);
		}
		$statement->setStatement($this->try(function() use($statement){
			return $this->getConnection()->prepare($statement->getSql());
		}));
		return $statement;
	}
	public function query($statement, $params = array()){
		if(!($statement instanceof Statement)){
			$statement = $this->createQueryStatement($statement);
		}
		if(!isset($params) && $statement->hasParameters()){
			$params = $statement->getParameters();
		}
		if($params){
			if(!$statement->hasStatement()){
				$this->prepare($statement);
			}
			$this->execute($statement, $params);
			return $statement;
		}else{
			return $this->try(function() use($statement){
				$statement->setStatement($this->getConnection()->query($statement->getSql()));
				$statement->setResult(true);
				return $statement;
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
	protected function createQueryStatement($opts = null){
		return $this->createStatment(Query::create($opts));
	}
	protected function createStatment($opts = null){
		return new Statement($opts);
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

	//==ssh tunnel
	protected function closeTunnel(){
		if(!empty($this->tunnel)){
			$stat = proc_get_status($this->tunnel);
			posix_kill($stat['pid'], SIGKILL);
			proc_close($this->tunnel);
			unset($this->tunnel);
		}
	}
	protected function needTunnel(){
		return !empty($this->sshID) && empty($this->tunnel);
	}
	protected function openTunnel(){
		if($this->needTunnel()){
			$this->tunnel = proc_open("ssh {$this->sshID} -L 8306:{$this->sshDBConnection} -N", [], $nope);
			//--sleep to ensure tunnel is set up by the time connection is set up
			usleep(1000000);
			return true;
		}
		return false;
	}
}
