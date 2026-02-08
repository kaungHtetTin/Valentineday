<?php
/**
 * Shared partner story view body (intro splash + story blocks).
 * Expects: $story, $blocks, $couple, $hasCoupleData
 */
?>
    <!-- Confetti Canvas -->
    <canvas id="confettiCanvas"></canvas>

    <!-- Intro Splash -->
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
                <div class="story-view" id="storyView">
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
                            <div class="story-block text-block glass-card p-4 mb-3 text-center animate-in" style="animation-delay: <?= $delay ?>s">
                                <p class="mb-0"><?= nl2br(sanitize($block['value'])) ?></p>
                            </div>
                        <?php elseif ($block['type'] === 'photo'): ?>
                            <div class="story-block photo-block glass-card overflow-hidden mb-3 animate-in" style="animation-delay: <?= $delay ?>s">
                                <img src="<?= sanitize($block['url']) ?>" alt="A memory of us">
                            </div>
                        <?php elseif ($block['type'] === 'audio'): ?>
                            <div class="story-block audio-block glass-card p-4 mb-3 animate-in" style="animation-delay: <?= $delay ?>s">
                                <audio class="lf-audio-src" preload="metadata"><source src="<?= sanitize($block['url']) ?>"></audio>
                                <div class="lf-player">
                                    <div class="lf-player-top">
                                        <div class="lf-disc-wrap"><div class="lf-disc"><div class="lf-disc-inner"></div></div></div>
                                        <div class="lf-player-info">
                                            <p class="lf-track-title"><?= !empty($block['caption']) ? sanitize($block['caption']) : 'A song for you' ?></p>
                                            <div class="lf-equalizer"><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span></div>
                                        </div>
                                    </div>
                                    <div class="lf-seek-wrap">
                                        <div class="lf-seek-bar"><div class="lf-seek-fill"></div><div class="lf-seek-thumb"></div></div>
                                        <div class="lf-time-row"><span class="lf-time-current">0:00</span><span class="lf-time-total">0:00</span></div>
                                    </div>
                                    <div class="lf-controls"><button class="lf-btn-play" aria-label="Play"><i class="bi bi-play-fill"></i></button></div>
                                </div>
                            </div>
                        <?php elseif ($block['type'] === 'game'): ?>
                            <div id="gameBlock" class="story-block game-block glass-card p-4 mb-3 text-center animate-in" data-success-message="<?= sanitize($block['successMessage'] ?? 'I love you! 💘') ?>" style="animation-delay: <?= $delay ?>s">
                                <p class="game-question mb-3">Will you be my Valentine? 💘</p>
                                <div class="game-arena">
                                    <button class="game-yes"><?= sanitize($block['yesText'] ?? 'YES ❤️') ?></button>
                                    <button class="game-no"><?= sanitize($block['noText'] ?? 'NO 😒') ?></button>
                                </div>
                                <div class="game-success-message d-none mt-3"><p class="success-text"></p></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!isPaid($story)): ?>
                        <div class="watermark text-center py-3 animate-in" style="animation-delay: <?= count($blocks) * 0.3 ?>s">Made with <a href="index.php" class="text-pink">LoveFun</a> 💖</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js"></script>
