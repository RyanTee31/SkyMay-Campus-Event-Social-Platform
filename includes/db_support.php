<?php

function dbSafeIdentifier(string $identifier): string
{
  if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
    throw new InvalidArgumentException('Invalid database identifier.');
  }

  return $identifier;
}

function dbTableExists(mysqli $conn, string $tableName): bool
{
  $safeTable = $conn->real_escape_string($tableName);
  $result = $conn->query("SHOW TABLES LIKE '{$safeTable}'");
  return (bool)($result && $result->num_rows > 0);
}

function dbColumnExists(mysqli $conn, string $tableName, string $columnName): bool
{
  $table = dbSafeIdentifier($tableName);
  $safeColumn = $conn->real_escape_string($columnName);
  $result = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$safeColumn}'");
  return (bool)($result && $result->num_rows > 0);
}

?>
