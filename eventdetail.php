<?php
require_once 'db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/event_support.php';

$pageTitle = 'Event Detail - SkyMay';
$activePage = 'detail';
$event = [
  'event_id' => 1,
  'title' => 'Hackathon 2026',
  'event_date' => '2026-05-20',
  'venue' => 'APU Innovation Lab',
  'description' => 'Join teams, build solutions, and win prizes.',
  'image_path' => null,
  'event_link' => null
];

$user = currentUser();
$isStudent = $user && (($user['role'] ?? '') === 'student');
$userId = $isStudent ? (int)$user['id'] : 0;
$message = '';
$error = '';
$eventId = isset($_GET['id']) ? max(0, (int)$_GET['id']) : (isset($_POST['event_id']) ? max(0, (int)$_POST['event_id']) : 0);
$eventFound = !$dbConnected;
$selectedJoinedEvent = false;
$studentJoinedEvent = false;
$joinedEvents = [];
$showJoinedEventsList = $dbConnected && $isStudent && $eventId <= 0 && !isset($_GET['id']);

function eventDetailRedirectUrl(int $eventId, string $messageKey): string
{
  if (!isset($_GET['id'])) {
    return 'eventdetail.php?msg=' . urlencode($messageKey) . '#event-' . urlencode((string)$eventId);
  }

  return 'eventdetail.php?id=' . urlencode((string)$eventId) . '&msg=' . urlencode($messageKey);
}

