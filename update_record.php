<?php
require 'config.php';

$database = $_POST['database'];
$table = $_POST['table'];
$row_id = $_POST['row_id'];
$dbType = $_POST['db-type'] ?? 'mysql'; // Default to MySQL if not specified

if (!isset($database) || !isset($table) || !isset($row_id))
{
  die("Database, table, or row ID not specified.");
}

try
{
  $pdo = getPDOConnection($dbType);
  $pdo->exec("USE $database");

  // Fetch primary key column name
  $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
  $primary_key = $stmt->fetch(PDO::FETCH_ASSOC)['Column_name'];

  $update_fields = [];
  foreach ($_POST as $key => $value)
  {
    if ($key != 'database' && $key != 'table' && $key != 'row_id')
    {
      $update_fields[] = "`$key` = " . $pdo->quote($value);
    }
  }

  $sql = "UPDATE `$table` SET " . implode(", ", $update_fields) . " WHERE `$primary_key` = " . $pdo->quote($row_id);
  $stmt = $pdo->prepare($sql);
  if ($stmt->execute())
  {
    echo "Record updated successfully";
  } else
  {
    echo "Error updating record: " . $stmt->errorInfo()[2];
  }
} catch (Exception $e)
{
  echo "Error: " . $e->getMessage();
}
?>
