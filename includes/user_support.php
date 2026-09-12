<?php
require_once __DIR__ . '/db_support.php';

function ensureUserStatusColumn(mysqli $conn): void
{
  if (!dbTableExists($conn, 'users')) {
    return;
  }

  if (!dbColumnExists($conn, 'users', 'status')) {
    $conn->query("ALTER TABLE users ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER role");
  }
}

function normalizeUserStatus(?string $status): string
{
  return strtolower(trim((string)$status)) === 'inactive' ? 'inactive' : 'active';
}

?>
