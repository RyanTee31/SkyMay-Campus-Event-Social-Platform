<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin('organizer');

function organizerPostsTableExists(mysqli $conn, string $tableName): bool {
  $safeTable = $conn->real_escape_string($tableName);
  $result = $conn->query("SHOW TABLES LIKE '{$safeTable}'");
  return (bool)($result && $result->num_rows > 0);
}

function organizerPostsColumnExists(mysqli $conn, string $tableName, string $columnName): bool {
  $safeTable = preg_replace('/[^A-Za-z0-9_]/', '', $tableName);
  $safeColumn = $conn->real_escape_string($columnName);
  $result = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");
  return (bool)($result && $result->num_rows > 0);
}

function organizerPostsEnsureCreatedByColumn(mysqli $conn): bool {
  if (!organizerPostsTableExists($conn, 'events')) {
    return false;
  }

  if (!organizerPostsColumnExists($conn, 'events', 'created_by')) {
    $conn->query('ALTER TABLE events ADD COLUMN created_by INT NULL');
  }

  if (!organizerPostsColumnExists($conn, 'events', 'image_path')) {
    $conn->query('ALTER TABLE events ADD COLUMN image_path VARCHAR(255) NULL');
  }

  if (!organizerPostsColumnExists($conn, 'events', 'event_status')) {
    $conn->query('ALTER TABLE events ADD COLUMN event_status VARCHAR(20) NOT NULL DEFAULT "pending"');
  }

  return organizerPostsColumnExists($conn, 'events', 'created_by');
}

function organizerPostsCleanEventTitle(?string $title): string {
  return trim(preg_replace('/^(?:\s*\[(?:Approved|Rejected)\]\s*)+/i', '', (string)$title));
}

function organizerPostsNormalizeEventStatus(?string $status, ?string $title = null): string {
  $clean = strtolower(trim((string)$status));
  if (($clean === '' || $clean === 'pending') && preg_match('/^\s*\[(Approved|Rejected)\]/i', (string)$title, $matches)) {
    $clean = strtolower($matches[1]);
  }

  return in_array($clean, ['approved', 'rejected', 'pending'], true) ? $clean : 'pending';
}

function organizerPostsEventStatusClass(string $status): string {
  if ($status === 'rejected') {
    return 'badge-error';
  }

  return $status === 'pending' ? 'badge-warning' : '';
}

function organizerPostsEventImageUrl(array $event): string {
  $path = trim((string)($event['image_path'] ?? ''));
  if ($path === '') {
    return 'https://via.placeholder.com/900x500?text=Campus+Event';
  }

  if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) {
    return $path;
  }

  $webPath = str_replace('\\', '/', $path);
  $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
  if (is_file(__DIR__ . DIRECTORY_SEPARATOR . $filePath)) {
    return $webPath;
  }

  if (is_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . $filePath)) {
    return '../' . ltrim($webPath, '/');
  }

  return 'https://via.placeholder.com/900x500?text=Campus+Event';
}

function organizerPostsDeleteRelatedRows(mysqli $conn, int $eventId): void {
  foreach (['event_registrations', 'likes', 'comments'] as $tableName) {
    if (!organizerPostsTableExists($conn, $tableName)) {
      continue;
    }

    $stmt = $conn->prepare("DELETE FROM `{$tableName}` WHERE event_id = ?");
    if (!$stmt) {
      throw new RuntimeException('Unable to prepare related delete.');
    }

    $stmt->bind_param('i', $eventId);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException('Unable to delete related rows.');
    }
    $stmt->close();
  }
}

function organizerPostsFindOwnedEvent(mysqli $conn, int $eventId, int $organizerId): ?array {
  $stmt = $conn->prepare('SELECT event_id, title, event_date, venue, description FROM events WHERE event_id = ? AND created_by = ? LIMIT 1');
  if (!$stmt) {
    return null;
  }

  $stmt->bind_param('ii', $eventId, $organizerId);
  $stmt->execute();
  $result = $stmt->get_result();
  $event = ($result && $row = $result->fetch_assoc()) ? $row : null;
  $stmt->close();

  return $event;
}

$pageTitle = 'My Event Posts - SkyMay';
$activePage = 'organizer';
$organizerId = (int)($_SESSION['user']['id'] ?? 0);
$message = '';
$error = '';
$myEvents = [];
$editingEvent = null;

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
  $message = 'Event post deleted successfully.';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
  $message = 'Event post updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $eventId = (int)($_POST['event_id'] ?? 0);

  if ($eventId <= 0 && in_array($action, ['delete_event', 'update_event'], true)) {
    $error = 'Invalid event selected.';
  } elseif (!$dbConnected) {
    $error = 'Database not connected. Unable to manage posts right now.';
  } elseif (!organizerPostsEnsureCreatedByColumn($conn)) {
    $error = 'Events table is not ready yet.';
  } elseif ($action === 'delete_event') {
    $ownedEvent = organizerPostsFindOwnedEvent($conn, $eventId, $organizerId);
    if (!$ownedEvent) {
      $error = 'You can only delete posts created by your own account.';
    } else {
      $conn->begin_transaction();
      try {
        organizerPostsDeleteRelatedRows($conn, $eventId);
        $deleteStmt = $conn->prepare('DELETE FROM events WHERE event_id = ? AND created_by = ?');
        if (!$deleteStmt) {
          throw new RuntimeException('Unable to prepare event delete.');
        }

        $deleteStmt->bind_param('ii', $eventId, $organizerId);
        if (!$deleteStmt->execute() || $deleteStmt->affected_rows < 1) {
          $deleteStmt->close();
          throw new RuntimeException('Unable to delete event.');
        }
        $deleteStmt->close();
        $conn->commit();
        header('Location: organizer_posts.php?msg=deleted');
        exit;
      } catch (Throwable $deleteError) {
        $conn->rollback();
        $error = 'Unable to delete this event post right now.';
      }
    }
  } elseif ($action === 'update_event') {
    $title = trim($_POST['title'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $eventDate === '' || $venue === '' || $description === '') {
      $error = 'Please complete all edit fields.';
    } elseif (!organizerPostsFindOwnedEvent($conn, $eventId, $organizerId)) {
      $error = 'You can only edit posts created by your own account.';
    } else {
      $updateStmt = $conn->prepare('UPDATE events SET title = ?, event_date = ?, venue = ?, description = ? WHERE event_id = ? AND created_by = ?');
      if ($updateStmt) {
        $updateStmt->bind_param('ssssii', $title, $eventDate, $venue, $description, $eventId, $organizerId);
        if ($updateStmt->execute()) {
          $updateStmt->close();
          header('Location: organizer_posts.php?msg=updated');
          exit;
        }
        $updateStmt->close();
        $error = 'Unable to update this event post right now.';
      } else {
        $error = 'Unable to prepare update query.';
      }
    }
  }
}