function loadEventDetailInteractions(mysqli $conn, array &$eventRow, bool $isStudent, int $userId): void
{
  $eventRow['like_count'] = 0;
  $eventRow['user_liked'] = false;
  $eventRow['comments'] = [];
  $targetEventId = (int)$eventRow['event_id'];

  $likeQ = $conn->prepare('SELECT COUNT(*) AS total FROM likes WHERE event_id = ?');
  if ($likeQ) {
    $likeQ->bind_param('i', $targetEventId);
    $likeQ->execute();
    $res = $likeQ->get_result();
    if ($res && $row = $res->fetch_assoc()) {
      $eventRow['like_count'] = (int)$row['total'];
    }
    $likeQ->close();
  }

  if ($isStudent) {
    $likedQ = $conn->prepare('SELECT like_id FROM likes WHERE event_id = ? AND user_id = ? LIMIT 1');
    if ($likedQ) {
      $likedQ->bind_param('ii', $targetEventId, $userId);
      $likedQ->execute();
      $likedRes = $likedQ->get_result();
      $eventRow['user_liked'] = (bool)($likedRes && $likedRes->num_rows > 0);
      $likedQ->close();
    }
  }

  $commentQ = $conn->prepare('
    SELECT c.comment_text, c.created_at, u.fullname
    FROM comments c
    JOIN users u ON u.user_id = c.user_id
    WHERE c.event_id = ?
    ORDER BY c.created_at DESC
  ');
  if ($commentQ) {
    $commentQ->bind_param('i', $targetEventId);
    $commentQ->execute();
    $res = $commentQ->get_result();
    while ($res && $row = $res->fetch_assoc()) {
      $eventRow['comments'][] = $row;
    }
    $commentQ->close();
  }
}

if ($dbConnected) {
  ensureEventOptionalColumns($conn);
  ensureEventInteractionTables($conn);
}

if ($showJoinedEventsList) {
  $joinedStmt = $conn->prepare('
    SELECT e.event_id, e.title, e.event_date, e.venue, e.description, e.image_path, e.event_link, er.registered_at
    FROM event_registrations er
    JOIN events e ON e.event_id = er.event_id
    WHERE er.user_id = ? AND er.status = "registered"
    ORDER BY e.event_date ASC, er.registered_at DESC
  ');
  if ($joinedStmt) {
    $joinedStmt->bind_param('i', $userId);
    $joinedStmt->execute();
    $joinedResult = $joinedStmt->get_result();
    while ($joinedResult && $joinedRow = $joinedResult->fetch_assoc()) {
      $joinedEvents[] = $joinedRow;
    }
    $eventFound = count($joinedEvents) > 0;
    $joinedStmt->close();
  }

  foreach ($joinedEvents as &$joinedEvent) {
    loadEventDetailInteractions($conn, $joinedEvent, $isStudent, $userId);
  }
  unset($joinedEvent);
}

if (!$dbConnected && $eventId <= 0) {
  $eventId = (int)$event['event_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$isStudent) {
    header('Location: login.php');
    exit;
  }

  if (!$dbConnected) {
    $error = 'Database not connected. Unable to submit interaction.';
  } elseif ($eventId <= 0) {
    $error = 'Please choose an event first.';
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'like') {
      $stmt = $conn->prepare('INSERT IGNORE INTO likes (event_id, user_id) VALUES (?, ?)');
      if ($stmt) {
        $stmt->bind_param('ii', $eventId, $userId);
        if (!$stmt->execute()) {
          $error = 'Unable to save like right now.';
        }
        $stmt->close();
      }
      if ($error === '') {
        header('Location: ' . eventDetailRedirectUrl($eventId, 'liked'));
        exit;
      }
    }

    if ($action === 'comment') {
      $commentText = trim($_POST['comment_text'] ?? '');
      if ($commentText !== '') {
        $stmt = $conn->prepare('INSERT INTO comments (event_id, user_id, comment_text) VALUES (?, ?, ?)');
        if ($stmt) {
          $stmt->bind_param('iis', $eventId, $userId, $commentText);
          if (!$stmt->execute()) {
            $error = 'Unable to post comment right now.';
          }
          $stmt->close();
        }
        if ($error === '') {
          header('Location: ' . eventDetailRedirectUrl($eventId, 'commented'));
          exit;
        }
      } else {
        $error = 'Comment cannot be empty.';
      }
    }
  }
}

if (isset($_GET['msg'])) {
  if ($_GET['msg'] === 'liked') {
    $message = 'Liked successfully.';
  } elseif ($_GET['msg'] === 'commented') {
    $message = 'Comment posted successfully.';
  }
}

if ($dbConnected) {
  if ($eventId > 0) {
    $stmt = $conn->prepare('SELECT event_id, title, event_date, venue, description, image_path, event_link FROM events WHERE event_id = ? LIMIT 1');
    if ($stmt) {
      $stmt->bind_param('i', $eventId);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result && $row = $result->fetch_assoc()) {
        $event = $row;
        $eventFound = true;
      }
      $stmt->close();
    }
  }

  if ($isStudent && $eventId > 0 && !$studentJoinedEvent) {
    $joinedCheck = $conn->prepare('SELECT registration_id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status = "registered" LIMIT 1');
    if ($joinedCheck) {
      $joinedCheck->bind_param('ii', $eventId, $userId);
      $joinedCheck->execute();
      $joinedResult = $joinedCheck->get_result();
      $studentJoinedEvent = (bool)($joinedResult && $joinedResult->num_rows > 0);
      $joinedCheck->close();
    }
  }
}

$likeCount = 0;
$userLiked = false;
$comments = [];

