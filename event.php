<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';

$pageTitle = 'Events - SkyMay';
$activePage = 'events';
$events = [];
$user = currentUser();
$isLoggedIn = isLoggedIn();
$isStudent = $isLoggedIn && ($user['role'] ?? '') === 'student';
$userRegistrations = [];

if ($dbConnected) {
  ensureEventOptionalColumns($conn);
  ensureEventInteractionTables($conn);

  $sql = "SELECT event_id, title, category, event_date, venue, description, image_path, event_link FROM events WHERE LOWER(COALESCE(event_status, 'pending')) <> 'rejected' AND TRIM(title) NOT LIKE '[Rejected]%' ORDER BY event_date ASC LIMIT 12";
  $result = $conn->query($sql);
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $events[] = $row;
    }
  }

  if ($isStudent) {
    $regStmt = $conn->prepare('SELECT event_id FROM event_registrations WHERE user_id = ? AND status = "registered"');
    if ($regStmt) {
      $userId = (int)($user['id'] ?? 0);
      $regStmt->bind_param('i', $userId);
      $regStmt->execute();
      $regResult = $regStmt->get_result();
      while ($row = $regResult->fetch_assoc()) {
        $userRegistrations[(int)$row['event_id']] = true;
      }
      $regStmt->close();
    }
  }
}

function getEventCategoryFromTitle(string $title): string {
  $titleLower = strtolower($title);
  if (strpos($titleLower, 'hackathon') !== false || strpos($titleLower, 'competition') !== false || strpos($titleLower, 'challenge') !== false) {
    return 'competition';
  }
  if (strpos($titleLower, 'career') !== false || strpos($titleLower, 'fair') !== false || strpos($titleLower, 'job') !== false) {
    return 'career';
  }
  if (strpos($titleLower, 'workshop') !== false || strpos($titleLower, 'training') !== false || strpos($titleLower, 'seminar') !== false) {
    return 'workshop';
  }
  return 'other';
}

function eventCategoryLabel(string $category): string {
  switch ($category) {
    case 'competition':
      return 'Competition';
    case 'career':
      return 'Career';
    case 'workshop':
      return 'Workshop';
    default:
      return 'Event';
  }
}

if (!$dbConnected && count($events) === 0) {
  $events = [
    ['event_id' => 1, 'title' => 'Hackathon 2026', 'category' => 'competition', 'event_date' => '2026-05-20', 'venue' => 'APU Innovation Lab', 'description' => 'Innovation challenge for all faculties.', 'image_path' => null, 'event_link' => null],
    ['event_id' => 2, 'title' => 'Career Fair', 'category' => 'career', 'event_date' => '2026-06-03', 'venue' => 'Main Hall', 'description' => 'Meet top employers and internship partners.', 'image_path' => null, 'event_link' => null],
    ['event_id' => 3, 'title' => 'Photography Workshop', 'category' => 'workshop', 'event_date' => '2026-06-14', 'venue' => 'Block B Studio', 'description' => 'Hands-on mobile and DSLR techniques.', 'image_path' => null, 'event_link' => null]
  ];
}

