<?php
	
	// Include Autoload
	require_once('src/Core/Autoload/Autoload.php');
	
	// Register Autoloading
	$autoload = new Autoload();
	$autoload->addLibrary('src/', 'Core');
	$autoload->register();

	// Database configuration, should be in your config folder
	$config = array(
		'driver'   => 'mysql',
		'host'     => 'localhost',
		'name'     => 'test',
		'username' => 'root',
		'password' => 'prideordiE',
		'options'  => array(),
	);
	
	// Create database instance
	$db = new \Core\Database\Database();
	
	// Or you can use setter method to set configuration
	$db->setConfig($config);
	
	// Fetch user with id = 5 as Object
	$user = $db->fetchObject("SELECT * FROM users WHERE id = ?", array(2));

	var_export($user);
	
	// Use your database...


?>