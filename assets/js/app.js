/**
 * LoveFun – app.js
 * Professional, clean, no over-engineering.
 */
$(function () {

    /* ===========================================
       INTRO SPLASH  (view.php)
       =========================================== */
    $('#openStoryBtn').on('click', function () {
        var $s = $('#introSplash');
        $s.css({ opacity: 0, visibility: 'hidden' });
        setTimeout(function () { $s.remove(); $('#storyContent').show(); }, 600);
    });

    /* ===========================================
       STORY BUILDER  (create.php)
       =========================================== */
    var blockId = 0;

    function updateProgress() {
        var n = $('#blocksContainer .block-card').length;
        if (n) { $('.progress-step[data-step="2"]').addClass('active'); $('#progressFill').css('width','50%'); $('#emptyState').slideUp(300); }
        else   { $('.progress-step[data-step="2"]').removeClass('active'); $('#progressFill').css('width','0%'); $('#emptyState').slideDown(300); }
    }

    /* ---- Couple Photo Uploads (Been Together) ---- */
    var coupleTarget = null; // 'your' or 'partner'

    $('#yourPhotoBox').on('click', function () {
        coupleTarget = 'your';
        $('#hiddenYourPhoto').val('').trigger('click');
    });
    $('#partnerPhotoBox').on('click', function () {
        coupleTarget = 'partner';
        $('#hiddenPartnerPhoto').val('').trigger('click');
    });

    $('#hiddenYourPhoto').on('change', function () {
        var file = this.files[0]; if (!file) return;
        uploadCouplePhoto(file, 'your');
    });
    $('#hiddenPartnerPhoto').on('change', function () {
        var file = this.files[0]; if (!file) return;
        uploadCouplePhoto(file, 'partner');
    });

    function uploadCouplePhoto(file, who) {
        var $box = who === 'your' ? $('#yourPhotoBox') : $('#partnerPhotoBox');
        var $img = who === 'your' ? $('#yourPhotoImg') : $('#partnerPhotoImg');
        var $placeholder = who === 'your' ? $('#yourPhotoPlaceholder') : $('#partnerPhotoPlaceholder');
        var $url = who === 'your' ? $('#yourPhotoUrl') : $('#partnerPhotoUrl');

        $placeholder.html('<div class="spinner-border spinner-border-sm text-pink"></div>');
        var fd = new FormData(); fd.append('photo', file);
        $.ajax({ url:'save.php?action=upload', type:'POST', data:fd, processData:false, contentType:false,
            success: function (r) {
                if (r.success) {
                    $url.val(r.url);
                    $img.attr('src', r.url).removeClass('d-none');
                    $placeholder.addClass('d-none');
                } else {
                    alert(r.message||'Upload failed.');
                    $placeholder.html('<i class="bi bi-' + (who === 'your' ? 'person-fill' : 'person-heart') + '"></i><span>Tap to add</span>');
                }
            },
            error: function () {
                alert('Upload failed.');
                $placeholder.html('<i class="bi bi-' + (who === 'your' ? 'person-fill' : 'person-heart') + '"></i><span>Tap to add</span>');
            }
        });
    }

    $('#addTextBlock').on('click', function () {
        blockId++;
        $('#blocksContainer').append(
            '<div class="card block-card p-4 mb-3" data-block-id="'+blockId+'" data-type="text">' +
            '<button type="button" class="block-remove" title="Remove">&times;</button>' +
            '<span class="badge bg-info block-type-badge mb-2"><i class="bi bi-chat-quote-fill me-1"></i>Text</span>' +
            '<textarea class="form-control block-value" rows="3" placeholder="Write something sweet…"></textarea>' +
            '</div>');
        updateProgress();
    });

    var activePhoto = null;
    $('#addPhotoBlock').on('click', function () {
        blockId++;
        $('#blocksContainer').append(
            '<div class="card block-card p-4 mb-3" data-block-id="'+blockId+'" data-type="photo">' +
            '<button type="button" class="block-remove" title="Remove">&times;</button>' +
            '<span class="badge bg-success block-type-badge mb-2"><i class="bi bi-image-fill me-1"></i>Photo</span>' +
            '<div class="text-center py-3">' +
            '<div class="photo-preview mb-3 d-none"><img src="" alt="" class="photo-preview-img"></div>' +
            '<button type="button" class="add-block-btn choose-photo px-4"><i class="bi bi-cloud-arrow-up-fill d-block mb-1" style="font-size:1.5rem"></i>Upload Photo</button>' +
            '<input type="hidden" class="block-value">' +
            '<div class="upload-progress d-none mt-3"><div class="d-flex align-items-center justify-content-center gap-2"><div class="spinner-border spinner-border-sm text-pink"></div><small>Uploading…</small></div></div>' +
            '</div></div>');
        updateProgress();
    });

    $(document).on('click', '.choose-photo', function () {
        activePhoto = $(this).closest('.block-card').data('block-id');
        $('#hiddenFileInput').val('').trigger('click');
    });

    $('#hiddenFileInput').on('change', function () {
        var file = this.files[0]; if (!file || !activePhoto) return;
        var $b = $('.block-card[data-block-id="'+activePhoto+'"]');
        $b.find('.upload-progress').removeClass('d-none');
        $b.find('.choose-photo').addClass('d-none');
        var fd = new FormData(); fd.append('photo', file);
        $.ajax({ url:'save.php?action=upload', type:'POST', data:fd, processData:false, contentType:false,
            success: function (r) {
                $b.find('.upload-progress').addClass('d-none');
                if (r.success) { $b.find('.block-value').val(r.url); $b.find('.photo-preview').removeClass('d-none').find('img').attr('src',r.url); $b.find('.choose-photo').html('<i class="bi bi-arrow-repeat me-1"></i>Change').removeClass('d-none'); }
                else { alert(r.message||'Upload failed.'); $b.find('.choose-photo').removeClass('d-none'); }
            },
            error: function () { $b.find('.upload-progress,.choose-photo').toggleClass('d-none'); alert('Upload failed.'); }
        });
    });

    /* ---- Audio Block ---- */
    var activeAudio = null;

    function buildCustomPlayerHtml() {
        return '<div class="audio-block-preview">' +
            '<audio class="lf-audio-src" preload="metadata"></audio>' +
            '<div class="lf-player">' +
                '<div class="lf-player-top">' +
                    '<div class="lf-disc-wrap"><div class="lf-disc"><div class="lf-disc-inner"></div></div></div>' +
                    '<div class="lf-player-info">' +
                        '<p class="lf-track-title">Uploaded audio</p>' +
                        '<div class="lf-equalizer"><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span><span class="lf-eq-bar"></span></div>' +
                    '</div>' +
                '</div>' +
                '<div class="lf-seek-wrap">' +
                    '<div class="lf-seek-bar"><div class="lf-seek-fill"></div><div class="lf-seek-thumb"></div></div>' +
                    '<div class="lf-time-row"><span class="lf-time-current">0:00</span><span class="lf-time-total">0:00</span></div>' +
                '</div>' +
                '<div class="lf-controls"><button class="lf-btn-play" aria-label="Play"><i class="bi bi-play-fill"></i></button></div>' +
            '</div>' +
        '</div>';
    }

    function initCustomPlayer($container) {
        var $player = $container.find('.lf-player');
        var audio   = $container.find('.lf-audio-src')[0];
        if (!audio || !$player.length) return;

        var $btnPlay   = $player.find('.lf-btn-play');
        var $seekBar   = $player.find('.lf-seek-bar');
        var $seekFill  = $player.find('.lf-seek-fill');
        var $seekThumb = $player.find('.lf-seek-thumb');
        var $timeCur   = $player.find('.lf-time-current');
        var $timeTotal = $player.find('.lf-time-total');
        var seeking    = false;

        function fmtTime(s) {
            if (isNaN(s) || !isFinite(s)) return '0:00';
            var m = Math.floor(s / 60);
            var sec = Math.floor(s % 60);
            return m + ':' + (sec < 10 ? '0' : '') + sec;
        }

        $btnPlay.off('click').on('click', function () {
            if (audio.paused) {
                $('.lf-audio-src').each(function () { if (this !== audio && !this.paused) { this.pause(); $(this).closest('.audio-block, .audio-block-preview, .block-card').find('.lf-player').removeClass('playing').find('.lf-btn-play i').attr('class','bi bi-play-fill'); } });
                audio.play();
                $player.addClass('playing');
                $btnPlay.find('i').attr('class', 'bi bi-pause-fill').css('margin-left', '0');
            } else {
                audio.pause();
                $player.removeClass('playing');
                $btnPlay.find('i').attr('class', 'bi bi-play-fill').css('margin-left', '3px');
            }
        });

        $(audio).on('loadedmetadata', function () { $timeTotal.text(fmtTime(audio.duration)); });

        $(audio).on('timeupdate', function () {
            if (seeking) return;
            var pct = (audio.currentTime / audio.duration) * 100 || 0;
            $seekFill.css('width', pct + '%');
            $seekThumb.css('left', pct + '%');
            $timeCur.text(fmtTime(audio.currentTime));
        });

        $(audio).on('ended', function () {
            $player.removeClass('playing');
            $btnPlay.find('i').attr('class', 'bi bi-play-fill').css('margin-left', '3px');
            $seekFill.css('width', '0%');
            $seekThumb.css('left', '0%');
            $timeCur.text('0:00');
        });

        $seekBar.on('mousedown touchstart', function (e) {
            seeking = true;
            doSeek(e);
            $(document).on('mousemove.lfseek touchmove.lfseek', function (ev) { doSeek(ev); });
            $(document).on('mouseup.lfseek touchend.lfseek', function () { seeking = false; $(document).off('.lfseek'); });
        });

        function doSeek(e) {
            var ev   = e.originalEvent.touches ? e.originalEvent.touches[0] : e;
            var rect = $seekBar[0].getBoundingClientRect();
            var pct  = Math.max(0, Math.min(1, (ev.clientX - rect.left) / rect.width));
            $seekFill.css('width', (pct * 100) + '%');
            $seekThumb.css('left', (pct * 100) + '%');
            if (audio.duration) { audio.currentTime = pct * audio.duration; $timeCur.text(fmtTime(audio.currentTime)); }
        }
    }

    $('#addAudioBlock').on('click', function () {
        blockId++;
        $('#blocksContainer').append(
            '<div class="card block-card p-4 mb-3" data-block-id="'+blockId+'" data-type="audio">' +
            '<button type="button" class="block-remove" title="Remove">&times;</button>' +
            '<span class="badge bg-purple block-type-badge mb-2"><i class="bi bi-music-note-beamed me-1"></i>Audio</span>' +
            '<div class="mb-3"><label class="form-label small fw-bold">Caption <span class="text-muted fw-normal">(optional)</span></label>' +
            '<input type="text" class="form-control audio-caption" placeholder="Our song, a voice note…"></div>' +
            '<div class="text-center py-3">' +
            '<div class="audio-preview mb-3 d-none"></div>' +
            '<button type="button" class="add-block-btn choose-audio px-4"><i class="bi bi-cloud-arrow-up-fill d-block mb-1" style="font-size:1.5rem"></i>Upload Audio</button>' +
            '<input type="hidden" class="block-value">' +
            '<div class="upload-progress-audio d-none mt-3"><div class="d-flex align-items-center justify-content-center gap-2"><div class="spinner-border spinner-border-sm text-pink"></div><small>Uploading…</small></div></div>' +
            '<p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>MP3, WAV, OGG, M4A — max 10MB</p>' +
            '</div></div>');
        updateProgress();
    });

    $(document).on('click', '.choose-audio', function () {
        activeAudio = $(this).closest('.block-card').data('block-id');
        $('#hiddenAudioInput').val('').trigger('click');
    });

    $('#hiddenAudioInput').on('change', function () {
        var file = this.files[0]; if (!file || !activeAudio) return;
        var $b = $('.block-card[data-block-id="'+activeAudio+'"]');
        $b.find('.upload-progress-audio').removeClass('d-none');
        $b.find('.choose-audio').addClass('d-none');
        var fd = new FormData(); fd.append('audio', file);
        $.ajax({ url:'save.php?action=upload_audio', type:'POST', data:fd, processData:false, contentType:false,
            success: function (r) {
                $b.find('.upload-progress-audio').addClass('d-none');
                if (r.success) {
                    $b.find('.block-value').val(r.url);
                    // Build custom player preview
                    var $preview = $b.find('.audio-preview');
                    $preview.html(buildCustomPlayerHtml()).removeClass('d-none');
                    $preview.find('.lf-audio-src').attr('src', r.url);
                    // Update title from caption
                    var cap = $b.find('.audio-caption').val().trim();
                    if (cap) $preview.find('.lf-track-title').text(cap);
                    initCustomPlayer($preview);
                    $b.find('.choose-audio').html('<i class="bi bi-arrow-repeat me-1"></i>Change').removeClass('d-none');
                } else {
                    alert(r.message||'Upload failed.');
                    $b.find('.choose-audio').removeClass('d-none');
                }
            },
            error: function () { $b.find('.upload-progress-audio,.choose-audio').toggleClass('d-none'); alert('Upload failed.'); }
        });
    });

    /* Update player title when caption changes */
    $(document).on('input', '.audio-caption', function () {
        var $b = $(this).closest('.block-card');
        var cap = $(this).val().trim();
        $b.find('.lf-track-title').text(cap || 'Uploaded audio');
    });

    /* ---- Game Block ---- */
    $('#addGameBlock').on('click', function () {
        if ($('.block-card[data-type="game"]').length) { alert('Only one game per story.'); return; }
        blockId++;
        $('#blocksContainer').append(
            '<div class="card block-card p-4 mb-3" data-block-id="'+blockId+'" data-type="game">' +
            '<button type="button" class="block-remove" title="Remove">&times;</button>' +
            '<span class="badge bg-danger block-type-badge mb-2"><i class="bi bi-joystick me-1"></i>Yes / No Game</span>' +
            '<div class="row g-3 mb-3"><div class="col-6"><label class="form-label small fw-bold">Yes Button</label><input type="text" class="form-control game-yes-text" value="YES ❤️"></div>' +
            '<div class="col-6"><label class="form-label small fw-bold">No Button</label><input type="text" class="form-control game-no-text" value="NO 😒"></div></div>' +
            '<div><label class="form-label small fw-bold">Message After "Yes"</label><input type="text" class="form-control game-success-msg" value="See you on Valentine 💘"></div>' +
            '</div>');
        updateProgress();
    });

    $(document).on('click', '.block-remove', function () {
        var $c = $(this).closest('.block-card');
        $c.css({ transform:'scale(0.9)', opacity:0 });
        setTimeout(function () { $c.remove(); updateProgress(); }, 300);
    });

    $('#previewBtn').on('click', function () {
        var $btn = $(this), blocks = [], err = false;
        $('#blocksContainer .block-card').each(function () {
            var t = $(this).data('type');
            if (t === 'text') { var v = $(this).find('.block-value').val().trim(); if (!v) { err=true; $(this).find('.block-value').addClass('is-invalid').focus(); return false; } $(this).find('.block-value').removeClass('is-invalid'); blocks.push({type:'text',value:v}); }
            else if (t === 'photo') { var u = $(this).find('.block-value').val().trim(); if (!u) { err=true; alert('Upload a photo first.'); return false; } blocks.push({type:'photo',url:u}); }
            else if (t === 'audio') { var au = $(this).find('.block-value').val().trim(); if (!au) { err=true; alert('Upload an audio file first.'); return false; } blocks.push({type:'audio',url:au,caption:$(this).find('.audio-caption').val().trim()}); }
            else if (t === 'game') { blocks.push({type:'game', yesText:$(this).find('.game-yes-text').val()||'YES ❤️', noText:$(this).find('.game-no-text').val()||'NO 😒', noBehavior:'run', successMessage:$(this).find('.game-success-msg').val()||'See you on Valentine 💘'}); }
        });
        if (err || !blocks.length) { if (!blocks.length && !err) alert('Add at least one block.'); return; }
        // Gather optional couple data
        var coupleData = {};
        var yourPhoto = $('#yourPhotoUrl').val();
        var partnerPhoto = $('#partnerPhotoUrl').val();
        var annDate = $('#anniversaryDate').val();
        if (yourPhoto)    coupleData.yourPhoto = yourPhoto;
        if (partnerPhoto) coupleData.partnerPhoto = partnerPhoto;
        if (annDate)      coupleData.anniversaryDate = annDate;

        $btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating…');
        $.ajax({ url:'save.php', type:'POST', contentType:'application/json', data:JSON.stringify({couple:coupleData,blocks:blocks}),
            success: function (r) { if (r.success) window.location.href=r.redirect; else { alert(r.message||'Error'); $btn.prop('disabled',false).html('<i class="bi bi-eye-fill me-2"></i>Preview & Share'); } },
            error:   function ()  { alert('Network error.'); $btn.prop('disabled',false).html('<i class="bi bi-eye-fill me-2"></i>Preview & Share'); }
        });
    });

    /* ===========================================
       COPY LINK  (preview.php)
       =========================================== */
    $('#copyLinkBtn').on('click', function () {
        var $b = $(this);
        navigator.clipboard.writeText($('#shareLink').val()).then(function () {
            $b.html('<i class="bi bi-check-lg me-1"></i>Copied!').addClass('btn-success').removeClass('btn-pink');
            $('#copyFeedback').removeClass('d-none');
            setTimeout(function () { $b.html('<i class="bi bi-clipboard me-1"></i>Copy').removeClass('btn-success').addClass('btn-pink'); $('#copyFeedback').addClass('d-none'); }, 3000);
        });
    });

    /* ===========================================
       YES / NO  GAME  (view.php)
       Professional rewrite — dead simple.
       =========================================== */

    var STEPS    = 10;                       // total interactions to reach end-state
    var PAD      = 10;                       // px inside the arena border

    /* ---- move NO to a random spot inside .game-arena ---- */
    function moveNo($no) {
        var $arena = $no.closest('.game-arena');
        var aw     = $arena.width();
        var ah     = $arena.height();
        var bw     = $no.outerWidth();
        var bh     = $no.outerHeight();

        // Random x,y with 10px padding, ensuring button fits fully
        var x = PAD + Math.random() * Math.max(0, aw - bw - PAD * 2);
        var y = PAD + Math.random() * Math.max(0, ah - bh - PAD * 2);

        // Remove the centered transform on first move
        $no.css({ transform: 'none', left: x + 'px', top: y + 'px' });
    }

    /* ---- visual progression ---- */
    function updateVisuals($block, step) {
        var t    = Math.min(step / STEPS, 1);          // 0 → 1
        var $yes = $block.find('.game-yes');
        var $no  = $block.find('.game-no');

        // NO: shrink padding/font, fade color to gray
        var noPad  = 20 - 12 * t;                      // 20 → 8
        var noPadH = 52 - 32 * t;                      // 52 → 20
        var noFont = 1.6 - 0.8 * t;                    // 1.6 → 0.8
        var nr = Math.round(233 + (160 - 233) * t);
        var ng = Math.round(30  + (160 - 30)  * t);
        var nb = Math.round(99  + (170 - 99)  * t);
        $no.css({
            padding: noPad + 'px ' + noPadH + 'px',
            fontSize: noFont + 'rem',
            background: 'rgb('+nr+','+ng+','+nb+')',
            boxShadow: '0 '+(6-4*t)+'px '+(25-18*t)+'px rgba(0,0,0,'+(0.3-0.2*t)+')',
            opacity: 1 - t * 0.3
        });

        // YES: grow padding/font, fill with pink
        var yesPad  = 8  + 16 * t;                     // 8 → 24
        var yesPadH = 20 + 36 * t;                     // 20 → 56
        var yesFont = 0.8 + 1.2 * t;                   // 0.8 → 2.0
        var yr = Math.round(170 + (233 - 170) * t);
        var yg = Math.round(170 + (30  - 170) * t);
        var yb = Math.round(180 + (99  - 180) * t);
        $yes.css({
            padding: yesPad + 'px ' + yesPadH + 'px',
            fontSize: yesFont + 'rem',
            background: t > 0.1 ? 'linear-gradient(135deg,rgb('+yr+','+yg+','+yb+'),rgb('+Math.min(yr+30,255)+','+Math.min(yg+40,255)+','+Math.min(yb+20,255)+'))' : '',
            color:   t > 0.2 ? '#fff' : '',
            border:  t > 0.2 ? '2px solid transparent' : '',
            boxShadow: t > 0.25 ? '0 '+(t*8)+'px '+(t*30)+'px rgba(233,30,99,'+(t*0.5)+')' : 'none'
        });

        if (t >= 0.8) $yes.addClass('final');
    }

    /* ---- debounce: move every time, step once per 200ms ---- */
    var lastStep = 0;

    function onNoInteraction($no) {
        moveNo($no);

        var now = Date.now();
        if (now - lastStep >= 200) {
            lastStep = now;
            var $block = $no.closest('.game-block');
            var step   = parseInt($block.attr('data-step') || '0');
            step = Math.min(step + 1, STEPS);
            $block.attr('data-step', step);
            updateVisuals($block, step);
        }
    }

    /* events: mouseenter, touchstart, click */
    $(document).on('mouseenter',  '.game-no', function ()  { onNoInteraction($(this)); });
    $(document).on('touchstart',  '.game-no', function (e) { e.preventDefault(); onNoInteraction($(this)); });
    $(document).on('click',       '.game-no', function (e) { e.preventDefault(); onNoInteraction($(this)); });

    /* ---- YES click: win ---- */
    $(document).on('click', '.game-yes', function () {
        var $block = $(this).closest('.game-block');
        var msg    = $block.data('success-message');
        var $arena = $block.find('.game-arena');
        var $msg   = $block.find('.game-success-message');

        // Fade out arena, show message
        $arena.css({ opacity: 0, transition: 'opacity 0.4s' });
        setTimeout(function () {
            $arena.addClass('d-none');
            $msg.removeClass('d-none').addClass('show').find('.success-text').text(msg);
        }, 400);

        // Confetti x3
        confetti(); setTimeout(confetti, 1000); setTimeout(confetti, 2200);
    });

    /* ===========================================
       CUSTOM AUDIO PLAYER INIT  (view.php / preview.php)
       =========================================== */
    $('.audio-block').each(function () {
        initCustomPlayer($(this));
    });

    /* ===========================================
       COUPLE DAY COUNTER  (view.php / preview.php)
       =========================================== */
    $('.couple-counter').each(function () {
        var $el   = $(this);
        var dStr  = $el.data('date');
        if (!dStr) return;
        var start = new Date(dStr);
        if (isNaN(start.getTime())) return;

        function updateCounter() {
            var now  = new Date();
            var diff = Math.floor((now - start) / (1000 * 60 * 60 * 24));
            if (diff < 0) diff = 0;
            $el.find('.counter-days').text(diff.toLocaleString());
        }
        updateCounter();
        setInterval(updateCounter, 60000); // refresh every minute
    });

    /* ===========================================
       CONFETTI
       =========================================== */
    function confetti() {
        var c = document.getElementById('confettiCanvas'); if (!c) return;
        var ctx = c.getContext('2d');
        c.width = window.innerWidth; c.height = window.innerHeight;
        var colors = ['#e91e63','#ff5252','#ff4081','#f48fb1','#ffd54f','#ffab40','#4fc3f7','#81c784','#ba68c8'];
        var p = [];
        for (var i = 0; i < 150; i++) p.push({ x:c.width/2+(Math.random()-0.5)*200, y:c.height/2, w:4+Math.random()*8, h:3+Math.random()*5, c:colors[Math.floor(Math.random()*colors.length)], vx:(Math.random()-0.5)*16, vy:-4-Math.random()*18, g:0.25+Math.random()*0.15, r:Math.random()*360, s:(Math.random()-0.5)*15, o:1, d:0.003+Math.random()*0.003 });
        (function draw() {
            ctx.clearRect(0,0,c.width,c.height); var alive=false;
            p.forEach(function(q){ if(q.o<=0)return; alive=true; ctx.save(); ctx.globalAlpha=q.o; ctx.translate(q.x,q.y); ctx.rotate(q.r*Math.PI/180); ctx.fillStyle=q.c; ctx.fillRect(-q.w/2,-q.h/2,q.w,q.h); ctx.restore(); q.vy+=q.g; q.y+=q.vy; q.x+=q.vx; q.vx*=0.99; q.r+=q.s; q.o-=q.d; });
            if(alive) requestAnimationFrame(draw); else ctx.clearRect(0,0,c.width,c.height);
        })();
    }

    /* init */
    if ($('#blocksContainer').length) updateProgress();
});
