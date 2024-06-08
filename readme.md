DB
===

This project is a simple PDO abstraction for database querying and other utilities.  It is still early stage and the interface is still being decided, with a good chance of breaking changes.

The two important classes that are likely to stay and remain similar to their description here are `TJM\DB\DB`, which represents a PDO connection, and `TJM\DB\Statement`, which is a wrapper around a `PDOStatement`.  `DB` has a `query()` method that can be passed a query and parameters.  It will execute it and return a `Statement` from which we can `fetch()` our results.  Example usage taking advantage of reuse of the statement:

``` php
$db = new TJM\DB\DB('mysql:dbname=thedb;host=localhost', 'me', '12345');
$query = "SELECT id, name FROM posts WHERE id = :id";
foreach([5, 6] as $id){
	$query = $db->query($query, ['id'=> $id]);
	while($item = $query->fetch()){
		var_dump($item);
	}
}
```

Can optionally connect through SSH tunnel to remote server if `sshID` is set, optionally with `sshDBConnection`.

`DB` will automatically attempt to reconnect to the database if the connection is broken, which can happen in long running scripts, for example.

The query can be an array or object with a special format meant to make progressively building a query easier, but that interface is still being worked out.  Examples of some of these formats that are currently supported	can be seen in the unit tests, but there's a chance some of those won't be supported in the future.
