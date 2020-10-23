<?php
namespace TJM\DB\Query;
class Clause extends Structure{
	public function __construct($opts = null){
		if(is_string($opts)){
			$this->value = $opts;
		}else{
			$this->set($opts);
		}
	}
}
