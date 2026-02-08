<?php
/**
 * Story Builder Page
 * LoveFun – Block-based story creator with guided UX
 */
require_once 'inc/db.php';
require_once 'inc/functions.php';

$editKey = isset($_GET['key']) ? trim($_GET['key']) : '';
$editStoryData = null;
if ($editKey) {
    $story = getStoryByKey($pdo, $editKey);
    if ($story) {
        $editStoryData = decodeStory($story['story_json']);
        $editStoryData['story_key'] = $editKey;
    } else {
        $editKey = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Story – LoveFun 💖</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light-pink">

    <div class="container py-4">

        <!-- Header -->
        <div class="creator-header mb-3">
            <a href="index.php" class="text-decoration-none">
                <h2>Love<span class="text-pink">Fun</span> 💖</h2>
            </a>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps mb-4">
            <div class="progress-line">
                <div class="progress-line-fill" id="progressFill"></div>
            </div>
            <div class="progress-step active" data-step="1">
                <div class="step-dot">1</div>
                <span class="step-label">Your Story</span>
            </div>
            <div class="progress-step" data-step="2">
                <div class="step-dot">2</div>
                <span class="step-label">Add Blocks</span>
            </div>
            <div class="progress-step" data-step="3">
                <div class="step-dot">3</div>
                <span class="step-label">Preview & Share</span>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">

                <!-- Been Together (Optional) -->
                <div class="glass-card p-4 mb-4" id="coupleSection">
                    <h5 class="fw-bold font-heading mb-1"><i class="bi bi-hearts me-2"></i>Been Together</h5>
                    <p class="text-muted small mb-3">Optional — add your photos and anniversary date</p>

                    <div class="row g-3 mb-3">
                        <!-- Your Photo -->
                        <div class="col-6">
                            <label class="form-label small fw-bold">Your Photo</label>
                            <div class="couple-photo-box" id="yourPhotoBox">
                                <div class="couple-photo-placeholder" id="yourPhotoPlaceholder">
                                    <i class="bi bi-person-fill"></i>
                                    <span>Tap to add</span>
                                </div>
                                <img src="" alt="" class="couple-photo-img d-none" id="yourPhotoImg">
                                <input type="hidden" id="yourPhotoUrl">
                            </div>
                        </div>

                        <!-- Partner Photo -->
                        <div class="col-6">
                            <label class="form-label small fw-bold">Partner's Photo</label>
                            <div class="couple-photo-box" id="partnerPhotoBox">
                                <div class="couple-photo-placeholder" id="partnerPhotoPlaceholder">
                                    <i class="bi bi-person-heart"></i>
                                    <span>Tap to add</span>
                                </div>
                                <img src="" alt="" class="couple-photo-img d-none" id="partnerPhotoImg">
                                <input type="hidden" id="partnerPhotoUrl">
                            </div>
                        </div>
                    </div>

                    <!-- Anniversary Date -->
                    <div>
                        <label class="form-label small fw-bold"><i class="bi bi-calendar-heart me-1"></i>Anniversary Date</label>
                        <input type="date" class="form-control" id="anniversaryDate"
                               style="border: 2px solid rgba(233,30,99,0.1); border-radius: 14px; padding: 12px 16px;">
                    </div>
                </div>

                <!-- Hidden file inputs for couple photos -->
                <input type="file" id="hiddenYourPhoto" accept="image/*" style="display:none">
                <input type="file" id="hiddenPartnerPhoto" accept="image/*" style="display:none">

                <!-- Blocks Container -->
                <div id="blocksContainer">
                    <!-- Empty state shown initially -->
                    <div class="empty-state glass-card p-5 mb-3" id="emptyState">
                        <div class="empty-state-icon">💌</div>
                        <p class="fw-bold mb-1" style="color: var(--text-muted);">Your story is empty</p>
                        <p class="small" style="color: var(--text-light);">Add your first block below to begin</p>
                    </div>
                </div>

                <!-- Add Block Buttons -->
                <div class="glass-card p-4 mb-4 text-center">
                    <p class="fw-bold font-heading mb-3" style="font-size:1.05rem;">Add a Block</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <button type="button" class="add-block-btn" id="addTextBlock">
                            <i class="bi bi-chat-quote-fill d-block mb-1"></i>Text
                        </button>
                        <button type="button" class="add-block-btn" id="addPhotoBlock">
                            <i class="bi bi-image-fill d-block mb-1"></i>Photo
                        </button>
                        <button type="button" class="add-block-btn" id="addAudioBlock">
                            <i class="bi bi-music-note-beamed d-block mb-1"></i>Audio
                        </button>
                        <button type="button" class="add-block-btn" id="addGameBlock">
                            <i class="bi bi-joystick d-block mb-1"></i>Yes / No Game
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <a href="index.php" class="btn btn-outline-secondary px-4" style="border-radius:50px;">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                    <button type="button" id="previewBtn" class="btn btn-pink px-5 py-3">
                        <i class="bi bi-eye-fill me-2"></i>Preview & Share
                    </button>
                </div>

                <p class="text-center text-muted small mb-3">
                    <i class="bi bi-shield-check me-1"></i>No account needed &middot; Free to use &middot; Shareable link
                </p>
            </div>
        </div>
    </div>

    <!-- Hidden file input for photo uploads -->
    <input type="file" id="hiddenFileInput" accept="image/*" style="display:none">
    <!-- Hidden file input for audio uploads -->
    <input type="file" id="hiddenAudioInput" accept="audio/mpeg,audio/mp3,.mp3" style="display:none">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <?php if ($editStoryData): ?>
    <script>window.EDIT_STORY_DATA = <?= json_encode($editStoryData) ?>;</script>
    <?php endif; ?>
    <script src="assets/js/app.js"></script>
</body>
</html>
