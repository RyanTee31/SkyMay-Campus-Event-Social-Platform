<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function currentUser(): ?array {
  return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool {
  return isset($_SESSION['user']);
}

function hasRole(string $role): bool {
  return isLoggedIn() && (strtolower($_SESSION['user']['role'] ?? '') === strtolower($role));
}

function requireLogin(?string $role = null): void {
  if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
  }

  if ($role !== null && !hasRole($role)) {
    header('Location: homepage.php?unauthorized=1');
    exit;
  }
}
?>
