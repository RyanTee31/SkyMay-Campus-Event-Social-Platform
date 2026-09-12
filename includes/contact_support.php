<?php

function ensureContactMessagesTable(mysqli $conn): void
{
  $conn->query('CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(120) NOT NULL,
    sender_email VARCHAR(160) NOT NULL,
    sender_role VARCHAR(30) NOT NULL,
    message_body TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT "unread",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    KEY idx_contact_messages_status (status),
    KEY idx_contact_messages_created_at (created_at)
  )');

  $conn->query("UPDATE contact_messages SET status = 'unread' WHERE LOWER(status) <> 'read'");
  $conn->query("UPDATE contact_messages SET read_at = NULL WHERE read_at = '0000-00-00 00:00:00'");
}

function normalizeContactRole(string $role): string
{
  $allowed = ['student', 'organizer', 'admin'];
  $clean = strtolower(trim($role));
  return in_array($clean, $allowed, true) ? $clean : '';
}

function normalizeContactStatus(string $status): string
{
  return strtolower(trim($status)) === 'read' ? 'read' : 'unread';
}

?>
