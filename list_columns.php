<?php
require 'config.php';

$database = $_GET['database'];
$table = $_GET['table'];
$dbType = $_GET['db-type'] ?? 'mysql';

header('Content-Type: application/json');

if (!isset($database) || !isset($table))
{
  echo json_encode(['error' => 'Database or table not specified.']);
  exit;
}

try
{
  $pdo = getPDOConnection($dbType);
  $stmt = $pdo->prepare("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE 
                           FROM information_schema.COLUMNS 
                           WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table");
  $stmt->bindParam(':database', $database, PDO::PARAM_STR);
  $stmt->bindParam(':table', $table, PDO::PARAM_STR);
  $stmt->execute();

  $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($columns);
} catch (Exception $e)
{
  echo json_encode(['error' => $e->getMessage()]);
}
?>
