<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login first']);
  exit;
}

if (!$dbConnected) {
  echo json_encode(['success' => false, 'message' => 'Database not connected']);
  exit;
}

ensureEventInteractionTables($conn);

$user = currentUser();
$userId = (int)$user['id'];
$registrations = [];

$stmt = $conn->prepare('
  SELECT er.event_id, er.status, er.registered_at, e.title, e.event_date, e.venue
  FROM event_registrations er
  JOIN events e ON er.event_id = e.event_id
  WHERE er.user_id = ? AND er.status = "registered"
  ORDER BY er.registered_at DESC
');

if ($stmt) {
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $registrations[] = $row;
  }
  $stmt->close();
}

echo json_encode(['success' => true, 'registrations' => $registrations]);
?>
