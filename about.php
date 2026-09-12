<?php
$pageTitle = 'About - SkyMay';
$activePage = 'about';
include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero page-hero-media reveal">
    <div>
      <h1>About SkyMay</h1>
      <p>SkyMay is a professional campus event sharing ecosystem that centralizes announcements, engagement, and role-driven management into one reliable platform.</p>
    </div>
    <img class="page-image" src="assets/content.png" alt="Students connecting at a campus event">
  </section>

  <section class="info-grid">
    <article class="card reveal">
      <h3>Project Background</h3>
      <p>Students regularly miss opportunities due to fragmented communication across social media, messaging apps, and email groups. SkyMay closes this gap with a central discovery hub.</p>
    </article>
    <article class="card reveal">
      <h3>Objectives</h3>
      <p>Improve event visibility, streamline organizer publishing, strengthen social interaction, and provide measurable participation insights for admin teams.</p>
    </article>
    <article class="card reveal">
      <h3>Target Users</h3>
      <p>Students discover and join events, organizers create and optimize campaigns, while admins monitor platform quality and ecosystem health.</p>
    </article>
  </section>

  <section class="card reveal section">
    <h2>System Architecture Overview</h2>
    <p>Frontend: HTML/CSS/JavaScript | Backend: PHP | Database: MySQL (`skymay_db`).</p>
    <p>The system uses role-based access with secure session flow, dynamic event loading, and structured table relationships for users, events, comments, and likes.</p>
  </section>

  <section class="info-grid">
    <article class="card reveal">
      <h3>Student Value</h3>
      <p>Easy event exploration, interaction, and participation tracking in one polished interface.</p>
    </article>
    <article class="card reveal">
      <h3>Organizer Value</h3>
      <p>Dedicated dashboard, event publishing workflow, and direct audience feedback from comments.</p>
    </article>
    <article class="card reveal">
      <h3>Admin Value</h3>
      <p>Central moderation and live metrics for strategic decisions and platform governance.</p>
    </article>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
