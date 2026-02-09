<?php
define('MYSQL_USER', 'zarliminnwe');
define('MYSQL_PASSWORD', 'zarliminnwe');
define('MYSQL_HOST', 'localhost');
define('MYSQL_DATABASE', 'zarliminnwe');
$options = array(
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
);
$pdo = new PDO(
  'mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD,$options
);

