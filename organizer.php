<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin('organizer');

function organizerDashboardTableExists(mysqli $conn, string $tableName): bool {
  $safeTable = $conn->real_escape_string($tableName);
  $result = $conn->query("SHOW TABLES LIKE '{$safeTable}'");
  return (bool)($result && $result->num_rows > 0);
}

function organizerDashboardColumnExists(mysqli $conn, string $tableName, string $columnName): bool {
  $safeTable = preg_replace('/[^A-Za-z0-9_]/', '', $tableName);
  $safeColumn = $conn->real_escape_string($columnName);
  $result = $conn->query("SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'");
  return (bool)($result && $result->num_rows > 0);
}

function organizerDashboardEnsureCreatedByColumn(mysqli $conn): bool {
  if (!organizerDashboardTableExists($conn, 'events')) {
    return false;
  }

  if (!organizerDashboardColumnExists($conn, 'events', 'created_by')) {
    $conn->query('ALTER TABLE events ADD COLUMN created_by INT NULL');
  }

  return organizerDashboardColumnExists($conn, 'events', 'created_by');
}

$pageTitle = 'Organizer Dashboard - SkyMay';
$activePage = 'organizer';
$organizerId = (int)($_SESSION['user']['id'] ?? 0);
$postCount = 0;
$error = '';

if ($dbConnected && organizerDashboardEnsureCreatedByColumn($conn)) {
  $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM events WHERE created_by = ?');
  if ($stmt) {
    $stmt->bind_param('i', $organizerId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
      $postCount = (int)$row['total'];
    }
    $stmt->close();
  }
} elseif (!$dbConnected) {
  $error = 'Database not connected. Post count cannot be loaded.';
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero reveal">
    <h1>Organizer Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Organizer'); ?>. Manage your event campaigns and publish new opportunities.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="createevent.php">Create New Event</a>
      <a class="btn btn-secondary" href="event.php">View Public Event Feed</a>
    </div>
  </section>

  <?php if ($error !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

  <section class="dashboard-grid">
    <article class="card reveal">
      <h3>Total Posts</h3>
      <p class="metric"><?php echo htmlspecialchars((string)$postCount); ?></p>
      <p>Events created by your account.</p>
    </article>
    <article class="card reveal">
      <h3>My Events</h3>
      <p>Open your event posts page to edit or delete published posts.</p>
      <a class="btn btn-secondary" href="organizer_posts.php">Manage Posts</a>
    </article>
  </section>

  <section class="info-grid">
    <article class="card reveal"><h3>Publishing</h3><p>Design clear titles, date, venue, and value proposition for attendees.</p></article>
    <article class="card reveal"><h3>Engagement</h3><p>Reply to comments and improve event visibility through interaction.</p></article>
    <article class="card reveal"><h3>Performance</h3><p>Track student interest and refine future events using feedback trends.</p></article>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
