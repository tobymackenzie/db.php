<?php
namespace TJM\DB\Tests;
use TJM\DB\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase{
	public function testCreateQuery(){
		$tests = [
			['SELECT * FROM table1', 'SELECT * FROM table1']
			,['SELECT * FROM table1 this', ['from'=> 'table1']]
			,['SELECT * FROM table1 this JOIN table2 ON table2.table1 = table1.id', [
				'from'=> 'table1'
				,'joins'=> 'JOIN table2 ON table2.table1 = table1.id'
			]]
			,[
				'SELECT this.id, table2.name'
					. ' FROM table1 this'
					. ' INNER JOIN table2 t2 ON table2.table1 = table1.id LEFT JOIN table3 t3 ON table3.table2 = table2.id INNER JOIN table4 t4'
					. ' WHERE table4.table3 = table3.id AND table2.type = :p1 AND table2.width >= :p2 AND table1.foo = :foo'
					. ' ORDER BY table1.sort asc, table2.name desc'
					. ' LIMIT 20, 100'
				,[
					'values'=> ['this.id', 'table2.name']
					,'from'=> 'table1'
					,'joins'=> [
						't2'=> [
							'on'=> 'table2.table1 = table1.id'
							,'table'=> 'table2'
						]
						,'t3'=> [
							'on'=> 'table3.table2 = table2.id'
							,'table'=> 'table3'
							,'type'=> 'LEFT'
						]
						,'t4'=> 'table4'
					]
					,'where'=> [
						'table4.table3 = table3.id'
						,'table2.type'=> 'good'
						,'table2.width >='=> '3'
						,'table1.foo'=> ':foo'
					]
					,'orderBy'=> ['table1.sort'=> 'asc', 'table2.name desc']
					,'limit'=> '20, 100'
				]
			]
			,[
				'SELECT city, sum(amount) as amount FROM table1 this GROUP BY city HAVING amount > 20'
				,[
					'values'=> ['city', 'sum(amount) as amount']
					,'from'=> 'table1'
					,'groupBy'=> 'city'
					,'having'=> 'amount > 20'
				]
			]
			,[
				'SHOW TABLES'
				,[
					'command'=> 'SHOW'
					,'values'=> 'TABLES'
				]
			]
			,[
				'SHOW TABLES'
				,[
					'command'=> 'SHOW TABLES'
				]
			]
			,[
				'select * from table1 t1 INNER JOIN table2 t2 ON t2.t1_id = t1.id where t1.a = 12 AND t2.a = 12'
				,[
					'select'=> '*'
					,'from'=> 'table1 t1'
					,'INNER JOIN table2 t2 ON t2.t1_id = t1.id'
					,'where'=> 't1.a = 12 AND t2.a = 12'
				]
			]
		];
		$successes = 0;
		foreach($tests as $i=> $test){
			$query = $test[1];
			if(!($query instanceof Query)){
				$query = Query::create($query);
			}
			$this->assertEquals($test[0], (string) $query, "SQL not as expected for value " . json_encode($test[1]));
		}
	}
}
