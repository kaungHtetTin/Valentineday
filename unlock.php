<?php
/**
 * Unlock Page
 * LoveFun – Partner uploads payment screenshot to unlock (admin approves).
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

    // When post_max_size exceeded, PHP may empty $_POST and $_FILES
    $fileSent = isset($_FILES['payment_screenshot']) && is_array($_FILES['payment_screenshot']) && (
        (isset($_FILES['payment_screenshot']['error']) && $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_NO_FILE)
        || !empty($_FILES['payment_screenshot']['tmp_name'])
    );
    if (empty($_POST) && empty($fileSent) && isset($_GET['key'])) {
        $error = 'ဓာတ်ပုံ သို့မဟုတ် ဒေတာ ကြီးလွန်းပါတယ်။ ၁၀ MB အောက် ဓာတ်ပုံ သုံးပြီး ထပ်ကြိုးစားပါ။';
        $key = trim($_GET['key']);
    } elseif (empty($key)) {
        $error = 'လင့်ခ် မမှန်ပါ။ သင့်ကို ပို့ထားတဲ့ လင့်ခ်ကို သုံးပါ။';
    } elseif (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'ငွေပေးချေထားတဲ့ ဓာတ်ပုံ စခရင်ရော့ တင်ပါ။';
    } else {
        $story = getStoryByKey($pdo, $key);
        if (!$story) {
            $error = 'ဒီ ဇာတ်လမ်း လင့်ခ်က မမှန်ပါ သို့မဟုတ် ဖျက်ပြီးသား ဖြစ်နိုင်ပါတယ်။';
        } else {
            $upload = uploadImage($_FILES['payment_screenshot']);
            if (!$upload['success']) {
                $error = $upload['message'] ?? 'ငွေပေးချေ ဓာတ်ပုံ တင်မရပါ။';
            } else {
                try {
                    $token = generateUnlockToken();
                    $stmt = $pdo->prepare("
                        INSERT INTO unlock_requests (story_key, email, name, phone, screenshot_url, status, token)
                        VALUES (:story_key, :email, :name, :phone, :screenshot_url, 'pending', :token)
                    ");
                    $stmt->execute([
                        'story_key'      => $key,
                        'email'          => '',
                        'name'           => '',
                        'phone'          => '',
                        'screenshot_url' => $upload['url'],
                        'token'          => $token
                    ]);
                    $statusUrl = BASE_URL . '/view.php?token=' . $token;
                    $success = true;
                } catch (Exception $e) {
                    $error = 'အမှားတစ်ခု ဖြစ်သွားပါတယ်။ ထပ်ကြိုးစားပါ။';
                }
            }
        }
    }
}

// GET or POST with error: need to show form (and validate key for GET)
if (!$success) {
    if (empty($key)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $story = getStoryByKey($pdo, $key);
    if (!$story) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    // If story is already paid, redirect to view page (same one link = story)
    if (isPaid($story)) {
        $token = getApprovedTokenForStory($pdo, $key);
        if ($token) {
            header('Location: ' . BASE_URL . '/view.php?token=' . urlencode($token));
            exit;
        }
    }
    $storyData = decodeStory($story['story_json']);
    $blocks = isset($storyData['blocks']) ? $storyData['blocks'] : [];
    // Teaser: first text block truncated, or generic message
    $teaserText = 'သင့်အတွက် အထူး တစ်ခုခု ဖန်တီးထားပါတယ် 💖';
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
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>သင့်ဇာတ်လမ်း ဖွင့်ပါ – LoveFun 💖</title>
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
                        <h3 class="fw-bold font-heading mb-2">ငွေလက်ခံပြီး</h3>
                        <p class="text-muted mb-3">သင့်ငွေပေးချေမှုကို စစ်ဆေးနေပါတယ်။ အတည်ပြုပြီးရင် ဒီလင့်ခ်မှာပဲ ဇာတ်လမ်း ပေါ်မယ် – ဒုတိယ လင့်ခ် မလိုပါ (ပုံမှန် ၂၄ နာရီအတွင်း)။</p>
                        <p class="small text-muted mb-2">ဒီလင့်ခ်ကို သိမ်းထားပါ – အခြေအနေနဲ့ ဇာတ်လမ်း ကြည့်ဖို့ ဒီလင့်ခ်ပဲ ရပါမယ်။</p>
                        <div class="input-group mb-3" style="border-radius: 14px; overflow: hidden;">
                            <input type="text" id="statusLink" class="form-control" value="<?= htmlspecialchars($statusUrl) ?>" readonly style="border-radius: 14px 0 0 14px;">
                            <button class="btn btn-pink px-4" type="button" id="copyStatusLink" style="border-radius: 0 14px 14px 0;">
                                <i class="bi bi-clipboard me-1"></i>ကူးပါ
                            </button>
                        </div>
                        <a href="<?= htmlspecialchars($statusUrl) ?>" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-box-arrow-up-right me-2"></i>အခြေအနေ ယခု ကြည့်ပါ
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Preview story teaser -->
                    <div class="glass-card p-4 mb-4 text-center animate-in">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔐</div>
                        <h3 class="fw-bold font-heading mb-2">သင့်ဇာတ်လမ်း ဖွင့်ပါ</h3>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($teaserText)) ?></p>
                    </div>

                    <!-- Payment method -->
                    <div class="glass-card p-4 mb-4 animate-in">
                        <h5 class="fw-bold font-heading mb-3"><i class="bi bi-bank me-2"></i>ငွေပေးချေမှု အချက်အလက်</h5>
                        <p class="mb-1"><strong>ဘဏ်/ငွေပေးစနစ်</strong> — KBZpay, WavePay</p>
                        <p class="mb-1"><strong>အကောင့်နံပါတ်</strong> — 09688683805</p>
                        <p class="mb-0"><strong>အကောင့်အမည်</strong> — Min Htet Kyaw</p>
                        <p class="small text-muted mt-2 mb-0">အပေါ်က အချက်တွေနဲ့ ငွေပေးပြီး အောက်က ငွေပေးချေ ဓာတ်ပုံ စခရင်ရော့ တင်ပါ။</p>
                    </div>

                    <!-- Unlock form -->
                    <div class="glass-card p-4 animate-in">
                        <h5 class="fw-bold font-heading mb-3">ငွေပေးချေ ဓာတ်ပုံ စခရင်ရော့ တင်ပါ</h5>
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post" action="unlock.php?key=<?= htmlspecialchars(urlencode($key)) ?>" enctype="multipart/form-data" id="unlockForm">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">ငွေပေးချေ ဓာတ်ပုံ စခရင်ရော့</label>
                                <input type="file" name="payment_screenshot" id="paymentScreenshot" class="form-control form-control-lg" accept="image/*" required style="border-radius: 14px;">
                                <small class="text-muted d-block mt-1">ဓာတ်ပုံ ပုံစံ မည်သည်မဆို (JPG, PNG, GIF, WEBP, BMP, TIFF, SVG, HEIC စသည်)။ အများဆုံး ၁၀ MB။</small>
                                <div id="screenshotPreview" class="mt-3 text-center d-none">
                                    <p class="small text-muted mb-2">ကြိုကြည့်ခြင်း</p>
                                    <img id="screenshotPreviewImg" src="" alt="ကြိုကြည့်ခြင်း" class="rounded border" style="max-width:100%; max-height:220px; object-fit:contain; background:#f8f9fa;">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-pink btn-lg w-100 py-3">
                                <i class="bi bi-unlock-fill me-2"></i>ဖွင့်ပြီး ဇာတ်လမ်း ကြည့်ပါ
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
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>ကူးပြီး!';
                setTimeout(function() { btn.innerHTML = orig; }, 2000);
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
