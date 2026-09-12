<?php
require_once 'db.php';
require_once __DIR__ . '/includes/contact_support.php';

$pageTitle = 'Contact - SkyMay';
$activePage = 'contact';
$allowedRoles = [
  'student' => 'Student',
  'organizer' => 'Organizer',
  'admin' => 'Admin'
];
$formValues = [
  'name' => '',
  'email' => '',
  'role' => 'student',
  'message' => ''
];
$successMessage = '';
$errorMessage = '';

if (isset($_GET['sent']) && $_GET['sent'] === '1') {
  $successMessage = 'Message sent successfully. An admin will review it soon.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $formValues['name'] = trim($_POST['name'] ?? '');
  $formValues['email'] = trim($_POST['email'] ?? '');
  $formValues['role'] = normalizeContactRole($_POST['role'] ?? '');
  $formValues['message'] = trim($_POST['message'] ?? '');

  if ($formValues['name'] === '' || $formValues['email'] === '' || $formValues['role'] === '' || $formValues['message'] === '') {
    $errorMessage = 'Please complete all fields.';
  } elseif (!filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)) {
    $errorMessage = 'Please enter a valid email address.';
  } elseif (!$dbConnected) {
    $errorMessage = 'Database not connected. Please try again later.';
  } else {
    ensureContactMessagesTable($conn);
    $stmt = $conn->prepare('INSERT INTO contact_messages (sender_name, sender_email, sender_role, message_body, status) VALUES (?, ?, ?, ?, "unread")');
    if ($stmt) {
      $stmt->bind_param('ssss', $formValues['name'], $formValues['email'], $formValues['role'], $formValues['message']);
      if ($stmt->execute()) {
        $stmt->close();
        header('Location: contact.php?sent=1');
        exit;
      }
      $errorMessage = 'Database insert failed: ' . $stmt->error;
      $stmt->close();
    } else {
      $errorMessage = 'Database prepare failed: ' . $conn->error;
    }
  }
}

include 'includes/header.php';
?>
<main class="container section split-grid">
  <section class="card page-hero reveal">
    <h1>Contact SkyMay Team</h1>
    <p>We support students, organizers, and admins for technical and platform questions.</p>
    <ul class="contact-list">
      <li><strong>Support Email:</strong> support@skymay.local</li>
      <li><strong>Technical Desk:</strong> +60 12-345 6789</li>
      <li><strong>Office:</strong> Campus Student Affairs Center</li>
      <li><strong>Working Hours:</strong> Mon-Fri, 9:00 AM - 6:00 PM</li>
    </ul>
  </section>

  <section class="card reveal">
    <h3>Send Message</h3>
    <?php if ($successMessage !== ''): ?><p class="badge"><?php echo htmlspecialchars($successMessage); ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($errorMessage); ?></p><?php endif; ?>

    <form id="contactForm" method="post" action="contact.php">
      <label>Name<input name="name" type="text" value="<?php echo htmlspecialchars($formValues['name']); ?>" required /></label>
      <label>Email<input name="email" type="email" value="<?php echo htmlspecialchars($formValues['email']); ?>" required /></label>
      <label>Role
        <select name="role" required>
          <?php foreach ($allowedRoles as $roleValue => $roleLabel): ?>
            <option value="<?php echo htmlspecialchars($roleValue); ?>" <?php echo $formValues['role'] === $roleValue ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($roleLabel); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Message<textarea name="message" rows="4" required><?php echo htmlspecialchars($formValues['message']); ?></textarea></label>
      <button class="btn btn-primary" type="submit">Send</button>
    </form>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
