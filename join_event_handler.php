<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login first']);
  exit;
}

$user = currentUser();
if (($user['role'] ?? '') !== 'student') {
  echo json_encode(['success' => false, 'message' => 'Only students can join events']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
  $input = [];
}

$eventId = $input['event_id'] ?? $_POST['event_id'] ?? null;
$action = strtolower((string)($input['action'] ?? $_POST['action'] ?? 'join'));
$userId = (int)($user['id'] ?? 0);
$eventId = (int)$eventId;

if (!$dbConnected) {
  echo json_encode(['success' => false, 'message' => 'Database not connected']);
  exit;
}

ensureEventInteractionTables($conn);

if ($userId <= 0 || $eventId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid user or event']);
  exit;
}

$eventCheck = $conn->prepare('SELECT event_id FROM events WHERE event_id = ? LIMIT 1');
if (!$eventCheck) {
  echo json_encode(['success' => false, 'message' => 'Database error']);
  exit;
}
$eventCheck->bind_param('i', $eventId);
$eventCheck->execute();
$eventResult = $eventCheck->get_result();
$eventExists = $eventResult && $eventResult->num_rows > 0;
$eventCheck->close();

if (!$eventExists) {
  echo json_encode(['success' => false, 'message' => 'Event not found']);
  exit;
}

function countRegisteredUsers(mysqli $conn, int $eventId): int
{
  $countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = ? AND status = "registered"');
  if (!$countStmt) {
    return 0;
  }
  $countStmt->bind_param('i', $eventId);
  $countStmt->execute();
  $countResult = $countStmt->get_result();
  $total = (int)($countResult->fetch_assoc()['total'] ?? 0);
  $countStmt->close();
  return $total;
}

if ($action === 'join') {
  $stmt = $conn->prepare('INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, "registered") ON DUPLICATE KEY UPDATE status = "registered", updated_at = CURRENT_TIMESTAMP');
  if ($stmt) {
    $stmt->bind_param('ii', $eventId, $userId);
    if ($stmt->execute()) {
      echo json_encode([
        'success' => true,
        'message' => 'Successfully joined event',
        'action' => 'joined',
        'total_registrations' => countRegisteredUsers($conn, $eventId)
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to join event']);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
  }
} elseif ($action === 'cancel') {
  $stmt = $conn->prepare('UPDATE event_registrations SET status = "cancelled" WHERE event_id = ? AND user_id = ?');
  if ($stmt) {
    $stmt->bind_param('ii', $eventId, $userId);
    if ($stmt->execute()) {
      echo json_encode([
        'success' => true,
        'message' => 'Cancelled registration',
        'action' => 'cancelled',
        'total_registrations' => countRegisteredUsers($conn, $eventId)
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to cancel registration']);
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
