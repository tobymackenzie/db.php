<?php
namespace TJM\DB\Tests;
use TJM\DB\Query;
use PHPUnit\Framework\TestCase;

abstract class QueryTestCase extends TestCase{
	protected $tests = [];
	public function testCreateQuery(){
		$successes = 0;
		foreach($this->tests as $i=> $test){
			$query = $test[1];
			$query = Query::create($query);
			$this->assertEquals($test[0], (string) $query, "SQL not as expected for value " . json_encode($test[1]));
		}
	}
}
