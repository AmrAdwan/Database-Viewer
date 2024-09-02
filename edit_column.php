<?php
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$database = $data['database'];
$table = $data['table'];
$column = $data['column'];
$newType = $data['newType'];
$dbType = $data['db-type'] ?? 'mysql';

if (!isset($database) || !isset($table) || !isset($column) || !isset($newType))
{
  die("Database, table, column, or new type not specified.");
}

try
{
  $pdo = getPDOConnection($dbType);
  $pdo->exec("USE $database");

  $sql = "ALTER TABLE `$table` MODIFY `$column` $newType";
  $stmt = $pdo->prepare($sql);
  if ($stmt->execute())
  {
    echo "Column type updated successfully";
  } else
  {
    echo "Error updating column type: " . $stmt->errorInfo()[2];
  }
} catch (Exception $e)
{
  echo "Error: " . $e->getMessage();
}
?>
