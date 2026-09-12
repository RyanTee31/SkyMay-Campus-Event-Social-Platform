<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';
requireLogin('student');

$pageTitle = 'Student Dashboard - SkyMay';
$activePage = 'student';

$user = currentUser();
$userId = (int)$user['id'];
$myEvents = [];

if ($dbConnected) {
  ensureEventInteractionTables($conn);
  $stmt = $conn->prepare('
    SELECT e.event_id, e.title, e.event_date, e.venue, e.description, er.registered_at
    FROM event_registrations er
    JOIN events e ON er.event_id = e.event_id
    WHERE er.user_id = ? AND er.status = "registered"
    ORDER BY e.event_date ASC
  ');
  if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $myEvents[] = $row;
    }
    $stmt->close();
  }
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero reveal">
    <h1>Student Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Student'); ?>. Here are your joined events.</p>
  </section>

  <section class="reveal">
    <h2>My Joined Events (<?php echo count($myEvents); ?>)</h2>
    <?php if (!$dbConnected): ?>
      <p class="badge badge-error">Database not connected. Joined events cannot be loaded.</p>
    <?php elseif (count($myEvents) === 0): ?>
      <div class="card empty-state">
        <p>You have not joined any events yet.</p>
        <a href="event.php" class="btn btn-primary">Browse Events</a>
      </div>
    <?php else: ?>
      <div class="event-grid student-event-grid">
        <?php foreach ($myEvents as $event): ?>
          <article class="card">
            <span class="badge">Joined on <?php echo date('M d, Y', strtotime($event['registered_at'])); ?></span>
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <div class="event-meta">Date: <?php echo htmlspecialchars($event['event_date']); ?> | Venue: <?php echo htmlspecialchars($event['venue']); ?></div>
            <p><?php echo htmlspecialchars(substr((string)$event['description'], 0, 100)); ?></p>
            <a href="eventdetail.php?id=<?php echo (int)$event['event_id']; ?>" class="btn btn-secondary">View Details</a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="info-grid">
    <article class="card reveal"><h3>Explore Events</h3><p>Browse and join more campus events.</p><a class="btn btn-secondary" href="event.php">Browse Events</a></article>
    <article class="card reveal"><h3>Event Reminders</h3><p>Check your joined events regularly for updates.</p></article>
    <article class="card reveal"><h3>Participation History</h3><p>Track all events you have joined on campus.</p></article>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
