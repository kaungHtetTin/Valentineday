<?php
/**
 * Helper Functions
 * LoveFun App
 */

/**
 * Generate a unique story key (32 chars)
 */
function generateStoryKey() {
    return bin2hex(random_bytes(16));
}

/**
 * Sanitize user input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get base URL of the application
 */
function baseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . '/love_app';
}

/**
 * Redirect to a given path
 */
function redirect($path) {
    header("Location: " . baseUrl() . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Upload an image file and return the relative URL for storage
 * Uses absolute path for save so it works regardless of CWD
 */
function uploadImage($file, $uploadDir = null) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    $relDir = 'assets/uploads/';
    if ($uploadDir === null) {
        $baseDir = dirname(__DIR__);
        $uploadDir = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relDir);
    }

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'No file uploaded.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File too large. Max 5MB.',
            UPLOAD_ERR_PARTIAL    => 'Upload was interrupted. Try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server upload error. Try again later.',
            UPLOAD_ERR_CANT_WRITE => 'Server could not save file. Try again.',
            UPLOAD_ERR_EXTENSION  => 'Upload not allowed by server.',
        ];
        $msg = $messages[$file['error']] ?? 'Upload error.';
        return ['success' => false, 'message' => $msg];
    }

    if (!empty($file['type']) && !in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Use JPG, PNG, GIF, or WEBP.'];
    }

    if (!empty($file['size']) && $file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Max 5MB.'];
    }

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        return ['success' => false, 'message' => 'Upload folder not writable.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $ext = 'jpg';
    }
    $filename = uniqid('pay_') . '.' . $ext;
    $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'url' => $relDir . $filename];
    }

    return ['success' => false, 'message' => 'Failed to save file.'];
}

/**
 * Upload an audio file and return the relative path
 */
function uploadAudio($file, $uploadDir = 'assets/uploads/') {
    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/webm', 'audio/mp4', 'audio/x-m4a', 'audio/aac'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error.'];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Use MP3, WAV, OGG, or M4A.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Max 10MB.'];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!$ext) $ext = 'mp3';
    $filename = uniqid('audio_') . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'url' => $destination];
    }

    return ['success' => false, 'message' => 'Failed to save file.'];
}

/**
 * Get a story by its story_key
 */
function getStoryByKey($pdo, $key) {
    $stmt = $pdo->prepare("SELECT * FROM stories WHERE story_key = :key LIMIT 1");
    $stmt->execute(['key' => $key]);
    return $stmt->fetch();
}

/**
 * Decode story JSON safely
 */
function decodeStory($jsonString) {
    $data = json_decode($jsonString, true);
    return $data ?: ['couple' => [], 'blocks' => []];
}

/**
 * Check if story is paid (premium)
 */
function isPaid($story) {
    return isset($story['is_paid']) && $story['is_paid'] == 1;
}

/**
 * Generate a unique token for unlock request (status page link)
 */
function generateUnlockToken() {
    return bin2hex(random_bytes(24));
}

/**
 * Get unlock request by token
 */
function getUnlockRequestByToken($pdo, $token) {
    $stmt = $pdo->prepare("SELECT * FROM unlock_requests WHERE token = :token LIMIT 1");
    $stmt->execute(['token' => $token]);
    return $stmt->fetch();
}

/**
 * Check if view access is allowed: valid key + token with approved status
 */
function isViewAccessAllowed($pdo, $key, $token) {
    if (empty($key) || empty($token)) return false;
    $stmt = $pdo->prepare("SELECT id FROM unlock_requests WHERE story_key = :key AND token = :token AND status = 'approved' LIMIT 1");
    $stmt->execute(['key' => $key, 'token' => $token]);
    return $stmt->fetch() !== false;
}

/**
 * Get an approved token for a story (so we can redirect to view when already paid)
 */
function getApprovedTokenForStory($pdo, $storyKey) {
    $stmt = $pdo->prepare("SELECT token FROM unlock_requests WHERE story_key = :key AND status = 'approved' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['key' => $storyKey]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['token'] : null;
}

/**
 * Get all pending unlock requests (for admin)
 */
function getPendingUnlockRequests($pdo) {
    $stmt = $pdo->query("SELECT * FROM unlock_requests WHERE status = 'pending' ORDER BY created_at ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update unlock request status and optionally mark story as paid
 */
function setUnlockRequestStatus($pdo, $id, $status) {
    $req = $pdo->prepare("SELECT story_key FROM unlock_requests WHERE id = :id");
    $req->execute(['id' => $id]);
    $row = $req->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("UPDATE unlock_requests SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);
    if ($status === 'approved' && $row) {
        setStoryPaid($pdo, $row['story_key']);
    }
}

/**
 * Mark story as paid by story_key
 */
function setStoryPaid($pdo, $storyKey) {
    $stmt = $pdo->prepare("UPDATE stories SET is_paid = 1 WHERE story_key = :key");
    $stmt->execute(['key' => $storyKey]);
}
