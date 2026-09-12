<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';
require_once __DIR__ . '/includes/contact_support.php';
requireLogin('admin');

$pageTitle = 'Admin Dashboard - SkyMay';
$activePage = 'admin';

$totalUsers = 0;
$totalEvents = 0;
$totalComments = 0;
$totalContactMessages = 0;
$unreadContactMessages = 0;
$approvedEvents = 0;
$pendingEvents = 0;
$rejectedEvents = 0;

function adminDashboardIcon(string $name): string
{
  $icons = [
    'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3Z"/><path d="M8 12c1.66 0 3-1.34 3-3S9.66 6 8 6 5 7.34 5 9s1.34 3 3 3Z"/><path d="M8 14c-2.67 0-5 1.35-5 3v1.5h10V17c0-1.65-2.33-3-5-3Z"/><path d="M16 13c-.65 0-1.27.08-1.84.23 1.13.84 1.84 1.95 1.84 3.27v2h5V17c0-2.2-2.33-4-5-4Z"/></svg>',
    'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h2v3H7V2Zm8 0h2v3h-2V2Z"/><path d="M4 5h16c1.1 0 2 .9 2 2v13c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2Zm0 6v9h16v-9H4Zm0-2h16V7H4v2Z"/></svg>',
    'comments' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v9c0 1.1-.9 2-2 2H8l-5 4v-4H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Zm2 5h12V7H6v2Zm0 4h9v-2H6v2Z"/></svg>',
    'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Zm8 9 8-5.2V6l-8 5.2L4 6v1.8L12 13Z"/></svg>',
    'alert' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 1 21h22L12 2Zm1 15h-2v2h2v-2Zm0-7h-2v6h2v-6Z"/></svg>',
    'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.05 3.41 9.74 8 11 4.59-1.26 8-5.95 8-11V5l-8-3Zm-1 14-3-3 1.4-1.4 1.6 1.58 4.6-4.58L17 10l-6 6Z"/></svg>',
    'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 5 20 12l-7 7-1.4-1.4 4.58-4.6H4v-2h12.18L11.6 6.4 13 5Z"/></svg>',
  ];

  return $icons[$name] ?? $icons['arrow'];
}

