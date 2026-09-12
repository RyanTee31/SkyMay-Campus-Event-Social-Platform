<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';
requireLogin('organizer');

$pageTitle = 'Create Event - SkyMay';
$activePage = 'create';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $eventDate = trim($_POST['event_date'] ?? '');
  $venue = trim($_POST['venue'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $category = normalizeEventCategory($_POST['category'] ?? 'other');
  $linkCheck = sanitizeEventLink($_POST['event_link'] ?? '');
  $eventLink = $linkCheck['url'] ?? null;

  if ($title === '' || $eventDate === '' || $venue === '' || $description === '') {
    $error = 'Please complete all fields.';
  } elseif (!$linkCheck['ok']) {
    $error = $linkCheck['error'];
  } elseif (!$dbConnected) {
    $error = 'Database not connected. Unable to save right now.';
  } else {
    ensureEventOptionalColumns($conn);
    $uploadResult = uploadEventImage($_FILES['event_image'] ?? []);
    if (!$uploadResult['ok']) {
      $error = $uploadResult['error'];
    }
  }

  if ($error === '' && $dbConnected) {
    $createdBy = (int)($_SESSION['user']['id'] ?? 1);
    $imagePath = $uploadResult['path'] ?? null;
    $stmt = $conn->prepare('INSERT INTO events (title, category, event_date, venue, description, image_path, event_link, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
      $stmt->bind_param('sssssssi', $title, $category, $eventDate, $venue, $description, $imagePath, $eventLink, $createdBy);
      if ($stmt->execute()) {
        $message = 'Event created successfully.';
      } else {
        $error = 'Failed to create event.';
      }
      $stmt->close();
    } else {
      $error = 'Unable to prepare database query.';
    }
  }
}

include 'includes/header.php';
?>
<main class="container section split-grid">
  <form class="card form-card reveal" id="eventForm" method="post" action="createevent.php" enctype="multipart/form-data">
    <h1>Create Campus Event</h1>
    <?php if ($message !== ''): ?><p class="badge"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="badge"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <div class="create-event-grid">
      <label>Event Title<input id="eventTitle" name="title" type="text" required /></label>
      <label>Category
        <select name="category" required>
          <option value="competition">Competition</option>
          <option value="career">Career</option>
          <option value="workshop">Workshop</option>
          <option value="other" selected>Other</option>
        </select>
      </label>
      <label>Date<input id="eventDate" name="event_date" type="date" required /></label>
      <label>Venue<input id="eventVenue" name="venue" type="text" required /></label>
      <label class="full-width">Description<textarea id="eventDesc" name="description" rows="4" required></textarea></label>
      <label>Event Image<input name="event_image" type="file" accept="image/*" /></label>
      <label>Link to Another Page<input name="event_link" type="url" placeholder="https://example.com/event-page" /></label>
    </div>
    <button class="btn btn-primary" type="submit">Publish Event</button>
  </form>

  <aside class="card preview-card reveal">
    <h3>Live Preview</h3>
    <h2 id="previewTitle">Your event title</h2>
    <p class="preview-meta"><strong>Date:</strong> <span id="previewDate">Event date</span></p>
    <p class="preview-meta"><strong>Venue:</strong> <span id="previewVenue">Event venue</span></p>
    <p id="previewDesc">Event description preview will appear here.</p>
  </aside>
</main>
<?php include 'includes/footer.php'; ?>
