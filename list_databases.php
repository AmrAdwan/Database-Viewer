<?php
require 'config.php';

$dbType = $_GET['db-type'] ?? 'mysql'; // Default to MySQL if not specified

try
{
  $pdo = getPDOConnection($dbType);

  if ($dbType == 'mysql')
  {
    $sql = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA";
  } elseif ($dbType == 'sqlsrv')
  {
    $sql = "SELECT name FROM sys.databases";
  } elseif ($dbType == 'pgsql')
  {
    $sql = "SELECT datname FROM pg_database";
  } else
  {
    throw new Exception("Unsupported database type");
  }

  $stmt = $pdo->query($sql);

  if ($stmt->rowCount() > 0)
  {
    echo "<select class='form-select' onchange='showTables(this.value)'>";
    echo "<option value=''>Select a database</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      $dbName = array_values($row)[0];
      echo "<option value='$dbName'>$dbName</option>";
    }
    echo "</select>";
  } else
  {
    echo "0 results";
  }
} catch (Exception $e)
{
  echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
}
?>
