<?php
require_once __DIR__ . '/db_support.php';

function eventColumnExists(mysqli $conn, string $columnName): bool
{
    return dbTableExists($conn, 'events') && dbColumnExists($conn, 'events', $columnName);
}

function ensureEventOptionalColumns(mysqli $conn): void
{
    if (!dbTableExists($conn, 'events')) {
        return;
    }

    if (!eventColumnExists($conn, 'category')) {
        $conn->query("ALTER TABLE events ADD COLUMN category VARCHAR(30) NULL AFTER title");
    }

    if (!eventColumnExists($conn, 'image_path')) {
        $conn->query("ALTER TABLE events ADD COLUMN image_path VARCHAR(255) NULL AFTER description");
    }

    if (!eventColumnExists($conn, 'event_link')) {
        $conn->query("ALTER TABLE events ADD COLUMN event_link VARCHAR(255) NULL AFTER image_path");
    }

    if (!eventColumnExists($conn, 'event_status')) {
        $conn->query("ALTER TABLE events ADD COLUMN event_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER event_link");
    }

    if (!eventColumnExists($conn, 'created_by')) {
        $conn->query("ALTER TABLE events ADD COLUMN created_by INT NULL AFTER event_link");
    }
}

function cleanEventTitle(?string $title): string
{
    return trim(preg_replace('/^(?:\s*\[(?:Approved|Rejected)\]\s*)+/i', '', (string)$title));
}

function legacyEventStatusFromTitle(?string $title): ?string
{
    if (preg_match('/^\s*\[(Approved|Rejected)\]/i', (string)$title, $matches)) {
        return strtolower($matches[1]);
    }

    return null;
}

function normalizeEventStatus(?string $status, ?string $title = null): string
{
    $clean = strtolower(trim((string)$status));
    if ($clean === '' || $clean === 'pending') {
        $legacyStatus = legacyEventStatusFromTitle($title);
        if ($legacyStatus !== null) {
            return $legacyStatus;
        }
    }

    return in_array($clean, ['approved', 'rejected', 'pending'], true) ? $clean : 'pending';
}

function ensureEventRegistrationTable(mysqli $conn): void
{
    $conn->query('CREATE TABLE IF NOT EXISTS event_registrations (
        registration_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT "registered",
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_registration (event_id, user_id),
        KEY idx_event_registrations_user (user_id),
        KEY idx_event_registrations_event (event_id)
    )');
}

function ensureEventLikesTable(mysqli $conn): void
{
    $conn->query('CREATE TABLE IF NOT EXISTS likes (
        like_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (event_id, user_id),
        KEY idx_likes_event (event_id),
        KEY idx_likes_user (user_id)
    )');
}

function ensureEventCommentsTable(mysqli $conn): void
{
    $conn->query('CREATE TABLE IF NOT EXISTS comments (
        comment_id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        comment_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_comments_event (event_id),
        KEY idx_comments_user (user_id)
    )');
}

function ensureEventInteractionTables(mysqli $conn): void
{
    ensureEventRegistrationTable($conn);
    ensureEventLikesTable($conn);
    ensureEventCommentsTable($conn);
}

function normalizeEventCategory(string $category): string
{
    $allowed = ['competition', 'career', 'workshop', 'other'];
    $clean = strtolower(trim($category));
    return in_array($clean, $allowed, true) ? $clean : 'other';
}

function eventImageUrl(array $event): string
{
    $path = trim((string)($event['image_path'] ?? ''));
    if ($path !== '') {
        $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (is_file($absolutePath)) {
            return $path;
        }
    }

    return 'https://via.placeholder.com/900x500?text=Campus+Event';
}

function uploadEventImage(array $file): array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Image upload failed. Please try again.'];
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'Image must be 5MB or smaller.'];
    }

    $tmpPath = $file['tmp_name'] ?? '';
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        return ['ok' => false, 'error' => 'Only valid image files are allowed.'];
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $originalName = (string)($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['ok' => false, 'error' => 'Allowed image types: JPG, PNG, GIF, WEBP.'];
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'events';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return ['ok' => false, 'error' => 'Unable to create upload folder.'];
    }

    $fileName = 'event_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($tmpPath, $destination)) {
        return ['ok' => false, 'error' => 'Unable to save uploaded image.'];
    }

    return ['ok' => true, 'path' => 'uploads/events/' . $fileName];
}

function sanitizeEventLink(string $rawUrl): array
{
    $url = trim($rawUrl);
    if ($url === '') {
        return ['ok' => true, 'url' => null];
    }

    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['ok' => false, 'error' => 'Please provide a valid page URL.'];
    }

    return ['ok' => true, 'url' => $url];
}
