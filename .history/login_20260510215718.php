<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/user_support.php';

if (isLoggedIn()) {
  $role = $_SESSION['user']['role'] ?? 'student';
  if ($role === 'admin') { header('Location: admin.php'); exit; }
  if ($role === 'organizer') { header('Location: organizer.php'); exit; }
  header('Location: student.php'); exit;
}

$pageTitle = 'Login - SkyMay';
$activePage = 'login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = strtolower(trim($_POST['role'] ?? 'student'));
  $allowedRoles = ['student', 'organizer', 'admin'];
  $dbRole = in_array($role, $allowedRoles, true) ? $role : 'student';
  $user = null;

  if ($dbConnected) {
    ensureUserStatusColumn($conn);
    $stmt = $conn->prepare('SELECT user_id, fullname, email, password, role, status FROM users WHERE email = ? AND LOWER(role) = ? LIMIT 1');
    if ($stmt) {
      $stmt->bind_param('ss', $email, $dbRole);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result && $row = $result->fetch_assoc()) {
        $storedPassword = (string)$row['password'];
        $passwordMatches = hash_equals(md5($password), $storedPassword) || password_verify($password, $storedPassword);
        if ($passwordMatches && normalizeUserStatus($row['status'] ?? 'active') === 'active') {
          $user = [
            'id' => (int)$row['user_id'],
            'name' => $row['fullname'],
            'email' => $row['email'],
            'role' => strtolower($row['role'])
          ];
        }
      }
      $stmt->close();
    }
  }

  if ($user) {
    $_SESSION['user'] = $user;
    if ($user['role'] === 'admin') { header('Location: admin.php'); exit; }
    if ($user['role'] === 'organizer') { header('Location: organizer.php'); exit; }
    header('Location: student.php');
    exit;
  }

  $error = 'Invalid credentials, inactive account, or role mismatch.';
}

include 'includes/header.php';
?>
<main id="mainContent" class="auth-layout auth-split">
  <section class="card auth-panel reveal" aria-label="SkyMay login benefits">
    <div>
      <span class="eyebrow">Role-based access</span>
      <h1>One login for every campus workflow.</h1>
      <p>Students join events, organizers publish opportunities, and admins manage platform quality from dedicated workspaces.</p>
    </div>
    <div class="stat-list">
      <article class="stat-item">
        <span class="stat-value">3</span>
        <p class="stat-label">Dedicated role portals</p>
      </article>
      <article class="stat-item">
        <span class="stat-value">1</span>
        <p class="stat-label">Unified event platform</p>
      </article>
    </div>
  </section>
  <form class="card auth-card reveal" id="loginForm" method="post" action="login.php">
    <div class="auth-head">
      <h1>Welcome Back to SkyMay</h1>
      <p>Login with your role to open the correct dashboard.</p>
    </div>

    <?php if ($error !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <label>Email
      <input name="email" type="email" placeholder="you@skymay.com" required />
    </label>

    <label>Role
      <select name="role" required>
        <option value="student">Student</option>
        <option value="organizer">Organizer</option>
        <option value="admin">Admin</option>
      </select>
    </label>

    <div class="input-group">
      <label>Password
        <input id="loginPassword" name="password" type="password" required />
      </label>
      <button class="toggle-password" type="button" data-target="loginPassword">Show</button>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Login</button>
    </div>

    <p>No account? <a href="register.php">Register</a></p>
  </form>
</main>
<?php include 'includes/footer.php'; ?>    