include 'includes/header.php';
?>
<main class="container section">
  <h1 class="reveal">Campus Events</h1>
  <?php if (!$dbConnected): ?>
    <p class="badge badge-error">Database offline. Showing demo events.</p>
  <?php endif; ?>

  <div class="toolbar">
    <input id="searchInput" type="text" placeholder="Search by event title..." oninput="window.SkyMayFilterEvents && window.SkyMayFilterEvents()" />
    <select id="categoryFilter" onchange="window.SkyMayFilterEvents && window.SkyMayFilterEvents()">
      <option value="all">All Categories</option>
      <option value="competition">Competition</option>
      <option value="career">Career</option>
      <option value="workshop">Workshop</option>
      <option value="other">Other</option>
    </select>
  </div>

  <div class="event-grid" id="eventGrid">
    <?php if ($dbConnected && count($events) === 0): ?>
      <p class="badge">No events available right now.</p>
    <?php endif; ?>
    <?php foreach ($events as $event): ?>
      <?php
        $category = normalizeEventCategory((string)($event['category'] ?? ''));
        if ($category === 'other' && empty($event['category'])) {
          $category = getEventCategoryFromTitle((string)$event['title']);
        }
        $isJoined = isset($userRegistrations[(int)$event['event_id']]);
        $description = (string)($event['description'] ?? '');
      ?>
      <article class="card event-item reveal"
               data-event-id="<?php echo (int)$event['event_id']; ?>"
               data-title="<?php echo htmlspecialchars($event['title']); ?>"
               data-category="<?php echo htmlspecialchars($category); ?>">
        <img
          class="event-image"
          src="<?php echo htmlspecialchars(eventImageUrl($event)); ?>"
          alt="<?php echo htmlspecialchars($event['title']); ?>"
          loading="lazy"
        />
        <span class="badge"><?php echo htmlspecialchars(eventCategoryLabel($category)); ?></span>
        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
        <div class="event-meta">Date: <?php echo htmlspecialchars($event['event_date']); ?> | Venue: <?php echo htmlspecialchars($event['venue']); ?></div>
        <p><?php echo htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? '...' : ''); ?></p>
        <div class="event-actions">
          <a href="eventdetail.php?id=<?php echo urlencode((string)$event['event_id']); ?>" class="btn btn-secondary">View Details</a>
          <?php if (!empty($event['event_link'])): ?>
            <a href="<?php echo htmlspecialchars($event['event_link']); ?>" class="btn btn-ghost" target="_blank" rel="noopener noreferrer">Open Link</a>
          <?php endif; ?>
          <?php if ($isStudent): ?>
            <button class="btn <?php echo $isJoined ? 'btn-ghost' : 'btn-primary'; ?> join-btn"
                    type="button"
                    data-event-id="<?php echo (int)$event['event_id']; ?>"
                    data-joined="<?php echo $isJoined ? '1' : '0'; ?>">
              <?php echo $isJoined ? 'Joined' : 'Join Event'; ?>
            </button>
          <?php elseif (!$isLoggedIn): ?>
            <a href="login.php" class="btn btn-ghost">Login to Join</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <p id="filterResultMsg" class="filter-result" aria-live="polite"></p>
</main>

<script>
(function () {
  var searchInput = document.getElementById('searchInput');
  var categoryFilter = document.getElementById('categoryFilter');
  var filterResult = document.getElementById('filterResultMsg');

  function filterEvents() {
    var searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : 'all';
    var eventItems = document.querySelectorAll('.event-item');
    var visibleCount = 0;

    for (var i = 0; i < eventItems.length; i += 1) {
      var item = eventItems[i];
      var title = (item.getAttribute('data-title') || '').toLowerCase();
      var itemCategory = (item.getAttribute('data-category') || '').toLowerCase();
      var isVisible = title.indexOf(searchTerm) !== -1 && (selectedCategory === 'all' || itemCategory === selectedCategory);

      item.style.display = isVisible ? '' : 'none';
      if (isVisible) visibleCount += 1;
    }

    if (filterResult) {
      filterResult.textContent = visibleCount === 0
        ? 'No events found. Try a different search or category.'
        : 'Showing ' + visibleCount + ' event' + (visibleCount === 1 ? '' : 's') + '.';
    }
  }

  if (searchInput) searchInput.addEventListener('input', filterEvents);
  if (categoryFilter) categoryFilter.addEventListener('change', filterEvents);
  window.SkyMayFilterEvents = filterEvents;
  filterEvents();
})();
</script>

<?php if ($isStudent): ?>
<script>
async function handleJoin(eventId, currentJoined) {
  const action = currentJoined ? 'cancel' : 'join';
  const formData = new FormData();
  formData.append('event_id', eventId);
  formData.append('action', action);

  try {
    const response = await fetch('join_event_handler.php', {
      method: 'POST',
      body: formData
    });
    const data = await response.json();

    if (!data.success) {
      window.SkyMayToast?.show(data.message || 'Operation failed', 'error');
      return;
    }

    const btn = document.querySelector(`.join-btn[data-event-id="${eventId}"]`);
    if (btn) {
      const joined = data.action === 'joined';
      btn.setAttribute('data-joined', joined ? '1' : '0');
      btn.textContent = joined ? 'Joined' : 'Join Event';
      btn.classList.toggle('btn-primary', !joined);
      btn.classList.toggle('btn-ghost', joined);
    }

    window.SkyMayToast?.show(data.message || 'Updated successfully', 'success');
  } catch (error) {
    console.error(error);
    window.SkyMayToast?.show('Network error, please try again', 'error');
  }
}

document.querySelectorAll('.join-btn').forEach((btn) => {
  btn.addEventListener('click', function () {
    const eventId = this.getAttribute('data-event-id');
    const currentJoined = this.getAttribute('data-joined') === '1';
    handleJoin(eventId, currentJoined);
  });
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
