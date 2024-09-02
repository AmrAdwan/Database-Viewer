<?php
require 'config.php';

$database = $_GET['database'];
$dbType = $_GET['db-type'] ?? 'mysql'; // Default to MySQL if not specified

if (!isset($database))
{
  die("Database not specified.");
}

try
{
  $pdo = getPDOConnection($dbType);

  // Adjust the query based on the database type
  if ($dbType === 'mysql')
  {
    $sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = :database";
  } elseif ($dbType === 'sqlsrv')
  {
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG = :database";
  } elseif ($dbType === 'pgsql')
  {
    // PostgreSQL lists tables from the selected database and schema
    $sql = "SELECT table_name FROM information_schema.tables WHERE table_catalog = :database AND table_schema = 'public'";
  } else
  {
    throw new Exception("Unsupported database type");
  }

  $stmt = $pdo->prepare($sql);

  // Bind the parameter for all databases
  $stmt->bindParam(':database', $database, PDO::PARAM_STR);

  $stmt->execute();

  if ($stmt->rowCount() > 0)
  {
    echo "<select class='form-select' onchange='showTableData(\"$database\", this.value)'>";
    echo "<option value=''>Select a table</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      $tableName = $row['TABLE_NAME'] ?? $row['table_name']; // Handle different column names
      echo "<option value='" . $tableName . "'>" . $tableName . "</option>";
    }
    echo "</select>";
  } else
  {
    echo "<div class='alert alert-warning'>0 results</div>";
  }
} catch (Exception $e)
{
  echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
}
?>
