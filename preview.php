<?php
/**
 * Preview Page
 * LoveFun – Emotional payoff + share moment
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

$key = isset($_GET['key']) ? $_GET['key'] : '';
if (empty($key)) redirect('create.php');

$story = getStoryByKey($pdo, $key);
if (!$story) redirect('create.php');

$storyData = decodeStory($story['story_json']);
$blocks = $storyData['blocks'];
$couple = isset($storyData['couple']) ? $storyData['couple'] : [];
$hasCoupleData = !empty($couple['yourPhoto']) || !empty($couple['partnerPhoto']) || !empty($couple['anniversaryDate']);
$shareUrl = baseUrl() . '/unlock.php?key=' . $story['story_key'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Story Is Ready! – LoveFun 💖</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light-pink">

    <div class="container py-4">

        <!-- Header -->
        <div class="text-center mb-2">
            <a href="index.php" class="text-decoration-none">
                <h2 class="fw-bold font-heading">Love<span class="text-pink">Fun</span> 💖</h2>
            </a>
        </div>

        <!-- Progress at Step 3 -->
        <div class="progress-steps mb-4">
            <div class="progress-line">
                <div class="progress-line-fill" style="width: 100%;"></div>
            </div>
            <div class="progress-step completed" data-step="1">
                <div class="step-dot"><i class="bi bi-check-lg"></i></div>
                <span class="step-label">Your Story</span>
            </div>
            <div class="progress-step completed" data-step="2">
                <div class="step-dot"><i class="bi bi-check-lg"></i></div>
                <span class="step-label">Blocks</span>
            </div>
            <div class="progress-step active" data-step="3">
                <div class="step-dot">3</div>
                <span class="step-label">Share</span>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">

                <!-- Success Banner -->
                <div class="glass-card p-4 mb-4 text-center animate-in" style="animation-delay: 0.1s;">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎉</div>
                    <h3 class="fw-bold font-heading mb-1">Your Story Is Ready!</h3>
                    <p class="text-muted mb-0">Here's how it will look to your special someone</p>
                </div>

                <!-- Story Preview -->
                <div class="story-view stagger-in">

                    <?php if ($hasCoupleData): ?>
                        <div class="story-block couple-block glass-card p-4 mb-3 text-center animate-in" style="animation-delay: 0.15s">
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

                    <?php foreach ($blocks as $i => $block): ?>
                        <?php if ($block['type'] === 'text'): ?>
                            <div class="story-block text-block glass-card p-4 mb-3 text-center animate-in" style="animation-delay: <?= 0.2 + $i * 0.15 ?>s;">
                                <p class="mb-0"><?= nl2br(sanitize($block['value'])) ?></p>
                            </div>

                        <?php elseif ($block['type'] === 'photo'): ?>
                            <div class="story-block photo-block glass-card overflow-hidden mb-3 animate-in" style="animation-delay: <?= 0.2 + $i * 0.15 ?>s;">
                                <img src="<?= sanitize($block['url']) ?>" alt="Love photo">
                            </div>

                        <?php elseif ($block['type'] === 'audio'): ?>
                            <div class="story-block audio-block glass-card p-4 mb-3 animate-in" style="animation-delay: <?= 0.2 + $i * 0.15 ?>s;">

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
                            <div class="story-block game-block glass-card p-4 mb-3 text-center animate-in" style="animation-delay: <?= 0.2 + $i * 0.15 ?>s;">
                                <p class="game-question mb-3">Will you be my Valentine? 💘</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <button class="btn btn-pink btn-lg" disabled style="animation:none; box-shadow: 0 4px 15px rgba(233,30,99,0.3);">
                                        <?= sanitize($block['yesText'] ?? 'YES ❤️') ?>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-lg" disabled>
                                        <?= sanitize($block['noText'] ?? 'NO 😒') ?>
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-info-circle me-1"></i>"No" button will <strong><?= sanitize($block['noBehavior'] ?? 'run') ?></strong> away
                                </small>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!isPaid($story)): ?>
                        <div class="watermark text-center py-2">
                            Made with <a href="index.php" class="text-pink">LoveFun</a> 💖
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                    <a href="<?= htmlspecialchars($shareUrl) ?>" class="btn btn-pink btn-lg px-4 py-3" style="border-radius:50px;" target="_blank">
                        <i class="bi bi-unlock-fill me-2"></i>Unlock page
                    </a>
                    <a href="create.php" class="btn btn-outline-secondary btn-lg px-4 py-3" style="border-radius:50px;">
                        <i class="bi bi-plus-circle me-2"></i>Create new story
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
