<?php
$pageTitle = 'Homepage - SkyMay';
$activePage = 'home';
include 'includes/header.php';
?>
<main>
  <section class="hero">
    <div class="container hero-grid">
      <div class="reveal">
        <span class="badge">Campus Event Social Platform</span>
        <h1>Discover Every Campus Opportunity in One Place</h1>
        <p>SkyMay unifies workshops, competitions, club activities, talks, and career sessions into a single modern platform so students never miss important events.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="event.php">Explore Events</a>
        </div>
      </div>
      <div class="card stat-card visual-card reveal">
        <img class="page-image" src="assets/campus-students.jpg" alt="Students gathering on campus">
        <h3>Today in SkyMay</h3>
        <ul>
          <li><strong>16</strong> active events across campus</li>
          <li><strong>423</strong> student interactions this week</li>
          <li><strong>89%</strong> attendance uplift reported</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="section container">
    <h2>Why SkyMay Feels Professional</h2>
    <div class="info-grid">
      <article class="card reveal">
        <h3>Unified Event Discovery</h3>
        <p>Stop checking multiple channels. Search, filter, and access every campus event from one clean feed.</p>
      </article>
      <article class="card reveal">
        <h3>Social Engagement Layer</h3>
        <p>Students can like and comment on event pages, helping organizers gauge interest and improve turnout.</p>
      </article>
      <article class="card reveal">
        <h3>Role-Based Control</h3>
        <p>Each role gets the right workspace: student dashboard, organizer publishing tools, and admin analytics.</p>
      </article>
    </div>
  </section>

  <section class="section container">
    <h2>Platform Advantages</h2>
    <div class="features-grid">
      <article class="card feature reveal">
        <h3>Smart Visibility</h3>
        <p>Event titles, dates, and venue details are presented in a high readability card layout for quick scanning.</p>
      </article>
      <article class="card feature reveal">
        <h3>Secure Access Design</h3>
        <p>Login by role ensures users only access relevant functions, reducing confusion and protecting admin tools.</p>
      </article>
      <article class="card feature reveal">
        <h3>Data-Driven Dashboard</h3>
        <p>Administrative metrics from `skymay_db` help monitor user growth, event publishing, and interaction trends.</p>
      </article>
      <article class="card feature reveal">
        <h3>Responsive Experience</h3>
        <p>From desktop to mobile, the interface adapts with dropdown navigation, clear typography, and compact cards.</p>
      </article>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
