<?php
/**
 * AJAX Save Story Endpoint
 * LoveFun - Saves story JSON + uploads photos
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

header('Content-Type: application/json');

// Handle photo upload
if (isset($_GET['action']) && $_GET['action'] === 'upload') {
    if (!isset($_FILES['photo'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
        exit;
    }

    $result = uploadImage($_FILES['photo']);
    echo json_encode($result);
    exit;
}

// Handle audio upload
if (isset($_GET['action']) && $_GET['action'] === 'upload_audio') {
    if (!isset($_FILES['audio'])) {
        echo json_encode(['success' => false, 'message' => 'No audio file uploaded.']);
        exit;
    }

    $result = uploadAudio($_FILES['audio']);
    echo json_encode($result);
    exit;
}

// Handle story save
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!$data || !isset($data['blocks'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid story data.']);
    exit;
}

// Validate: must have at least one block
if (empty($data['blocks'])) {
    echo json_encode(['success' => false, 'message' => 'Add at least one block to your story.']);
    exit;
}

try {
    $storyKey = generateStoryKey();

    $storyJson = json_encode([
        'couple' => isset($data['couple']) ? $data['couple'] : [],
        'blocks' => $data['blocks']
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $pdo->prepare("INSERT INTO stories (story_key, story_json, is_paid) VALUES (:key, :json, 0)");
    $stmt->execute([
        'key'  => $storyKey,
        'json' => $storyJson
    ]);

    echo json_encode([
        'success'   => true,
        'story_key' => $storyKey,
        'redirect'  => 'preview.php?key=' . $storyKey
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to save story.']);
}
