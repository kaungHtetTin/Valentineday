<?php
/**
 * Unlock Page
 * LoveFun – Partner fills form (email, name, phone, payment screenshot) then gets partner view link
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

$key = isset($_GET['key']) ? trim($_GET['key']) : '';
$error = '';
$success = false;
$statusUrl = '';  // customer checks approval here; link given only after admin approval

// POST: handle unlock form submit (payment screenshot → pending; admin approves later)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = isset($_POST['key']) ? trim($_POST['key']) : (isset($_GET['key']) ? trim($_GET['key']) : $key);
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

    // When post_max_size exceeded, PHP may empty $_POST and $_FILES
    $fileSent = isset($_FILES['payment_screenshot']) && is_array($_FILES['payment_screenshot']) && (
        (isset($_FILES['payment_screenshot']['error']) && $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_NO_FILE)
        || !empty($_FILES['payment_screenshot']['tmp_name'])
    );
    if (empty($_POST) && empty($fileSent) && isset($_GET['key'])) {
        $error = 'Upload or form data too large. Use an image under 5MB and try again.';
        $key = trim($_GET['key']);
    } elseif (empty($key)) {
        $error = 'Invalid link. Please use the link sent to you.';
    } elseif (empty($email) || empty($name) || empty($phone)) {
        $error = 'Please fill in email, name and phone.';
    } elseif (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please upload your payment screenshot.';
    } else {
        $story = getStoryByKey($pdo, $key);
        if (!$story) {
            $error = 'This story link is invalid or has been removed.';
        } else {
            $upload = uploadImage($_FILES['payment_screenshot']);
            if (!$upload['success']) {
                $error = $upload['message'] ?? 'Payment screenshot upload failed.';
            } else {
                try {
                    $token = generateUnlockToken();
                    $stmt = $pdo->prepare("
                        INSERT INTO unlock_requests (story_key, email, name, phone, screenshot_url, status, token)
                        VALUES (:story_key, :email, :name, :phone, :screenshot_url, 'pending', :token)
                    ");
                    $stmt->execute([
                        'story_key'      => $key,
                        'email'          => $email,
                        'name'           => $name,
                        'phone'          => $phone,
                        'screenshot_url' => $upload['url'],
                        'token'          => $token
                    ]);
                    $statusUrl = baseUrl() . '/view.php?token=' . $token;
                    $success = true;
                } catch (Exception $e) {
                    $error = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}

// GET or POST with error: need to show form (and validate key for GET)
if (!$success) {
    if (empty($key)) {
        header('Location: ' . baseUrl() . '/index.php');
        exit;
    }
    $story = getStoryByKey($pdo, $key);
    if (!$story) {
        header('Location: ' . baseUrl() . '/index.php');
        exit;
    }
    // If story is already paid, redirect to view page (same one link = story)
    if (isPaid($story)) {
        $token = getApprovedTokenForStory($pdo, $key);
        if ($token) {
            header('Location: ' . baseUrl() . '/view.php?token=' . urlencode($token));
            exit;
        }
    }
    $storyData = decodeStory($story['story_json']);
    $blocks = isset($storyData['blocks']) ? $storyData['blocks'] : [];
    // Teaser: first text block truncated, or generic message
    $teaserText = 'Someone made something special for you 💖';
    foreach ($blocks as $b) {
        if (isset($b['type']) && $b['type'] === 'text' && !empty($b['value'])) {
            $teaserText = mb_substr(strip_tags($b['value']), 0, 120);
            if (mb_strlen($b['value']) > 120) $teaserText .= '…';
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Your Story – LoveFun 💖</title>
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

                <?php if ($success): ?>
                    <!-- Success: payment received, pending admin approval -->
                    <div class="glass-card p-4 text-center animate-in">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">✅</div>
                        <h3 class="fw-bold font-heading mb-2">Payment received</h3>
                        <p class="text-muted mb-3">We’re verifying your payment. Once approved, your story will appear on this same link – no second link to open (usually within 24 hours).</p>
                        <p class="small text-muted mb-2">Save this link – it’s your only link for status and story:</p>
                        <div class="input-group mb-3" style="border-radius: 14px; overflow: hidden;">
                            <input type="text" id="statusLink" class="form-control" value="<?= htmlspecialchars($statusUrl) ?>" readonly style="border-radius: 14px 0 0 14px;">
                            <button class="btn btn-pink px-4" type="button" id="copyStatusLink" style="border-radius: 0 14px 14px 0;">
                                <i class="bi bi-clipboard me-1"></i>Copy
                            </button>
                        </div>
                        <a href="<?= htmlspecialchars($statusUrl) ?>" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Check status now
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Preview story teaser -->
                    <div class="glass-card p-4 mb-4 text-center animate-in">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔐</div>
                        <h3 class="fw-bold font-heading mb-2">Unlock Your Story</h3>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($teaserText)) ?></p>
                    </div>

                    <!-- Unlock form -->
                    <div class="glass-card p-4 animate-in">
                        <h5 class="fw-bold font-heading mb-3">Fill in your details to unlock</h5>
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post" action="unlock.php?key=<?= htmlspecialchars(urlencode($key)) ?>" enctype="multipart/form-data" id="unlockForm">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="your@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" style="border-radius: 14px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" name="name" class="form-control form-control-lg" placeholder="Your name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" style="border-radius: 14px;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="tel" name="phone" class="form-control form-control-lg" placeholder="Your phone" required value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" style="border-radius: 14px;">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Payment screenshot</label>
                                <input type="file" name="payment_screenshot" id="paymentScreenshot" class="form-control form-control-lg" accept="image/jpeg,image/png,image/gif,image/webp" required style="border-radius: 14px;">
                                <small class="text-muted d-block mt-1">JPG, PNG, GIF or WEBP. Max 5MB.</small>
                                <div id="screenshotPreview" class="mt-3 text-center d-none">
                                    <p class="small text-muted mb-2">Preview</p>
                                    <img id="screenshotPreviewImg" src="" alt="Preview" class="rounded border" style="max-width:100%; max-height:220px; object-fit:contain; background:#f8f9fa;">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-pink btn-lg w-100 py-3">
                                <i class="bi bi-unlock-fill me-2"></i>Unlock &amp; View Story
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!$success): ?>
    <script>
        (function() {
            var input = document.getElementById('paymentScreenshot');
            var preview = document.getElementById('screenshotPreview');
            var img = document.getElementById('screenshotPreviewImg');
            var lastUrl = null;
            if (input && preview && img) {
                input.addEventListener('change', function() {
                    if (lastUrl) URL.revokeObjectURL(lastUrl);
                    lastUrl = null;
                    if (this.files && this.files[0]) {
                        lastUrl = URL.createObjectURL(this.files[0]);
                        img.src = lastUrl;
                        preview.classList.remove('d-none');
                    } else {
                        img.src = '';
                        preview.classList.add('d-none');
                    }
                });
            }
        })();
    </script>
    <?php endif; ?>
    <?php if ($success): ?>
    <script>
        document.getElementById('copyStatusLink').addEventListener('click', function() {
            var el = document.getElementById('statusLink');
            el.select();
            el.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(el.value).then(function() {
                var btn = document.getElementById('copyStatusLink');
                var orig = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copied!';
                setTimeout(function() { btn.innerHTML = orig; }, 2000);
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
