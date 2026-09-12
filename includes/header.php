<?php
require_once __DIR__ . '/auth.php';
if (!isset($pageTitle)) {
  $pageTitle = 'SkyMay';
}
if (!isset($activePage)) {
  $activePage = '';
}
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="stylesheet" href="style.css" />
</head>
<body data-nav="<?php echo htmlspecialchars($activePage); ?>">
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="homepage.php">SkyMay</a>
      <button class="nav-toggle btn btn-ghost" id="navToggle" type="button">Menu</button>
      <nav class="nav-main" id="mainNav">
        <a href="homepage.php" data-nav="home">Home</a>
        <a href="event.php" data-nav="events">Explore Events</a>
        <a href="about.php" data-nav="about">About</a>
        <a href="faq.php" data-nav="faq">FAQ</a>
        <a href="contact.php" data-nav="contact">Contact</a>

        <div class="dropdown">
          <button class="dropbtn" type="button">Portal</button>
          <div class="dropdown-content">
            <?php if ($user): ?>
              <?php if (($user['role'] ?? '') === 'admin'): ?>
                <a href="admin.php" data-nav="admin">Admin Dashboard</a>
                <a href="manage_users.php" data-nav="admin-users">User Management</a>
                <a href="manage_events.php" data-nav="admin-events">Event Management</a>
                <a href="contact_messages.php" data-nav="admin-messages">Contact Messages</a>
              <?php elseif (($user['role'] ?? '') === 'organizer'): ?>
                <a href="organizer.php" data-nav="organizer">Organizer Dashboard</a>
                <a href="createevent.php" data-nav="create">Create Event</a>
              <?php else: ?>
                <a href="student.php" data-nav="student">Student Dashboard</a>
              <?php endif; ?>
              <?php if (!in_array(($user['role'] ?? ''), ['admin', 'organizer'], true)): ?>
                <a href="eventdetail.php" data-nav="detail">Event Detail</a>
              <?php endif; ?>
              <a href="logout.php">Logout</a>
            <?php else: ?>
              <a href="login.php" data-nav="login">Login</a>
              <a href="register.php" data-nav="register">Register</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
      <div class="nav-extra">
        <button class="btn btn-ghost" id="themeToggle" type="button">Theme</button>
      </div>
    </div>
  </header>
