<?php
$pageTitle = 'FAQ - SkyMay';
$activePage = 'faq';
include 'includes/header.php';
?>
<main class="container section">
  <section class="card page-hero page-hero-media reveal">
    <div>
      <h1>Frequently Asked Questions</h1>
      <p>Everything you need to know about account roles, event workflows, and platform operations.</p>
    </div>
    <img class="page-image" src="assets/faq.avif" alt="Campus students discussing event information">
  </section>

  <section class="card reveal">
    <details class="faq-item" open>
      <summary>How do I access my dashboard?</summary>
      <p>Login with your selected role. Student, organizer, and admin each have dedicated dashboard pages with role-based access.</p>
    </details>
    <details class="faq-item">
      <summary>Can students create events?</summary>
      <p>No. Only organizer accounts can access the Create Event page and publish new events.</p>
    </details>
    <details class="faq-item">
      <summary>Why can I not open admin dashboard?</summary>
      <p>Admin dashboard is protected. You must login with an admin role account to access it.</p>
    </details>
    <details class="faq-item">
      <summary>Does the system store events in database?</summary>
      <p>Yes. When connected to MySQL, events are stored in `skymay_db` and loaded in the Explore Events page.</p>
    </details>
    <details class="faq-item">
      <summary>What if MySQL is offline?</summary>
      <p>Some pages fall back to demo content; create/register actions that require DB will display connection errors.</p>
    </details>
    <details class="faq-item">
      <summary>Why is Create Event not visible on homepage?</summary>
      <p>Create Event belongs to the Organizer Dashboard. This keeps student view simple and role-appropriate.</p>
    </details>
    <details class="faq-item">
      <summary>How does Event View Detail work?</summary>
      <p>In Explore Events, each card's View Details button routes to `eventdetail.php` (with event id when available).</p>
    </details>
    <details class="faq-item">
      <summary>Can admin and organizer use the same login page?</summary>
      <p>Yes. Choose the correct role on login, and the system redirects to the matching dashboard automatically.</p>
    </details>
    <details class="faq-item">
      <summary>What should be in database for real login?</summary>
      <p>The `users` table should contain `fullname`, `email`, `password`, `role`, and `status`. Existing MD5 passwords still work, and stronger PHP password hashes are also accepted in the `password` column.</p>
    </details>
  </section>

  <section class="info-grid">
    <article class="card reveal">
      <h3>Quick Tip</h3>
      <p>Use campus email format for better account management and easier user segmentation.</p>
    </article>
    <article class="card reveal">
      <h3>Security Note</h3>
      <p>Never store plain text passwords in database. Always use strong hashes and session-based authentication.</p>
    </article>
    <article class="card reveal">
      <h3>Support</h3>
      <p>If access fails by role, verify your role value in database and retry login with matching selection.</p>
    </article>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