if ($error === '' && $dbConnected && isset($_GET['edit_id'])) {
  $editId = (int)$_GET['edit_id'];
  if ($editId > 0 && organizerPostsEnsureCreatedByColumn($conn)) {
    $editingEvent = organizerPostsFindOwnedEvent($conn, $editId, $organizerId);
    if (!$editingEvent) {
      $error = 'You can only edit posts created by your own account.';
    }
  }
}

if ($dbConnected && organizerPostsEnsureCreatedByColumn($conn)) {
  $stmt = $conn->prepare('SELECT event_id, title, event_date, venue, description, image_path, event_status FROM events WHERE created_by = ? ORDER BY event_date DESC, event_id DESC');
  if ($stmt) {
    $stmt->bind_param('i', $organizerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($result && $row = $result->fetch_assoc()) {
      $myEvents[] = $row;
    }
    $stmt->close();
  }
} elseif (!$dbConnected) {
  $error = $error !== '' ? $error : 'Database not connected. Your posts cannot be loaded.';
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero reveal">
    <h1>My Event Posts</h1>
    <p>Manage the event posts created by your organizer account.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="createevent.php">Create New Event</a>
      <a class="btn btn-secondary" href="organizer.php">Back to Dashboard</a>
    </div>
  </section>

  <?php if ($message !== ''): ?><p class="badge"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
  <?php if ($error !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

  <?php if ($editingEvent): ?>
    <section class="card form-card reveal" id="edit-post">
      <h2>Edit Event Post</h2>
      <form method="post" action="organizer_posts.php">
        <input type="hidden" name="action" value="update_event" />
        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars((string)$editingEvent['event_id']); ?>" />
        <label>Event Title<input name="title" type="text" value="<?php echo htmlspecialchars($editingEvent['title']); ?>" required /></label>
        <label>Date<input name="event_date" type="date" value="<?php echo htmlspecialchars($editingEvent['event_date']); ?>" required /></label>
        <label>Venue<input name="venue" type="text" value="<?php echo htmlspecialchars($editingEvent['venue']); ?>" required /></label>
        <label>Description<textarea name="description" rows="4" required><?php echo htmlspecialchars($editingEvent['description']); ?></textarea></label>
        <div class="hero-actions">
          <button class="btn btn-primary" type="submit">Save Changes</button>
          <a class="btn btn-secondary" href="organizer_posts.php">Cancel</a>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <section class="card table-card reveal" id="my-posts">
    <h2>My Event Posts</h2>
    <?php if (count($myEvents) === 0): ?>
      <p>You have not created any event posts yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Status</th>
              <th>Date</th>
              <th>Venue</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($myEvents as $event): ?>
              <?php
                $eventTitle = organizerPostsCleanEventTitle($event['title'] ?? '');
                $eventStatus = organizerPostsNormalizeEventStatus($event['event_status'] ?? 'pending', $event['title'] ?? '');
                $statusClass = organizerPostsEventStatusClass($eventStatus);
              ?>
              <tr>
                <td>
                  <img
                    class="table-event-thumb"
                    src="<?php echo htmlspecialchars(organizerPostsEventImageUrl($event)); ?>"
                    alt="<?php echo htmlspecialchars($eventTitle !== '' ? $eventTitle : 'Event image'); ?>"
                    loading="lazy"
                  />
                </td>
                <td><a href="eventdetail.php?id=<?php echo urlencode((string)$event['event_id']); ?>"><?php echo htmlspecialchars($eventTitle); ?></a></td>
                <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($eventStatus)); ?></span></td>
                <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                <td>
                  <div class="table-actions">
                    <a class="btn btn-secondary" href="organizer_posts.php?edit_id=<?php echo urlencode((string)$event['event_id']); ?>#edit-post">Edit</a>
                    <form class="inline-form" method="post" action="organizer_posts.php" onsubmit="return confirm('Delete this event post?');">
                      <input type="hidden" name="action" value="delete_event" />
                      <input type="hidden" name="event_id" value="<?php echo htmlspecialchars((string)$event['event_id']); ?>" />
                      <button class="btn btn-ghost" type="submit">Delete</button>
                    </form>
                  </div>
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
