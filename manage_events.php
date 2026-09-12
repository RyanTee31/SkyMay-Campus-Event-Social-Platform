<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';
requireLogin('admin');

$pageTitle = 'Event Management - SkyMay';
$activePage = 'admin-events';

$events = [];
$flashMessage = '';
$flashType = 'success';

if ($dbConnected) {
  ensureEventOptionalColumns($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $flashMessage = trim($_GET['msg'] ?? '');
  $flashType = ($_GET['type'] ?? '') === 'error' ? 'error' : 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbConnected) {
  $action = trim($_POST['action'] ?? '');
  $eventId = (int)($_POST['event_id'] ?? 0);

  if ($eventId > 0 && ($action === 'delete_event' || $action === 'approve_event' || $action === 'reject_event')) {
    if ($action === 'delete_event') {
      $stmt = $conn->prepare('DELETE FROM events WHERE event_id = ?');
      $stmt->bind_param('i', $eventId);
      if ($stmt->execute() && $stmt->affected_rows > 0) {
        $flashMessage = 'Event deleted successfully.';
      } else {
        $flashMessage = 'Unable to delete event.';
        $flashType = 'error';
      }
      $stmt->close();
    } else {
      $nextStatus = $action === 'approve_event' ? 'approved' : 'rejected';
      $selectStmt = $conn->prepare('SELECT title FROM events WHERE event_id = ? LIMIT 1');
      $selectStmt->bind_param('i', $eventId);
      $selectStmt->execute();
      $eventRow = $selectStmt->get_result()->fetch_assoc();
      $selectStmt->close();

      if ($eventRow) {
        $cleanTitle = cleanEventTitle($eventRow['title'] ?? '');
        $stmt = $conn->prepare('UPDATE events SET event_status = ?, title = ? WHERE event_id = ?');
        $stmt->bind_param('ssi', $nextStatus, $cleanTitle, $eventId);
        if ($stmt->execute()) {
          $flashMessage = $action === 'approve_event' ? 'Event approved successfully.' : 'Event rejected successfully.';
        } else {
          $flashMessage = 'Unable to update event status.';
          $flashType = 'error';
        }
        $stmt->close();
      } else {
        $flashMessage = 'Event not found.';
        $flashType = 'error';
      }
    }
  } else {
    $flashMessage = 'Invalid event selected.';
    $flashType = 'error';
  }

  $redirectMsg = rawurlencode($flashMessage);
  $redirectType = rawurlencode($flashType);
  header("Location: manage_events.php?msg={$redirectMsg}&type={$redirectType}");
  exit;
}

if ($dbConnected) {
  $q = $conn->query('SELECT event_id, title, event_date, venue, image_path, event_status FROM events ORDER BY event_date DESC LIMIT 50');
  if ($q) {
    while ($row = $q->fetch_assoc()) {
      $events[] = $row;
    }
  }
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero reveal">
    <h1>Event Management</h1>
    <p>Approve, reject, or remove events from the platform.</p>
    <div class="hero-actions">
      <a class="btn btn-secondary" href="admin.php">Back to Admin Dashboard</a>
    </div>
  </section>

  <?php if ($flashMessage !== ''): ?>
    <p class="badge <?php echo $flashType === 'error' ? 'badge-error' : ''; ?>"><?php echo htmlspecialchars($flashMessage); ?></p>
  <?php endif; ?>

  <section class="card reveal table-card">
    <?php if (count($events) === 0): ?>
      <p>No events found.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Title</th>
              <th>Status</th>
              <th>Date</th>
              <th>Venue</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($events as $event): ?>
              <?php
                $eventTitle = cleanEventTitle($event['title'] ?? '');
                $eventStatus = normalizeEventStatus($event['event_status'] ?? 'pending', $event['title'] ?? '');
                $statusClass = $eventStatus === 'rejected' ? 'badge-error' : ($eventStatus === 'pending' ? 'badge-warning' : '');
              ?>
              <tr>
                <td><?php echo (int)$event['event_id']; ?></td>
                <td>
                  <img
                    class="table-event-thumb"
                    src="<?php echo htmlspecialchars(eventImageUrl($event)); ?>"
                    alt="<?php echo htmlspecialchars($eventTitle !== '' ? $eventTitle : 'Event image'); ?>"
                    loading="lazy"
                  />
                </td>
                <td><?php echo htmlspecialchars($eventTitle); ?></td>
                <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($eventStatus)); ?></span></td>
                <td><?php echo htmlspecialchars($event['event_date'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($event['venue'] ?? ''); ?></td>
                <td class="table-actions">
                  <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="approve_event">
                    <input type="hidden" name="event_id" value="<?php echo (int)$event['event_id']; ?>">
                    <button class="btn btn-secondary" type="submit">Approve</button>
                  </form>
                  <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="reject_event">
                    <input type="hidden" name="event_id" value="<?php echo (int)$event['event_id']; ?>">
                    <button class="btn btn-ghost" type="submit">Reject</button>
                  </form>
                  <form method="post" class="inline-form" onsubmit="return confirm('Delete this event?');">
                    <input type="hidden" name="action" value="delete_event">
                    <input type="hidden" name="event_id" value="<?php echo (int)$event['event_id']; ?>">
                    <button class="btn btn-ghost" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
