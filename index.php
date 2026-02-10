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
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveFun – သူတို့ကို ပြုံးအောင် လုပ်ပါ၊ သင့်ရဲ့သူပဲ ဖြစ်အောင် လုပ်ပါ 💖</title>
    <meta name="description" content="ဓာတ်ပုံတွေနဲ့ ပျော်စရာ ဟုတ်/မဟုတ် ဂိမ်း ပါဝင်တဲ့ ချစ်ဇာတ်လမ်း ဖန်တီးပြီး အထူးသူတစ်ယောက်ကို မျှဝေပါ။">
    <meta property="og:title" content="သင့်အတွက် ချစ်ဇာတ်လမ်း တစ်ပုဒ် ဖန်တီးထားပါတယ် 💖">
    <meta property="og:description" content="ချစ်ခြင်းနဲ့ ပျော်စရာ ပြည့်နေတဲ့ စာကို ကြည့်ဖို့ ဖွင့်ပါ!">
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
                    <p class="hero-tagline">ချစ်ခြင်းဟာ အပြုံးကိုဖန်တီးပေးပါတယ်တယ်</p>
                </div>

                <h1 class="hero-brand fade-in" style="animation-delay: 0.3s">
                    Love<span class="text-pink">Fun</span> 💖
                </h1>

                <p class="hero-description fade-in" style="animation-delay: 0.5s">
                    ဓာတ်ပုံတွေ၊ ချစ်စာတိုလေးတွေနဲ့ ပျော်စရာ
                    <strong>Yes / No ဂိမ်း</strong> ပါဝင်တဲ့ မိမိတို့အချစ်ဇာတ်လမ်း အမှတ်တရအကြောင်းအရာများကိုစုစည်းပြီး ချစ်သူဆီကိုပဲပို့မလား။
                    မိမိတစ်ဖက်သတ်ချစ်နေရတဲ့ ချစ်ရသူနဲ့ အမှတ်တရတွေကို Love Story လိုင်းအလေးအဖြစ်ဖန်တီးပြီး  Yes or No ဂိမ်းလေးနဲ့ ချစ်ခွင့်ပန်မလား။
                    ခုပဲ storyline creator လေးသုံးပြီး အပြုံးလေးတွေ ဖန်တီးပေးလိုက်ပါ။
                </p>

                <div class="fade-in" style="animation-delay: 0.7s">
                    <a href="create.php" class="btn btn-pink hero-cta">
                        <i class="bi bi-heart-fill me-2"></i>အခုဖန်တီးမယ်မယ်
                    </a>
                </div>
                <br>
                <div class="hero-features fade-in" style="animation-delay: 0.9s">
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <span class="hero-feature-item"><i class="bi bi-camera-fill"></i> ဓာတ်ပုံများ</span>
                        <span class="hero-feature-item"><i class="bi bi-chat-heart-fill"></i> ချစ်စာများ</span>
                        <span class="hero-feature-item"><i class="bi bi-controller"></i> ဟုတ်/မဟုတ် ဂိမ်း</span>
                        <span class="hero-feature-item"><i class="bi bi-send-fill"></i> လင့်ခ် မျှဝေခြင်း</span>
                    </div>
                </div>

                <!-- Social Proof (Psychology: bandwagon effect) -->
                <div class="fade-in" style="animation-delay: 1.1s">
                    <div class="social-proof mx-auto">
                        <span class="pulse-dot"></span>
                        <span><strong><?= number_format($displayCount) ?>+</strong> ချစ်ဇာတ်လမ်း ဖန်တီးပြီးသား</span>
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
            <p class="countdown-subtitle mb-1 opacity-75">ချစ်သူများနေ့ ရောက်လာပြီ</p>
            <h2 class="countdown-title fw-bold mb-4">ဒီအခွင့်အရေးကို အမိအရအသုံးချဖို့လိုပါမယ်နော် 💌</h2>
            <div class="countdown-row d-flex justify-content-center align-items-start flex-wrap" id="countdown">
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countDays"><?= $daysLeft ?></div>
                    <div class="countdown-label">ရက်</div>
                </div>
                <span class="countdown-divider">:</span>
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countHours">00</div>
                    <div class="countdown-label">နာရီ</div>
                </div>
                <span class="countdown-divider">:</span>
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countMinutes">00</div>
                    <div class="countdown-label">မိနစ်</div>
                </div>
                <span class="countdown-divider">:</span>
                <div class="countdown-item text-center">
                    <div class="countdown-value" id="countSeconds">00</div>
                    <div class="countdown-label">စက္ကန့်</div>
                </div>
            </div>
            <a href="create.php" class="btn btn-light text-pink fw-bold px-4 py-2 mt-4 countdown-cta" style="border-radius:50px;">
                အခု ဖန်တီးပါ <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- ========== HOW IT WORKS (Psychology: simplicity, clarity) ========== -->
    <section class="how-section">
        <div class="container text-center">
            <h2 class="section-title mb-2">အသုံးပြုပုံ</h2>
            <p class="section-subtitle mb-5">အကောင့် မလိုပါ။</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card h-100">
                        <div class="step-number">1</div>
                        <h5>ဖန်တီးမယ်မယ်</h5>
                        <p>မိမိချစ်သူအားပြောကြားလိုသော စကားများ၊ ဓာတ်ပုံများ၊ အသံ/ သီချင်းများကို ထည့်သွင်းပြီး Love Story Line တစ်ပုဒ်ဖန်တီးမယ်မယ်</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card h-100">
                        <div class="step-number">2</div>
                        <h5>ပြန်လည်ကြည့်ရှုမယ်ပြန်လည်ကြည့်ရှုမယ်</h5>
                        <p>မိမိဖန်တီးထာသောအရာကို ပြန်လည်ကြည့်ရှုစစ်ဆေးပါမယ်မယ်</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card h-100">
                        <div class="step-number">3</div>
                        <h5>ချစ်ရသူဆီမျှဝေမယ်</h5>
                        <p>ထို့နောက် လင့်ခ်တစ်ခုရပါက မိမိချစ်ရသူထံကို Email, Messenger, WhatsApp သို့မဟုတ် Instagram နဲ့ မျှဝေပါမယ်</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== EMOTIONAL CTA (Psychology: reciprocity, emotion) ========== -->
    <section class="py-5" style="background: var(--bg-cream);">
        <div class="container text-center" style="max-width: 600px;">
            <p class="text-script mb-2">ချစ်ရသူအတွက်အပြုံးတွေဖန်တွေဖန်းပေးဖို့အသင့်ပဲလား</p>
            <h2 class="section-title mb-3">သင့်ဇာတ်လမ်း စောင့်နေပါပြီ</h2>
            <p class="text-muted mb-4">
                သူတို့ တစ်သက်လုံး မှတ်မိမယ့် အရာတစ်ခု ဖန်တီးဖို့ မိနစ် ၂ မိနစ်ပဲ ကြာပါတယ်။
                အကောင့် မလိုပါ — နှလုံးသားလေးနဲ့ ခလုတ် နှိပ်လိုက်ရုံပါပဲ။
            </p>
            <a href="create.php" class="btn btn-pink hero-cta">
                <i class="bi bi-heart-fill me-2"></i>စတင် ဖန်တီးပါ
            </a>
        </div>
    </section>

    <footer class="site-footer">
        LoveFun နဲ့ ❤️ လုပ်ထားပါတယ် &middot; ချစ်ခြင်းမေတ္တာပဲ မျှဝေပါ၊ အကောင့် မလိုပါဘူး
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
