<?php
require 'config.php';

$database = $_GET['database'];
$table = $_GET['table'];
$row_id = $_GET['row_id'];
$dbType = $_GET['db-type'] ?? 'mysql'; // Default to MySQL if not specified

header('Content-Type: application/json');

if (!isset($database) || !isset($table) || !isset($row_id))
{
  echo json_encode(['error' => 'Database, table, or row ID not specified.']);
  exit;
}

try
{
  $pdo = getPDOConnection($dbType);
  $pdo->exec("USE $database");

  // Fetch primary key column name
  $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
  $primary_key = $stmt->fetch(PDO::FETCH_ASSOC)['Column_name'];

  $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$primary_key` = :row_id");
  $stmt->bindParam(':row_id', $row_id, PDO::PARAM_STR);
  $stmt->execute();

  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result)
  {
    echo json_encode($result);
  } else
  {
    echo json_encode(['error' => 'Record not found.']);
  }
} catch (Exception $e)
{
  echo json_encode(['error' => $e->getMessage()]);
}
?>