if ($dbConnected && $eventFound && $eventId > 0) {
  $likeQ = $conn->prepare('SELECT COUNT(*) AS total FROM likes WHERE event_id = ?');
  if ($likeQ) {
    $likeQ->bind_param('i', $eventId);
    $likeQ->execute();
    $res = $likeQ->get_result();
    if ($res && $row = $res->fetch_assoc()) {
      $likeCount = (int)$row['total'];
    }
    $likeQ->close();
  }

  if ($isStudent) {
    $likedQ = $conn->prepare('SELECT like_id FROM likes WHERE event_id = ? AND user_id = ? LIMIT 1');
    if ($likedQ) {
      $likedQ->bind_param('ii', $eventId, $userId);
      $likedQ->execute();
      $likedRes = $likedQ->get_result();
      $userLiked = (bool)($likedRes && $likedRes->num_rows > 0);
      $likedQ->close();
    }
  }

  $commentQ = $conn->prepare('
    SELECT c.comment_text, c.created_at, u.fullname
    FROM comments c
    JOIN users u ON u.user_id = c.user_id
    WHERE c.event_id = ?
    ORDER BY c.created_at DESC
  ');
  if ($commentQ) {
    $commentQ->bind_param('i', $eventId);
    $commentQ->execute();
    $res = $commentQ->get_result();
    while ($res && $row = $res->fetch_assoc()) {
      $comments[] = $row;
    }
    $commentQ->close();
  }
}

include 'includes/header.php';
?>
<main class="container section">
  <?php if ($showJoinedEventsList && count($joinedEvents) > 0): ?>
    <section class="card page-hero reveal">
      <h1>My Joined Events</h1>
      <?php if ($message !== ''): ?><p class="badge"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
      <?php if ($error !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
      <p>You have joined <?php echo count($joinedEvents); ?> event<?php echo count($joinedEvents) === 1 ? '' : 's'; ?>.</p>
    </section>

    <?php foreach ($joinedEvents as $joinedEvent): ?>
      <?php
        $joinedEventId = (int)$joinedEvent['event_id'];
        $joinedLikeCount = (int)($joinedEvent['like_count'] ?? 0);
        $joinedUserLiked = (bool)($joinedEvent['user_liked'] ?? false);
        $joinedComments = $joinedEvent['comments'] ?? [];
      ?>
      <article class="card detail-card reveal" id="event-<?php echo $joinedEventId; ?>" style="margin-bottom: 1rem;">
        <div class="event-hero-meta">
          <span class="badge">Event ID: <?php echo htmlspecialchars((string)$joinedEventId); ?></span>
          <span class="badge">Joined</span>
        </div>
        <h1><?php echo htmlspecialchars($joinedEvent['title']); ?></h1>
        <img
          class="event-detail-image"
          src="<?php echo htmlspecialchars(eventImageUrl($joinedEvent)); ?>"
          alt="<?php echo htmlspecialchars($joinedEvent['title']); ?>"
        />
        <p>Date: <?php echo htmlspecialchars($joinedEvent['event_date']); ?> | Venue: <?php echo htmlspecialchars($joinedEvent['venue']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($joinedEvent['description'])); ?></p>

        <div class="social-actions">
          <form method="post" action="eventdetail.php" class="inline-form">
            <input type="hidden" name="event_id" value="<?php echo $joinedEventId; ?>" />
            <input type="hidden" name="action" value="like" />
            <button class="btn <?php echo $joinedUserLiked ? 'btn-ghost' : 'btn-primary'; ?>" type="submit" <?php echo $joinedUserLiked ? 'disabled' : ''; ?>>
              <?php echo $joinedUserLiked ? 'Liked' : 'Like'; ?> (<?php echo $joinedLikeCount; ?>)
            </button>
          </form>
          <?php if (!empty($joinedEvent['event_link'])): ?>
            <a class="btn btn-ghost" href="<?php echo htmlspecialchars($joinedEvent['event_link']); ?>" target="_blank" rel="noopener noreferrer">Open Event Page</a>
          <?php endif; ?>
          <a class="btn btn-secondary" href="event.php">Back to Events</a>
          <a class="btn btn-ghost" href="eventdetail.php?id=<?php echo urlencode((string)$joinedEventId); ?>">Single View</a>
        </div>

        <h3>Comments (<?php echo count($joinedComments); ?>)</h3>
        <ul class="comment-list">
          <?php if (count($joinedComments) === 0): ?>
            <li class="muted-list-item">No comments yet. Be the first to comment.</li>
          <?php else: ?>
            <?php foreach ($joinedComments as $comment): ?>
              <li>
                <strong><?php echo htmlspecialchars($comment['fullname']); ?>:</strong>
                <p><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>

        <form class="comment-form" method="post" action="eventdetail.php">
          <input type="hidden" name="event_id" value="<?php echo $joinedEventId; ?>" />
          <input type="hidden" name="action" value="comment" />
          <input type="text" name="comment_text" placeholder="Write a comment..." required />
          <button class="btn btn-primary" type="submit">Post Comment</button>
        </form>
      </article>
    <?php endforeach; ?>
  <?php elseif ($dbConnected && !$eventFound): ?>
    <section class="card empty-state reveal">
      <?php if ($isStudent && !isset($_GET['id'])): ?>
        <h1>No Joined Event Yet</h1>
        <p>You have not joined any events yet. Join an event first, then its details will appear here.</p>
      <?php else: ?>
        <h1>Event Not Found</h1>
        <p>The event you are looking for is unavailable.</p>
      <?php endif; ?>
      <a class="btn btn-primary" href="event.php">Browse Events</a>
      <?php if ($isStudent): ?>
        <a class="btn btn-secondary" href="student.php">Student Dashboard</a>
      <?php endif; ?>
    </section>
  <?php else: ?>
  <article class="card detail-card reveal">
    <?php if ($message !== ''): ?><p class="badge"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="badge badge-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <div class="event-hero-meta">
      <span class="badge">Event ID: <?php echo htmlspecialchars((string)$event['event_id']); ?></span>
      <?php if ($selectedJoinedEvent): ?><span class="badge">Your Joined Event</span><?php endif; ?>
      <?php if ($isStudent && $studentJoinedEvent && !$selectedJoinedEvent): ?><span class="badge">Joined</span><?php endif; ?>
    </div>
    <h1><?php echo htmlspecialchars($event['title']); ?></h1>
    <img
      class="event-detail-image"
      src="<?php echo htmlspecialchars(eventImageUrl($event)); ?>"
      alt="<?php echo htmlspecialchars($event['title']); ?>"
    />
    <p>Date: <?php echo htmlspecialchars($event['event_date']); ?> | Venue: <?php echo htmlspecialchars($event['venue']); ?></p>
    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>

    <div class="social-actions">
      <?php if ($isStudent): ?>
        <form method="post" action="eventdetail.php?id=<?php echo urlencode((string)$eventId); ?>" class="inline-form">
          <input type="hidden" name="action" value="like" />
          <button class="btn <?php echo $userLiked ? 'btn-ghost' : 'btn-primary'; ?>" type="submit" <?php echo $userLiked ? 'disabled' : ''; ?>>
            <?php echo $userLiked ? 'Liked' : 'Like'; ?> (<span id="likeCount"><?php echo $likeCount; ?></span>)
          </button>
        </form>
      <?php else: ?>
        <a class="btn btn-primary" href="login.php">Login as Student to Like (<?php echo $likeCount; ?>)</a>
      <?php endif; ?>
      <?php if (!empty($event['event_link'])): ?>
        <a class="btn btn-ghost" href="<?php echo htmlspecialchars($event['event_link']); ?>" target="_blank" rel="noopener noreferrer">Open Event Page</a>
      <?php endif; ?>
      <a class="btn btn-secondary" href="event.php">Back to Events</a>
      <?php if ($isStudent): ?>
        <a class="btn btn-ghost" href="eventdetail.php">My Joined Events</a>
      <?php endif; ?>
    </div>

    <h3>Comments (<?php echo count($comments); ?>)</h3>
    <ul class="comment-list">
      <?php if (count($comments) === 0): ?>
        <li class="muted-list-item">No comments yet. Be the first to comment.</li>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <li>
            <strong><?php echo htmlspecialchars($comment['fullname']); ?>:</strong>
            <p><?php echo htmlspecialchars($comment['comment_text']); ?></p>
            <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>

    <?php if ($isStudent): ?>
      <form class="comment-form" method="post" action="eventdetail.php?id=<?php echo urlencode((string)$eventId); ?>">
        <input type="hidden" name="action" value="comment" />
        <input type="text" name="comment_text" placeholder="Write a comment..." required />
        <button class="btn btn-primary" type="submit">Post Comment</button>
      </form>
    <?php else: ?>
      <p><a href="login.php">Login as Student to comment.</a></p>
    <?php endif; ?>
  </article>
  <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>
