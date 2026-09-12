<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/user_support.php';
requireLogin('admin');

$pageTitle = 'User Management - SkyMay';
$activePage = 'admin-users';

$users = [];
$flashMessage = '';
$flashType = 'success';

if ($dbConnected) {
  ensureUserStatusColumn($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbConnected) {
  $action = trim($_POST['action'] ?? '');
  $userId = (int)($_POST['user_id'] ?? 0);

  if ($userId > 0 && ($action === 'deactivate_user' || $action === 'activate_user' || $action === 'delete_user')) {
    if ($action === 'delete_user') {
      $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ? AND LOWER(role) <> "admin"');
      if ($stmt) {
        $stmt->bind_param('i', $userId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
          $flashMessage = 'User deleted successfully.';
        } else {
          $flashMessage = 'Unable to delete user. Admin accounts are protected.';
          $flashType = 'error';
        }
        $stmt->close();
      }
    } else {
      $nextStatus = $action === 'deactivate_user' ? 'inactive' : 'active';
      $stmt = $conn->prepare('UPDATE users SET status = ? WHERE user_id = ? AND LOWER(role) <> "admin"');
      if ($stmt) {
        $stmt->bind_param('si', $nextStatus, $userId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
          $flashMessage = $action === 'deactivate_user' ? 'User deactivated successfully.' : 'User activated successfully.';
        } else {
          $flashMessage = 'Unable to update user status.';
          $flashType = 'error';
        }
        $stmt->close();
      }
    }
  } else {
    $flashMessage = 'Invalid user selected.';
    $flashType = 'error';
  }

  $redirectMsg = rawurlencode($flashMessage);
  $redirectType = rawurlencode($flashType);
  header("Location: manage_users.php?msg={$redirectMsg}&type={$redirectType}");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $flashMessage = trim($_GET['msg'] ?? '');
  $flashType = ($_GET['type'] ?? '') === 'error' ? 'error' : 'success';
}

if ($dbConnected) {
  $q = $conn->query('SELECT user_id, fullname, email, role, status FROM users ORDER BY user_id DESC LIMIT 50');
  if ($q) {
    while ($row = $q->fetch_assoc()) {
      $users[] = $row;
    }
  }
}

include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero reveal">
    <h1>User Management</h1>
    <p>Manage user account access without changing assigned roles.</p>
    <div class="hero-actions">
      <a class="btn btn-secondary" href="admin.php">Back to Admin Dashboard</a>
    </div>
  </section>

  <?php if ($flashMessage !== ''): ?>
    <p class="badge <?php echo $flashType === 'error' ? 'badge-error' : ''; ?>"><?php echo htmlspecialchars($flashMessage); ?></p>
  <?php endif; ?>

  <section class="card reveal table-card">
    <?php if (count($users) === 0): ?>
      <p>No users found.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <?php
                $role = strtolower($user['role'] ?? '');
                $status = normalizeUserStatus($user['status'] ?? 'active');
              ?>
              <tr>
                <td><?php echo (int)$user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['fullname'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($user['role'] ?? ''); ?></td>
                <td><span class="badge <?php echo $status === 'inactive' ? 'badge-error' : ''; ?>"><?php echo ucfirst($status); ?></span></td>
                <td class="table-actions">
                  <?php if ($role !== 'admin'): ?>
                    <form method="post" class="inline-form">
                      <input type="hidden" name="action" value="<?php echo $status === 'inactive' ? 'activate_user' : 'deactivate_user'; ?>">
                      <input type="hidden" name="user_id" value="<?php echo (int)$user['user_id']; ?>">
                      <button class="btn btn-secondary" type="submit"><?php echo $status === 'inactive' ? 'Activate' : 'Deactivate'; ?></button>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this user?');">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?php echo (int)$user['user_id']; ?>">
                      <button class="btn btn-ghost" type="submit">Delete</button>
                    </form>
                  <?php else: ?>
                    <span class="badge">Protected</span>
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
