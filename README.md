## About ##

This Database class is thin layer around PDO that adds more functionality to PDO. It adds new methods but enables you to use this class joust as you would use regural PDO object, all PDO methods are available. Added methods are SQL Injection safe, but when using existing PDO methods its up to you to take care of SQL Injection. 

## How to configure and use ##

To use this class you need to set your database configuration. Database configuration is defined as array that should looks like this:

<pre>
$config = array(
	'driver'   => 'mysql',
	'host'     => 'localhost',
	'name'     => 'test',
	'username' => 'root',
	'password' => '******',
	'options'  => array(),
);
</pre>

You can set configuration thorugh constructor:

<pre>
$db = new \Core\Database\Database($config);
</pre>

or through setter method:

<pre>
$db = new \Core\Database\Database();
$db->setConfig($config);
</pre>

## Data manipulation ##

Insert new row:
<pre>
$db->insert('users', array('username' => 'matija', 'password' => '******'));
</pre>

Update existing row:
<pre>
$db->update('users', array('password' => '*****'), array('id' => 1));
</pre>

Delete row:
<pre>
$db->delete('users', array('id' => 1));
</pre>

## Queries ##

Fetch first row as array, both indexed and associative
<pre>
$user = $db->fetchArray("SELECT * FROM users WHERE id = ?", array(1));
</pre>

Fetch all rows as array, both indexed and associative
<pre>
$posts = $db->fetchAllArray("SELECT * FROM posts WHERE category = ?", array(1));
</pre>

Fetch first row as array, associative only
<pre>
$user = $db->fetchAssoc("SELECT * FROM users WHERE id = ?", array(1));
</pre>

Fetch all rows as array, associative only
<pre>
$posts = $db->fetchAllAssoc("SELECT * FROM posts WHERE category = ?", array(1));
</pre>

Fetch first row as object
<pre>
$user = $db->fetchObject("SELECT * FROM users WHERE id = ?", array(1));
</pre>

Fetch all rows as objects, returns array holding all objects
<pre>
$posts = $db->fetchAllObject("SELECT * FROM posts WHERE category = ?", array(1));
</pre>

To close database connection, simply destroy $db object:
<pre>
$db = null;
</pre>
















