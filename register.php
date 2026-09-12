<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/user_support.php';

$pageTitle = 'Register - SkyMay';
$activePage = 'register';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = strtolower(trim($_POST['role'] ?? 'student'));
  $allowedRoles = ['student', 'organizer', 'admin'];

  if ($name === '' || $email === '' || $password === '') {
    $error = 'Please complete all fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } elseif (!in_array($role, $allowedRoles, true)) {
    $error = 'Please choose a valid role.';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';
  } elseif (!$dbConnected) {
    $error = 'Database not connected. Cannot register now.';
  } else {
    ensureUserStatusColumn($conn);
    $passwordHash = md5($password);
    $dbRole = ucfirst($role);

    $stmt = $conn->prepare('INSERT INTO users (fullname, email, password, role, status) VALUES (?, ?, ?, ?, "active")');
    if ($stmt) {
      $stmt->bind_param('ssss', $name, $email, $passwordHash, $dbRole);
      if ($stmt->execute()) {
        $message = 'Registration successful. Please login.';
      } else {
        $error = 'Registration failed. Email may already exist.';
      }
      $stmt->close();
    } else {
      $error = 'Unable to process registration.';
    }
  }
}

include 'includes/header.php';
?>
<main class="auth-layout">
  <form class="card auth-card reveal" id="registerForm" method="post" action="register.php">
    <div class="auth-head">
      <h1>Create Your SkyMay Account</h1>
      <p>Choose your role to unlock the right features.</p>
    </div>

    <?php if ($message !== ''): ?><p class="badge"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <label>Full Name<input name="full_name" type="text" required /></label>
    <label>Email<input name="email" type="email" required /></label>

    <label>Role
      <select name="role" required>
        <option value="student">Student</option>
        <option value="organizer">Organizer</option>
        <option value="admin">Admin</option>
      </select>
    </label>

    <div class="input-group">
      <label>Password<input id="registerPassword" name="password" type="password" required /></label>
      <button class="toggle-password" type="button" data-target="registerPassword">Show</button>
    </div>

    <button class="btn btn-primary" type="submit">Register</button>
    <p>Already have account? <a href="login.php">Login</a></p>
  </form>
</main>
<?php include 'includes/footer.php'; ?>
