<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/contact_support.php';
requireLogin('admin');

$pageTitle = 'Contact Messages - SkyMay';
$activePage = 'admin-messages';

$messages = [];
$flashMessage = '';
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $flashMessage = trim($_GET['msg'] ?? '');
  $flashType = ($_GET['type'] ?? '') === 'error' ? 'error' : 'success';
}

if ($dbConnected) {
  ensureContactMessagesTable($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbConnected) {
  $action = trim($_POST['action'] ?? '');
  $messageId = (int)($_POST['message_id'] ?? 0);

  if ($action === 'mark_read' && $messageId > 0) {
    $stmt = $conn->prepare('UPDATE contact_messages SET status = "read", read_at = NOW() WHERE message_id = ? AND LOWER(status) <> "read"');
    if ($stmt) {
      $stmt->bind_param('i', $messageId);
      if ($stmt->execute() && $stmt->affected_rows > 0) {
        $flashMessage = 'Message marked as read.';
      } else {
        $flashMessage = 'Message is already read or was not found.';
        $flashType = 'error';
      }
      $stmt->close();
    } else {
      $flashMessage = 'Unable to update message.';
      $flashType = 'error';
    }
  } else {
    $flashMessage = 'Invalid message selected.';
    $flashType = 'error';
  }

  $redirectMsg = rawurlencode($flashMessage);
  $redirectType = rawurlencode($flashType);
  header("Location: contact_messages.php?msg={$redirectMsg}&type={$redirectType}");
  exit;
}

if ($dbConnected) {
  $q = $conn->query('SELECT message_id, sender_name, sender_email, sender_role, message_body, status, created_at, read_at FROM contact_messages ORDER BY created_at DESC LIMIT 100');
  if ($q) {
    while ($row = $q->fetch_assoc()) {
      $messages[] = $row;
    }
  }
} else {
  $flashMessage = 'Database not connected. Messages cannot be loaded.';
  $flashType = 'error';
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero reveal">
    <h1>Contact Messages</h1>
    <p>Review messages submitted from the public contact form.</p>
    <div class="hero-actions">
      <a class="btn btn-secondary" href="admin.php">Back to Admin Dashboard</a>
    </div>
  </section>

  <?php if ($flashMessage !== ''): ?>
    <p class="badge <?php echo $flashType === 'error' ? 'badge-error' : ''; ?>"><?php echo htmlspecialchars($flashMessage); ?></p>
  <?php endif; ?>

  <section class="card reveal table-card">
    <?php if (count($messages) === 0): ?>
      <p>No contact messages found.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Sender</th>
              <th>Role</th>
              <th>Message</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $message): ?>
              <?php $status = normalizeContactStatus($message['status'] ?? 'unread'); ?>
              <tr>
                <td><?php echo (int)$message['message_id']; ?></td>
                <td>
                  <strong><?php echo htmlspecialchars($message['sender_name'] ?? ''); ?></strong><br>
                  <span class="muted-list-item"><?php echo htmlspecialchars($message['sender_email'] ?? ''); ?></span>
                </td>
                <td><?php echo htmlspecialchars(ucfirst($message['sender_role'] ?? '')); ?></td>
                <td class="message-cell"><?php echo nl2br(htmlspecialchars($message['message_body'] ?? '')); ?></td>
                <td>
                  <span class="badge <?php echo $status === 'unread' ? 'badge-warning' : ''; ?>">
                    <?php echo ucfirst($status); ?>
                  </span>
                  <?php if ($status === 'read' && !empty($message['read_at'])): ?>
                    <br><span class="muted-list-item"><?php echo htmlspecialchars($message['read_at']); ?></span>
                  <?php endif; ?>
                </td>
                <td class="submitted-cell"><?php echo htmlspecialchars($message['created_at'] ?? ''); ?></td>
                <td class="table-actions">
                  <?php if ($status === 'unread'): ?>
                    <form method="post" class="inline-form">
                      <input type="hidden" name="action" value="mark_read">
                      <input type="hidden" name="message_id" value="<?php echo (int)$message['message_id']; ?>">
                      <button class="btn btn-secondary" type="submit">Mark Read</button>
                    </form>
                  <?php else: ?>
                    <span class="badge">Done</span>
                  <?php endif; ?>
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
