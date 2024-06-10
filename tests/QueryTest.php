<?php
namespace TJM\DB\Tests;

class QueryTest extends QueryTestCase{
	protected $tests = [
		['CREATE TABLE table1 (val INT)', 'CREATE TABLE table1 (val INT)'],
		[
			'CREATE TABLE table1 (val INT)',
			[
				'CREATE TABLE'=> 'table1',
				'(val INT)',
			],
		],
		['SELECT * FROM table1', 'SELECT * FROM table1'],
		[
			'select * from table1 t1 INNER JOIN table2 t2 ON t2.t1_id = t1.id where t1.a = 12 AND t2.a = 12',
			[
				'select'=> '*',
				'from'=> 'table1 t1',
				'INNER JOIN table2 t2 ON t2.t1_id = t1.id',
				'where'=> 't1.a = 12 AND t2.a = 12',
			],
		],
		['UPDATE table1 SET val1 = "val1", val2 = "val2"', 'UPDATE table1 SET val1 = "val1", val2 = "val2"'],
		[
			'UPDATE table1 SET val1 = "val1", val2 = "val2"', 
			[
				'UPDATE'=> 'table1',
				'SET'=> 'val1 = "val1", val2 = "val2"',
			],
		],
		['INSERT INTO table1 SET val1 = "val1", val2 = "val2"', 'INSERT INTO table1 SET val1 = "val1", val2 = "val2"'],
		['INSERT INTO table1 SET val1 = "val1", val2 = "val2"', 
			[
				'INSERT INTO'=> 'table1',
				'SET'=> 'val1 = "val1", val2 = "val2"',
			],
		],
	];
}