if ($dbConnected) {
  ensureEventOptionalColumns($conn);
  ensureEventInteractionTables($conn);
  ensureContactMessagesTable($conn);

  $q1 = $conn->query('SELECT COUNT(*) AS total FROM users');
  if ($q1 && $r = $q1->fetch_assoc()) { $totalUsers = (int)$r['total']; }

  $q2 = $conn->query('SELECT COUNT(*) AS total FROM events');
  if ($q2 && $r = $q2->fetch_assoc()) { $totalEvents = (int)$r['total']; }

  $q3 = $conn->query('SELECT COUNT(*) AS total FROM comments');
  if ($q3 && $r = $q3->fetch_assoc()) { $totalComments = (int)$r['total']; }

  $qContactTotal = $conn->query('SELECT COUNT(*) AS total FROM contact_messages');
  if ($qContactTotal && $r = $qContactTotal->fetch_assoc()) { $totalContactMessages = (int)$r['total']; }

  $qContactUnread = $conn->query('SELECT COUNT(*) AS total FROM contact_messages WHERE status = "unread"');
  if ($qContactUnread && $r = $qContactUnread->fetch_assoc()) { $unreadContactMessages = (int)$r['total']; }

  $qEventStatus = $conn->query('SELECT title, event_status FROM events');
  if ($qEventStatus) {
    while ($event = $qEventStatus->fetch_assoc()) {
      $status = normalizeEventStatus($event['event_status'] ?? 'pending', $event['title'] ?? '');
      if ($status === 'approved') {
        $approvedEvents++;
      } elseif ($status === 'rejected') {
        $rejectedEvents++;
      } else {
        $pendingEvents++;
      }
    }
  }
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero admin-dashboard-card reveal">
    <div class="admin-dashboard-copy">
      <h1>Admin Dashboard</h1>
      <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin'); ?>. Monitor activity, review submissions, and manage platform records.</p>
    </div>

    <div class="admin-dashboard-status">
      <article class="status-summary-card status-tile">
        <span class="dashboard-icon icon-events"><?php echo adminDashboardIcon('shield'); ?></span>
        <div>
          <h3>Approved Events</h3>
          <p class="metric"><?php echo number_format($approvedEvents); ?></p>
        </div>
      </article>
      <article class="status-summary-card status-tile">
        <span class="dashboard-icon icon-comments"><?php echo adminDashboardIcon('calendar'); ?></span>
        <div>
          <h3>Pending Events</h3>
          <p class="metric"><?php echo number_format($pendingEvents); ?></p>
        </div>
      </article>
      <article class="status-summary-card status-tile">
        <span class="dashboard-icon icon-alert"><?php echo adminDashboardIcon('alert'); ?></span>
        <div>
          <h3>Rejected Events</h3>
          <p class="metric"><?php echo number_format($rejectedEvents); ?></p>
        </div>
      </article>
    </div>
  </section>

  <div class="dashboard-grid admin-metric-grid">
    <article class="card reveal metric-card">
      <span class="dashboard-icon icon-users"><?php echo adminDashboardIcon('users'); ?></span>
      <div>
        <h3>Total Users</h3>
        <p class="metric"><?php echo number_format($totalUsers); ?></p>
      </div>
    </article>
    <article class="card reveal metric-card">
      <span class="dashboard-icon icon-events"><?php echo adminDashboardIcon('calendar'); ?></span>
      <div>
        <h3>Total Events</h3>
        <p class="metric"><?php echo number_format($totalEvents); ?></p>
      </div>
    </article>
    <article class="card reveal metric-card">
      <span class="dashboard-icon icon-comments"><?php echo adminDashboardIcon('comments'); ?></span>
      <div>
        <h3>Total Comments</h3>
        <p class="metric"><?php echo number_format($totalComments); ?></p>
      </div>
    </article>
    <article class="card reveal metric-card">
      <span class="dashboard-icon icon-mail"><?php echo adminDashboardIcon('mail'); ?></span>
      <div>
        <h3>Contact Messages</h3>
        <p class="metric"><?php echo number_format($totalContactMessages); ?></p>
      </div>
    </article>
    <article class="card reveal metric-card">
      <span class="dashboard-icon icon-alert"><?php echo adminDashboardIcon('alert'); ?></span>
      <div>
        <h3>Unread Messages</h3>
        <p class="metric"><?php echo number_format($unreadContactMessages); ?></p>
      </div>
    </article>
  </div>

  <section class="admin-card-grid">
    <a class="card reveal admin-action-card" href="manage_users.php">
      <span class="dashboard-icon icon-users"><?php echo adminDashboardIcon('users'); ?></span>
      <div>
        <h3>User Management</h3>
        <p>Review accounts, roles, and access status.</p>
      </div>
      <span class="action-arrow"><?php echo adminDashboardIcon('arrow'); ?></span>
    </a>
    <a class="card reveal admin-action-card" href="manage_events.php">
      <span class="dashboard-icon icon-events"><?php echo adminDashboardIcon('calendar'); ?></span>
      <div>
        <h3>Event Management</h3>
        <p>Approve, reject, or remove submitted events.</p>
      </div>
      <span class="action-arrow"><?php echo adminDashboardIcon('arrow'); ?></span>
    </a>
    <a class="card reveal admin-action-card" href="contact_messages.php">
      <span class="dashboard-icon icon-mail"><?php echo adminDashboardIcon('mail'); ?></span>
      <div>
        <h3>Contact Inbox</h3>
        <p>Read new messages and support requests.</p>
      </div>
      <span class="action-arrow"><?php echo adminDashboardIcon('arrow'); ?></span>
    </a>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
