<?php
/**
 * View Page – One link for the customer: ?token=xxx (status + story when approved).
 * Also supports ?key=xxx&token=xxx for backwards compatibility.
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

$key = isset($_GET['key']) ? trim($_GET['key']) : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// --- Entry by token only (main customer link: view.php?token=xxx) ---
if ($token && empty($key)) {
    $request = getUnlockRequestByToken($pdo, $token);
    if (!$request) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $status = $request['status'];

    if ($status === 'approved') {
        $story = getStoryByKey($pdo, $request['story_key']);
        if (!$story) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        $storyData = decodeStory($story['story_json']);
        $blocks = $storyData['blocks'];
        $couple = isset($storyData['couple']) ? $storyData['couple'] : [];
        $hasCoupleData = !empty($couple['yourPhoto']) || !empty($couple['partnerPhoto']) || !empty($couple['anniversaryDate']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Someone Sent You Something Special 💖</title>
    <meta property="og:title" content="Someone sent you a love story 💖">
    <meta property="og:description" content="Open this link to see a special message made just for you!">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light-pink">
<?php include __DIR__ . '/inc/partner_story_view.php'; ?>
</body>
</html>
<?php
        exit;
    }

    // Pending or Rejected
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Story – LoveFun 💖</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light-pink">
    <div class="container py-4">
        <div class="text-center mb-4">
            <a href="index.php" class="text-decoration-none">
                <h2 class="fw-bold font-heading">Love<span class="text-pink">Fun</span> 💖</h2>
            </a>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="glass-card p-4 text-center animate-in">
                    <?php if ($status === 'rejected'): ?>
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">😔</div>
                        <h3 class="fw-bold font-heading mb-2">Payment not approved</h3>
                        <p class="text-muted mb-0">We couldn’t verify your payment. Please contact us with your payment details or try again.</p>
                    <?php else: ?>
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">⏳</div>
                        <h3 class="fw-bold font-heading mb-2">Pending</h3>
                        <p class="text-muted mb-0">Your payment is being verified. Keep this page bookmarked – once approved, your story will appear here (no new link needed).</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit;
}

// --- Entry by key (and optional token for key+token access) ---
if (empty($key)) {
    die('
    <html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head>
    <body class="bg-light-pink d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center"><p style="font-size:4rem;">💔</p><h3 class="font-heading">This link is broken</h3><p class="text-muted">Ask the person who sent you this for a new link.</p></div>
    </body></html>');
}

$story = getStoryByKey($pdo, $key);
if (!$story) {
    die('
    <html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head>
    <body class="bg-light-pink d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center"><p style="font-size:4rem;">💔</p><h3 class="font-heading">Story not found</h3><p class="text-muted">This love story may have been removed.</p></div>
    </body></html>');
}

if (!isViewAccessAllowed($pdo, $key, $token)) {
    $unlockUrl = BASE_URL . '/unlock.php?key=' . urlencode($key);
    die('
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"></head>
    <body class="bg-light-pink d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center px-3">
    <p style="font-size:4rem;">🔐</p>
    <h3 class="font-heading">This story is private</h3>
    <p class="text-muted mb-3">Use the unlock link sent to you to pay and get access, or open your view link after approval.</p>
    <a href="' . htmlspecialchars($unlockUrl) . '" class="btn btn-pink">Unlock this story</a>
    </div>
    </body></html>');
}

$storyData = decodeStory($story['story_json']);
$blocks = $storyData['blocks'];
$couple = isset($storyData['couple']) ? $storyData['couple'] : [];
$hasCoupleData = !empty($couple['yourPhoto']) || !empty($couple['partnerPhoto']) || !empty($couple['anniversaryDate']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Someone Sent You Something Special 💖</title>
    <meta property="og:title" content="Someone sent you a love story 💖">
    <meta property="og:description" content="Open this link to see a special message made just for you!">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light-pink">
<?php include __DIR__ . '/inc/partner_story_view.php'; ?>
</body>
</html>
