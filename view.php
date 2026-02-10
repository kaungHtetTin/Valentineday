<?php
/**
 * Partner View Page
 * LoveFun – Cinematic story reveal + interactive game
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

$sads =[
    ['url' => 'assets/gif/sad_1.gif', 'text' => 'အဲ့လိုမပြောပါနဲ့ဟာ၊ ငါတကယ်မခံစားနိူင်ဘူး'],
    ['url' => 'assets/gif/sad_2.gif', 'text' => 'စောင်ရမ်းခံစားရတယ်ကွာ'],
    ['url' => 'assets/gif/sad_3.gif', 'text' => 'မပြောနဲ့ မပြောနဲ့ မကြားချင်ဘူး'],
    ['url' => 'assets/gif/sad_4.gif', 'text' => 'မစနဲ့ကွာ ငါငိုချင်လာပြီ'],
    ['url' => 'assets/gif/sad_5.gif', 'text' => 'ဗြဲ ....................'],
    ['url' => 'assets/gif/sad_6.gif', 'text' => 'အဲ့စကားက စောင်ရမ်းရိုင်းတယ်နော်'],
    ['url' => 'assets/gif/sad_7.gif', 'text' => 'မင်းသိပ်ချစ်တာကို လိုချင်ခဲ့တာပါကွာ'],
    ['url' => 'assets/gif/sad_8.gif', 'text' => 'ဟာ No တဲ့ကွာ သေရာ'],
    ['url' => 'assets/gif/sad_9.gif', 'text' => 'မပြောနဲ့ မပြောနဲ့ မကြားချင်ဘူး'],
    ['url' => 'assets/gif/sad_10.gif', 'text' => 'ရင်မှာခံစားရတဲ့အဖြစ်ကို မင်းကနားမလည်ဘူး'],
];
$loves = [
    ['url' => 'assets/gif/love_1.gif', 'text' => 'ဟီးဟီး အများကြီးပိုချစ်ပေးနော်'],
    ['url' => 'assets/gif/love_2.gif', 'text' => 'ယေ့ယေ့ ပျော်လိုက်တာ၊ သူကချစ်တယ်တဲ့'],
    ['url' => 'assets/gif/love_3.gif', 'text' => 'ရော့ အာဘွားအကြီးကြီးယူလိုက်ယူလိုက်'],
    ['url' => 'assets/gif/love_4.gif', 'text' => 'I love you!'],
    ['url' => 'assets/gif/love_5.gif', 'text' => 'ကဲကွာ တစ်နေကုန်အာဘွားထိုင်ပေးပစ်မယ်'],
    ['url' => 'assets/gif/love_6.gif', 'text' => 'တစ်နေကုန်ထိုင်ပြီး တစိမ့်စိမ့်ကြည့်'],
    ['url' => 'assets/gif/love_7.gif', 'text' => 'ချစ်တယ်နော် အိုင်လပ်ဖ်ယူအိုင်လပ်ဖ်ယူ'],
    ['url' => 'assets/gif/love_8.gif', 'text' => 'ပန်းစည်းကလေးနေပြီး မြန်မြန်ယူပေးတော့ဗျာ'],
    ['url' => 'assets/gif/love_9.gif', 'text' => 'ပျော်တယ်ဟေ့ ပျော်တယ်ဟေ့'],
    ['url' => 'assets/gif/love_10.gif', 'text' => 'ဟုတ်ကဲ့ ဟုတ်ကဲ့ အရာရာမင်းသဘောနော်'],
];


$key   = isset($_GET['key']) ? trim($_GET['key']) : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$story = null;

// Support view.php?token=xxx (partner link after unlock)
if (!empty($token)) {
    $request = getUnlockRequestByToken($pdo, $token);
    if (!$request) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $status = $request['status'];
    if ($status === 'approved') {
        $key   = $request['story_key'];
        $story = getStoryByKey($pdo, $key);
    } else {
        // Pending or rejected: show status page
        $brokenHtml = '<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head><body class="bg-light-pink"><div class="container py-4"><div class="text-center mb-4"><a href="index.php" class="text-decoration-none"><h2 class="fw-bold font-heading">Love<span class="text-pink">Fun</span> 💖</h2></a></div><div class="row justify-content-center"><div class="col-lg-5"><div class="glass-card p-4 text-center animate-in">';
        if ($status === 'rejected') {
            $brokenHtml .= '<div style="font-size:3rem;margin-bottom:0.5rem;">😔</div><h3 class="fw-bold font-heading mb-2">Payment not approved</h3><p class="text-muted mb-0">We couldn\'t verify your payment. Please contact us or try again.</p>';
        } else {
            $brokenHtml .= '<div style="font-size:3rem;margin-bottom:0.5rem;">⏳</div><h3 class="fw-bold font-heading mb-2">Pending</h3><p class="text-muted mb-0">Your payment is being verified. Keep this page bookmarked – once approved, your story will appear here.</p>';
        }
        $brokenHtml .= '</div></div></div></div></body></html>';
        die($brokenHtml);
    }
}

// Support view.php?key=xxx (direct story link)
if (empty($key) && !$story) {
    die('
    <html><head><meta charset="UTF-8"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head>
    <body class="bg-light-pink d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center"><p style="font-size:4rem;">💔</p><h3 class="font-heading">This link is broken</h3><p class="text-muted">Ask the person who sent you this for a new link.</p></div>
    </body></html>');
}

if (!$story) {
    $story = getStoryByKey($pdo, $key);
}
if (!$story) {
    die('
    <html><head><meta charset="UTF-8"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head>
    <body class="bg-light-pink d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center"><p style="font-size:4rem;">💔</p><h3 class="font-heading">Story not found</h3><p class="text-muted">This love story may have been removed.</p></div>
    </body></html>');
}

$storyData = decodeStory($story['story_json']);
$blocks = isset($storyData['blocks']) ? $storyData['blocks'] : [];
$couple = isset($storyData['couple']) ? $storyData['couple'] : [];
$hasCoupleData = !empty($couple['yourPhoto']) || !empty($couple['partnerPhoto']) || !empty($couple['anniversaryDate']);
$theme = isset($storyData['theme']) ? $storyData['theme'] : 'default';
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

    <!-- Confetti Canvas -->
    <canvas id="confettiCanvas"></canvas>

    <!-- Intro Splash (Psychology: anticipation builds emotion) -->
    <div id="introSplash" class="d-flex align-items-center justify-content-center" style="position:fixed;inset:0;z-index:9000;background:linear-gradient(135deg,#fff0f5,#fce4ec,#f8bbd0);transition:opacity 0.6s, visibility 0.6s;">
        <div class="text-center">
            <p class="text-script fade-in" style="font-size:2.5rem; animation-delay:0.3s;">Someone special</p>
            <h2 class="font-heading fw-bold fade-in" style="font-size:2rem; color:var(--text-dark); animation-delay:0.8s;">made this for you 💖</h2>
            <button id="openStoryBtn" class="btn btn-pink hero-cta mt-4 fade-in" style="animation-delay:1.3s;">
                <i class="bi bi-heart-fill me-2"></i>Open Your Story
            </button>
        </div>
    </div>

    <!-- Story Content -->
    <div class="container py-4" id="storyContent" style="display:none;">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="text-center mb-4 animate-in">
                    <p class="text-script" style="font-size:1.8rem;">a story for you</p>
                </div>

                <div class="story-view theme-<?= sanitize($theme) ?>" id="storyView">
                    <?php if ($hasCoupleData): ?>
                        <div class="story-block couple-block glass-card p-4 mb-3 text-center animate-in" style="animation-delay: 0s">
                            <div class="couple-photos-row">
                                <?php if (!empty($couple['yourPhoto'])): ?>
                                    <div class="couple-avatar"><img src="<?= sanitize($couple['yourPhoto']) ?>" alt="You"></div>
                                <?php else: ?>
                                    <div class="couple-avatar couple-avatar-empty"><i class="bi bi-person-fill"></i></div>
                                <?php endif; ?>
                                <div class="couple-heart-divider"><i class="bi bi-heart-fill"></i></div>
                                <?php if (!empty($couple['partnerPhoto'])): ?>
                                    <div class="couple-avatar"><img src="<?= sanitize($couple['partnerPhoto']) ?>" alt="Partner"></div>
                                <?php else: ?>
                                    <div class="couple-avatar couple-avatar-empty"><i class="bi bi-person-heart"></i></div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($couple['anniversaryDate'])): ?>
                                <div class="couple-counter mt-3" data-date="<?= sanitize($couple['anniversaryDate']) ?>">
                                    <p class="couple-counter-label mb-1">Together for</p>
                                    <p class="couple-counter-value mb-0"><span class="counter-days">--</span> days</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($blocks as $i => $block):
                        $delay = $hasCoupleData ? ($i + 1) * 0.3 : $i * 0.3;
                    ?>

                        <?php if ($block['type'] === 'text'): ?>
                            <div class="story-block text-block glass-card p-4 mb-3 text-center animate-in" style="animation-delay: <?= $i * 0.3 ?>s">
                                <p class="mb-0"><?= nl2br(sanitize($block['value'])) ?></p>
                            </div>

                        <?php elseif ($block['type'] === 'photo'): ?>
                            <div class="story-block photo-block glass-card overflow-hidden mb-3 animate-in" style="animation-delay: <?= $i * 0.3 ?>s">
                                <img src="<?= sanitize($block['url']) ?>" alt="A memory of us">
                            </div>

                        <?php elseif ($block['type'] === 'audio'): ?>
                            <div class="story-block audio-block glass-card p-4 mb-3 animate-in" style="animation-delay: <?= $i * 0.3 ?>s">

                                <!-- Hidden real audio element -->
                                <audio class="lf-audio-src" preload="metadata">
                                    <source src="<?= sanitize($block['url']) ?>">
                                </audio>

                                <!-- Custom Player UI -->
                                <div class="lf-player">
                                    <!-- Top: disc + info -->
                                    <div class="lf-player-top">
                                        <div class="lf-disc-wrap">
                                            <div class="lf-disc">
                                                <div class="lf-disc-inner"></div>
                                            </div>
                                        </div>
                                        <div class="lf-player-info">
                                            <?php if (!empty($block['caption'])): ?>
                                                <p class="lf-track-title"><?= sanitize($block['caption']) ?></p>
                                            <?php else: ?>
                                                <p class="lf-track-title">A song for you</p>
                                            <?php endif; ?>
                                            <div class="lf-equalizer">
                                                <span class="lf-eq-bar"></span>
                                                <span class="lf-eq-bar"></span>
                                                <span class="lf-eq-bar"></span>
                                                <span class="lf-eq-bar"></span>
                                                <span class="lf-eq-bar"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Seek bar -->
                                    <div class="lf-seek-wrap">
                                        <div class="lf-seek-bar">
                                            <div class="lf-seek-fill"></div>
                                            <div class="lf-seek-thumb"></div>
                                        </div>
                                        <div class="lf-time-row">
                                            <span class="lf-time-current">0:00</span>
                                            <span class="lf-time-total">0:00</span>
                                        </div>
                                    </div>

                                    <!-- Controls -->
                                    <div class="lf-controls">
                                        <button class="lf-btn-play" aria-label="Play">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>

                        <?php elseif ($block['type'] === 'game'): ?>
                            <div class="story-block game-block glass-card p-4 mb-3 text-center animate-in"
                                 data-success-message="<?= sanitize($block['successMessage'] ?? 'I love you! 💘') ?>"
                                 style="animation-delay: <?= $delay ?>s">

                                <p class="game-question mb-3">Will you be my Valentine? 💘</p>

                                <div class="game-arena">
                                    <button class="game-yes"><span class="game-btn-sticker"><i class="bi bi-heart-fill"></i></span><span class="game-btn-label"><?= sanitize($block['yesText'] ?? 'Yes') ?></span></button>
                                    <button class="game-no"><span class="game-btn-sticker"><i class="bi bi-emoji-frown"></i></span><span class="game-btn-label"><?= sanitize($block['noText'] ?? 'No') ?></span></button>
                                </div>

                                <div class="game-success-message d-none mt-3">
                                    <p class="success-text"></p>
                                </div>
                            </div>

                            <!-- Pre-loaded GIF blocks (hidden, shown randomly on button click) -->
                            <div class="story-block gif-block-container" style="display: none;">
                                <?php 
                                $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
                                // Pre-load all LOVE GIFs
                                foreach ($loves as $idx => $love): 
                                    $gifSrc = $love['url'];
                                    if ($baseUrl && !preg_match('/^https?:\/\//', $gifSrc)) {
                                        $gifSrc = $baseUrl . '/' . ltrim($gifSrc, '/');
                                    }
                                ?>
                                    <div class="gif-block glass-card p-4 mb-3 text-center gif-block-love gif-item" data-type="love" data-index="<?= $idx ?>">
                                        <div class="gif-block-inner gif-block-love">
                                            <img class="gif-block-img" src="<?= htmlspecialchars($gifSrc) ?>" alt="" loading="eager">
                                            <p class="gif-block-text"><?= htmlspecialchars($love['text']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php 
                                // Pre-load all SAD GIFs
                                foreach ($sads as $idx => $sad): 
                                    $gifSrc = $sad['url'];
                                    if ($baseUrl && !preg_match('/^https?:\/\//', $gifSrc)) {
                                        $gifSrc = $baseUrl . '/' . ltrim($gifSrc, '/');
                                    }
                                ?>
                                    <div class="gif-block glass-card p-4 mb-3 text-center gif-block-sad gif-item" data-type="sad" data-index="<?= $idx ?>">
                                        <div class="gif-block-inner gif-block-sad">
                                            <img class="gif-block-img" src="<?= htmlspecialchars($gifSrc) ?>" alt="" loading="eager">
                                            <p class="gif-block-text"><?= htmlspecialchars($sad['text']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

           
                        <?php endif; ?>

                    <?php endforeach; ?>

                    <?php if (!isPaid($story)): ?>
                        <div class="watermark text-center py-3 animate-in" style="animation-delay: <?= count($blocks) * 0.3 ?>s">
                            Made with <a href="index.php" class="text-pink">LoveFun</a> 💖
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Debug: Check if GIFs are pre-loaded
        $(document).ready(function() {
            console.log('=== PAGE LOADED - CHECKING GIF CONTAINERS ===');
            var $containers = $('.gif-block-container');
            console.log('Total GIF containers found:', $containers.length);
            $containers.each(function(idx) {
                var $container = $(this);
                var $loveGifs = $container.find('.gif-item[data-type="love"]');
                var $sadGifs = $container.find('.gif-item[data-type="sad"]');
                console.log('Container', idx + ':', 'Love GIFs:', $loveGifs.length, 'Sad GIFs:', $sadGifs.length);
                if ($loveGifs.length > 0) {
                    console.log('First love GIF src:', $loveGifs.first().find('img').attr('src'));
                }
                if ($sadGifs.length > 0) {
                    console.log('First sad GIF src:', $sadGifs.first().find('img').attr('src'));
                }
            });
            console.log('Total game blocks:', $('.game-block').length);
        });
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
