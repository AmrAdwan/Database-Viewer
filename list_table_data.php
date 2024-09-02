<?php
require 'config.php';

$database = $_GET['database'];
$table = $_GET['table'];
$dbType = $_GET['db-type'] ?? 'mysql'; // Default to MySQL if not specified

if (!isset($database) || !isset($table))
{
  die("Database or table not specified.");
}

try
{
  $pdo = getPDOConnection($dbType);

  // Adjust the query based on the database type
  if ($dbType === 'mysql')
  {
    // Use the database
    $pdo->exec("USE `$database`");
    $sql = "SELECT * FROM `$table`";
  } elseif ($dbType === 'sqlsrv')
  {
    $sql = "SELECT * FROM [$database].dbo.[$table]";
  } elseif ($dbType === 'pgsql')
  {
    // Directly select from the table
    $sql = "SELECT * FROM \"$table\"";
  } else
  {
    throw new Exception("Unsupported database type");
  }

  $stmt = $pdo->query($sql);

  if ($stmt->rowCount() > 0)
  {
    echo "<table class='table table-bordered'><thead><tr>";
    $field_names = [];
    for ($i = 0; $i < $stmt->columnCount(); $i++)
    {
      $columnMeta = $stmt->getColumnMeta($i);
      echo "<th>" . $columnMeta['name'] . "</th>";
      $field_names[] = $columnMeta['name'];
    }
    echo "<th>Actions</th>";
    echo "</tr></thead><tbody>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      $row_id = $row[$field_names[0]];
      echo "<tr>";
      foreach ($row as $cell)
      {
        echo "<td>" . htmlspecialchars($cell) . "</td>";
      }
      echo "<td><button class='btn btn-primary btn-sm' onclick='editRecord(\"$database\", \"$table\", \"$row_id\")'>Edit</button></td>";
      echo "</tr>";
    }
    echo "</tbody></table>";
  } else
  {
    echo "<div class='alert alert-warning'>0 results</div>";
  }
} catch (Exception $e)
{
  echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
}
?>
