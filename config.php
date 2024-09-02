<?php
$dbConfig = [
  'mysql' => [
    'dsn' => 'mysql:host=localhost',
    'username' => 'root',
    'password' => '',
  ],
  'sqlsrv' => [
    'dsn' => 'sqlsrv:Server=localhost',
    'username' => 'root',
    'password' => '',
  ],
  'pgsql' => [
    // 'dsn' => 'pgsql:host=localhost',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=Users',
    'username' => 'amradwan',
    'password' => '',
  ],
];

function getPDOConnection($dbType)
{
  global $dbConfig;
  if (!isset($dbConfig[$dbType]))
  {
    throw new Exception("Unsupported database type: $dbType");
  }
  $config = $dbConfig[$dbType];
  try
  {
    return new PDO($config['dsn'], $config['username'], $config['password']);
  } catch (PDOException $e)
  {
    throw new Exception("Connection failed: " . $e->getMessage());
  }
}
?>
