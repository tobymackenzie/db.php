<?php
namespace TJM\DB\Tests;
use TJM\DB;
use TJM\DB\Query\GenericQuery;
use PHPUnit\Framework\TestCase;

class DBExecuteTest extends TestCase{
	protected function getDB(){
		return new DB('sqlite::memory:');
	}
	public function testCreateTable(){
		$db = $this->getDB();
		$db->query('CREATE TABLE foo (val INT)');
		$results = $db->query('SELECT name FROM sqlite_master WHERE type="table"');
		$count = 0;
		while(($result = $results->fetch())){
			$this->assertEquals('foo', $result['name']);
			++$count;
		}
		$this->assertEquals(1, $count);
	}
	public function testSimpleCount(){
		$db = $this->getDB();
		$db->query('CREATE TABLE foo (val INT)');
		foreach(range(1,42) as $i){
			$db->query('INSERT INTO foo (val) VALUES (' . ($i * 3) . ')');
		}
		$this->assertEquals(42, $db->query('SELECT count(rowid) AS cnt FROM foo')->fetch()['cnt']);
		$this->assertEquals(32, $db->query('SELECT count(rowid) AS cnt FROM foo WHERE rowid > 10')->fetch()['cnt']);
	}
	public function testSimpleGenericQueryCount(){
		$db = $this->getDB();
		$db->query('CREATE TABLE foo (val INT)');
		foreach(range(1,42) as $i){
			$db->query('INSERT INTO foo (val) VALUES (' . ($i * 3) . ')');
		}
		$this->assertEquals(42, $db->query([
			'values'=> 'count(rowid) AS cnt',
			'table'=> 'foo',
		])->fetch()['cnt']);
		$this->assertEquals(32, $db->query([
			'values'=> 'count(rowid) AS cnt',
			'table'=> 'foo',
			'where'=> 'rowid > 10',
		])->fetch()['cnt']);
	}
	public function testGenericQuerySelect(){
		$db = $this->getDB();
		$db->query('CREATE TABLE foo (val INT)');
		foreach(range(1,42) as $i){
			$db->query('INSERT INTO foo (val) VALUES (' . ($i * 3) . ')');
		}
		$this->assertEquals(54, $db->query([
			'values'=> 'val',
			'table'=> 'foo',
			'where'=> ['rowid'=> 18],
		])->fetch()['val']);
		$this->assertEquals(126, $db->query([
			'values'=> 'val',
			'table'=> 'foo',
			'where'=> ['rowid'=> 42],
		])->fetch()['val']);
		$this->assertEquals(null, $db->query([
			'values'=> 'val',
			'table'=> 'foo',
			'where'=> ['rowid'=> 43],
		])->fetch());
	}
}
