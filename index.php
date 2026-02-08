<?php
/**
 * Landing Page
 * LoveFun – Psychological UX: emotion, trust, urgency
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

// Count stories for social proof
$storyCount = $pdo->query("SELECT COUNT(*) FROM stories")->fetchColumn();
$displayCount = max($storyCount, 1200); // minimum social proof number

// Valentine's Day countdown
$valentine = new DateTime('2026-02-14');
$now = new DateTime();
$diff = $now->diff($valentine);
$daysLeft = $valentine > $now ? $diff->days : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveFun – Make Them Smile, Make Them Yours 💖</title>
    <meta name="description" content="Create a funny, romantic & interactive love story with photos and a playful Yes/No game. Share it with someone special.">
    <meta property="og:title" content="Someone made a love story just for you 💖">
    <meta property="og:description" content="Open to see a special message filled with love and fun!">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ========== HERO ========== -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <div class="fade-in" style="animation-delay: 0.1s">
                    <p class="hero-tagline">because love should be fun</p>
                </div>

                <h1 class="hero-brand fade-in" style="animation-delay: 0.3s">
                    Love<span class="text-pink">Fun</span> 💖
                </h1>

                <p class="hero-description fade-in" style="animation-delay: 0.5s">
                    Create a playful love story with photos, sweet notes, and a hilarious
                    <strong>Yes / No game</strong> — then send it to your person and watch them smile.
                </p>

                <div class="fade-in" style="animation-delay: 0.7s">
                    <a href="create.php" class="btn btn-pink hero-cta">
                        <i class="bi bi-heart-fill me-2"></i>Create Your Story
                    </a>
                </div>
                <br>
                <div class="hero-features fade-in" style="animation-delay: 0.9s">
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <span class="hero-feature-item"><i class="bi bi-camera-fill"></i> Photos</span>
                        <span class="hero-feature-item"><i class="bi bi-chat-heart-fill"></i> Love Notes</span>
                        <span class="hero-feature-item"><i class="bi bi-controller"></i> Yes/No Game</span>
                        <span class="hero-feature-item"><i class="bi bi-send-fill"></i> Share Link</span>
                    </div>
                </div>

                <!-- Social Proof (Psychology: bandwagon effect) -->
                <div class="fade-in" style="animation-delay: 1.1s">
                    <div class="social-proof mx-auto">
                        <span class="pulse-dot"></span>
                        <span><strong><?= number_format($displayCount) ?>+</strong> love stories created</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Hearts -->
        <div class="floating-hearts" aria-hidden="true">
            <span class="heart">💕</span>
            <span class="heart">💗</span>
            <span class="heart">💖</span>
            <span class="heart">❤️</span>
            <span class="heart">💘</span>
            <span class="heart">💝</span>
            <span class="heart">💞</span>
            <span class="heart">🩷</span>
        </div>
    </section>

    <!-- ========== VALENTINE COUNTDOWN (Psychology: urgency & scarcity) ========== -->
    <?php if ($daysLeft > 0 && $daysLeft <= 30): ?>
    <section class="countdown-section">
        <div class="container text-center countdown-container">
            <p class="countdown-subtitle mb-1 opacity-75">Valentine's Day is coming</p>
            <h2 class="countdown-title fw-bold mb-4">Don't miss the moment 💌</h2>
            <div class="countdown-row d-flex justify-content-center align-items-start flex-wrap" id="countdown">
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countDays"><?= $daysLeft ?></div>
                    <div class="countdown-label">Days</div>
                </div>
                <span class="countdown-divider">:</span>
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countHours">00</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <span class="countdown-divider">:</span>
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countMinutes">00</div>
                    <div class="countdown-label">Mins</div>
                </div>
                <span class="countdown-divider">:</span>
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countSeconds">00</div>
                    <div class="countdown-label">Secs</div>
                </div>
            </div>
            <a href="create.php" class="btn btn-light text-pink fw-bold px-4 py-2 mt-4 countdown-cta" style="border-radius:50px;">
                Create Now <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== HOW IT WORKS (Psychology: simplicity, clarity) ========== -->
    <section class="how-section">
        <div class="container text-center">
            <h2 class="section-title mb-2">How It Works</h2>
            <p class="section-subtitle mb-5">Three simple steps. Zero sign-up. Pure emotion.</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card h-100">
                        <div class="step-number">1</div>
                        <h5>Create</h5>
                        <p>Add text messages, upload your favorite photos together, and set up a playful Yes/No question.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card h-100">
                        <div class="step-number">2</div>
                        <h5>Preview</h5>
                        <p>See exactly how your partner will experience it. Every animation, every word — perfect before sending.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card h-100">
                        <div class="step-number">3</div>
                        <h5>Share</h5>
                        <p>Get a unique link. Send it via WhatsApp, Instagram, or text. Watch their reaction and smile.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== EMOTIONAL CTA (Psychology: reciprocity, emotion) ========== -->
    <section class="py-5" style="background: var(--bg-cream);">
        <div class="container text-center" style="max-width: 600px;">
            <p class="text-script mb-2">Ready to make them smile?</p>
            <h2 class="section-title mb-3">Your Story Is Waiting</h2>
            <p class="text-muted mb-4">
                It takes 2 minutes to create something they'll remember forever.
                No account needed — just your heart and a few clicks.
            </p>
            <a href="create.php" class="btn btn-pink hero-cta">
                <i class="bi bi-heart-fill me-2"></i>Start Creating
            </a>
        </div>
    </section>

    <footer class="site-footer">
        Made with ❤️ by LoveFun &middot; Spread love, not passwords — no sign-up required
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Valentine countdown timer
        (function() {
            const target = new Date('2026-02-14T00:00:00').getTime();
            function update() {
                const now = Date.now();
                const diff = target - now;
                if (diff <= 0) return;
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                const el = (id, v) => { const e = document.getElementById(id); if(e) e.textContent = String(v).padStart(2,'0'); };
                el('countDays', d);
                el('countHours', h);
                el('countMinutes', m);
                el('countSeconds', s);
            }
            update();
            setInterval(update, 1000);
        })();
    </script>
</body>
</html>
