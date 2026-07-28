@if(isset($employeeOfTheMonth) && $employeeOfTheMonth)
<!-- Employee of the Month Overlay - Theme-Aware (Light/Dark) -->
<div id="customEotmOverlay" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.25); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); z-index: 999999 !important; display: flex; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;">
    
    <!-- Modal Card Container -->
    <div class="eotm-card-container" style="width: 100%; max-width: 680px; border-radius: 28px; border: 2px solid rgba(245, 158, 11, 0.5); overflow: hidden; position: relative; pointer-events: auto !important; max-height: 94vh; overflow-y: auto; font-family: 'Segoe UI', system-ui, sans-serif;">
        
        <!-- Background Sunburst Ray Lines -->
        <div class="eotm-sunburst" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none;"></div>
        
        <!-- Confetti Particles -->
        <div style="position: absolute; top: 20px; left: 6%; font-size: 22px; pointer-events: none; animation: floatParticle 3s infinite ease-in-out;">✨</div>
        <div style="position: absolute; top: 30px; right: 8%; font-size: 20px; pointer-events: none; animation: floatParticle 2.5s infinite ease-in-out 0.4s;">🎉</div>
        <div style="position: absolute; bottom: 80px; left: 5%; font-size: 18px; pointer-events: none; animation: floatParticle 3.5s infinite ease-in-out 0.8s;">🌟</div>
        <div style="position: absolute; bottom: 90px; right: 5%; font-size: 20px; pointer-events: none; animation: floatParticle 2.8s infinite ease-in-out 0.2s;">💫</div>

        <!-- Close Button -->
        <button type="button" onclick="closeEotmOverlay()" class="eotm-close-btn"
                style="position: absolute; right: 18px; top: 18px; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; cursor: pointer; z-index: 10; transition: all 0.2s ease;" 
                title="Tutup Pop-up">✕</button>

        <!-- Body Content -->
        <div class="eotm-body-padding" style="padding: 30px 24px 32px 24px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; z-index: 2; width: 100%; box-sizing: border-box;">
            
            <!-- 1. Cursive "Congratulations!" -->
            <div class="eotm-congrats-text" style="font-family: 'Brush Script MT', 'Playfair Display', cursive, sans-serif; font-size: 44px; font-weight: bold; line-height: 1.1; margin-bottom: 6px;">
                Congratulations!
            </div>

            <!-- 2. Arched Title Banner -->
            <div style="margin-bottom: 20px;">
                <div style="background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #92400e 100%); padding: 6px 22px; border-radius: 50px; border: 1px solid rgba(254, 240, 138, 0.8); box-shadow: 0 4px 20px rgba(245, 158, 11, 0.45); display: inline-block;">
                    <span style="font-size: 12.5px; font-weight: 900; letter-spacing: 2.5px; text-transform: uppercase; color: #0f172a; text-shadow: 0 1px 0 rgba(255,255,255,0.4); display: block;">
                        BEST EMPLOYEE OF THE MONTH • {{ $employeeOfTheMonth['period_label'] }}
                    </span>
                </div>
            </div>

            <!-- 3. CENTERPIECE: Wreath + Avatar + Name Ribbon -->
            <div class="eotm-centerpiece-wrapper" style="width: 210px; height: 210px; position: relative; margin: 0 auto 30px auto; display: flex; align-items: center; justify-content: center;">
                
                <!-- SVG Laurel Wreath -->
                <div style="position: absolute; top: 0; left: 0; width: 210px; height: 210px; pointer-events: none; z-index: 1;">
                    <svg width="210" height="210" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g fill="url(#goldGrad)">
                            <path d="M70 160 C50 140 35 110 40 75 C42 60 50 45 62 35 C55 45 52 58 52 70 C52 95 62 120 78 140 Z"/>
                            <path d="M45 130 C30 120 25 105 32 95 C38 105 50 115 55 125 Z"/>
                            <path d="M40 100 C25 90 22 75 30 65 C36 75 46 85 50 95 Z"/>
                            <path d="M48 70 C35 55 35 40 45 32 C48 42 56 55 58 65 Z"/>
                            <path d="M62 45 C52 32 55 20 66 15 C67 25 72 37 72 45 Z"/>
                        </g>
                        <g fill="url(#goldGrad)">
                            <path d="M130 160 C150 140 165 110 160 75 C158 60 150 45 138 35 C145 45 148 58 148 70 C148 95 138 120 122 140 Z"/>
                            <path d="M155 130 C170 120 175 105 168 95 C162 105 150 115 145 125 Z"/>
                            <path d="M160 100 C175 90 178 75 170 65 C164 75 154 85 150 95 Z"/>
                            <path d="M152 70 C165 55 165 40 155 32 C152 42 144 55 142 65 Z"/>
                            <path d="M138 45 C148 32 145 20 134 15 C133 25 128 37 128 45 Z"/>
                        </g>
                        <defs>
                            <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#fef08a"/>
                                <stop offset="50%" stop-color="#f59e0b"/>
                                <stop offset="100%" stop-color="#92400e"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

                <!-- Avatar Circle -->
                <div style="padding: 6px; border-radius: 50%; background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #78350f 100%); box-shadow: 0 0 35px rgba(245, 158, 11, 0.5); position: relative; z-index: 2;">
                    @if(!empty($employeeOfTheMonth['photo']))
                        <img src="{{ asset('storage/' . $employeeOfTheMonth['photo']) }}" alt="{{ $employeeOfTheMonth['fullname'] }}" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; display: block;">
                    @else
                        <div class="eotm-avatar-initials" style="width: 130px; height: 130px; border-radius: 50%; font-size: 46px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 4px solid #ffffff;">
                            {{ Str::upper(substr($employeeOfTheMonth['fullname'], 0, 2)) }}
                        </div>
                    @endif
                </div>

                <!-- Gold Crown -->
                <span style="position: absolute; top: 0px; left: 50%; transform: translateX(-50%); font-size: 26px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4)); z-index: 5;">👑</span>

                <!-- Name Ribbon -->
                <div style="position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); width: 220px; background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #b45309 100%); padding: 6px 10px; border-radius: 8px; border: 2px solid #ffffff; box-shadow: 0 6px 20px rgba(0,0,0,0.3), 0 0 15px rgba(245, 158, 11, 0.4); z-index: 5;">
                    <div style="font-size: 15px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 1px 0 rgba(255,255,255,0.5);">
                        {{ $employeeOfTheMonth['fullname'] }}
                    </div>
                </div>
            </div>

            <!-- 4. Department & Position Badges -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; width: 100%;">
                <span class="eotm-badge-dept" style="font-size: 12.5px; border-radius: 12px; padding: 5px 14px; font-weight: 700;">
                    🏢 {{ $employeeOfTheMonth['department'] }}
                </span>
                <span class="eotm-badge-pos" style="font-size: 12.5px; border-radius: 12px; padding: 5px 14px; font-weight: 700;">
                    💼 {{ $employeeOfTheMonth['position'] }}
                </span>
            </div>

            <!-- 5. KPI Score Pill -->
            <div class="eotm-score-pill" style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 18px; margin-bottom: 18px; border-radius: 50px;">
                <span class="eotm-score-label" style="font-size: 13px; font-weight: 700; margin-right: 8px;">Skor KPI Performa:</span>
                <span style="background: linear-gradient(135deg, #fef08a 0%, #f59e0b 100%); color: #0f172a; font-size: 16px; font-weight: 900; border-radius: 8px; padding: 3px 12px; box-shadow: 0 0 12px rgba(245, 158, 11, 0.4);">
                    {{ $employeeOfTheMonth['composite_score'] }} / 100
                </span>
            </div>

            <!-- 6. Appreciation Card -->
            <div class="eotm-appreciation-card" style="width: 100%; max-width: 520px; margin: 0 auto 20px auto; border-radius: 16px; padding: 12px 18px; box-sizing: border-box;">
                <p style="margin: 0; font-size: 13.5px; font-weight: 700; line-height: 1.5;">
                    ✨ <strong>Apresiasi Kinerja & Prestasi Tertinggi Perusahaan</strong><br>
                    <span class="eotm-appreciation-sub" style="font-weight: normal; font-size: 13px;">You are an essential member of our team, and your contributions are highly valued.</span>
                </p>
            </div>

            <!-- 7. Action Buttons -->
            <div class="eotm-btn-group" style="display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; width: 100%;">
                <a href="{{ route('kpi.company') }}" class="eotm-btn-primary" 
                   style="background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #d97706 100%); color: #0f172a; border: none; font-size: 14px; font-weight: 800; padding: 11px 24px; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.45);">
                    🏆 Lihat Company KPI
                </a>
                <button type="button" onclick="closeEotmOverlay()" class="eotm-btn-secondary" 
                        style="font-size: 14px; font-weight: 600; padding: 11px 24px; border-radius: 50px; cursor: pointer; transition: all 0.2s ease;">
                    Lanjutkan ke Dashboard
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Floating Trigger Button -->
<div style="position: fixed; bottom: 25px; right: 25px; z-index: 99998;">
    <button type="button" onclick="openEotmOverlay()" style="background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #d97706 100%); color: #0f172a; border: 2px solid #ffffff; font-weight: 900; border-radius: 50px; padding: 10px 20px; display: flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.5);">
        <span style="font-size: 18px;">🏆</span>
        <span style="font-size: 13px; letter-spacing: 0.5px;">Employee of the Month</span>
    </button>
</div>

<style>
@keyframes floatParticle {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.8; }
    50% { transform: translateY(-8px) scale(1.15); opacity: 1; }
}

/* ═══════════ LIGHT MODE (Default) ═══════════ */
.eotm-card-container {
    background: linear-gradient(145deg, #fffdf5 0%, #fef9e7 40%, #fdf4d6 100%);
    box-shadow: 0 30px 80px -10px rgba(0, 0, 0, 0.25), 0 0 50px rgba(245, 158, 11, 0.15);
    color: #1e293b;
}
.eotm-sunburst {
    background: repeating-conic-gradient(from 0deg, rgba(245, 158, 11, 0.04) 0deg 15deg, transparent 15deg 30deg);
}
.eotm-close-btn {
    background: rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.15);
    color: #78350f;
}
.eotm-congrats-text {
    color: #92400e;
    text-shadow: 0 0 18px rgba(245, 158, 11, 0.5), 0 2px 4px rgba(0,0,0,0.15);
}
.eotm-avatar-initials {
    background: linear-gradient(145deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    text-shadow: none;
}
.eotm-badge-dept {
    background: rgba(219, 234, 254, 0.85);
    color: #1d4ed8;
    border: 1px solid rgba(59, 130, 246, 0.3);
}
.eotm-badge-pos {
    background: rgba(243, 232, 255, 0.85);
    color: #7c3aed;
    border: 1px solid rgba(139, 92, 246, 0.3);
}
.eotm-score-pill {
    background: rgba(254, 243, 199, 0.7);
    border: 1px solid rgba(245, 158, 11, 0.4);
}
.eotm-score-label {
    color: #92400e;
}
.eotm-appreciation-card {
    background: rgba(254, 243, 199, 0.45);
    border: 1px solid rgba(245, 158, 11, 0.25);
}
.eotm-appreciation-card p {
    color: #78350f;
}
.eotm-appreciation-sub {
    color: #64748b !important;
}
.eotm-btn-secondary {
    background: rgba(0, 0, 0, 0.05);
    color: #92400e;
    border: 1px solid rgba(245, 158, 11, 0.4);
}

/* ═══════════ DARK MODE ═══════════ */
[data-bs-theme="dark"] .eotm-card-container {
    background: radial-gradient(circle at center, #1c233a 0%, #0e1322 65%, #05070f 100%);
    box-shadow: 0 30px 80px -10px rgba(0, 0, 0, 0.9), 0 0 60px rgba(245, 158, 11, 0.28);
    color: #ffffff;
}
[data-bs-theme="dark"] .eotm-sunburst {
    background: repeating-conic-gradient(from 0deg, rgba(245, 158, 11, 0.035) 0deg 15deg, transparent 15deg 30deg);
}
[data-bs-theme="dark"] .eotm-close-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #fffbeb;
}
[data-bs-theme="dark"] .eotm-congrats-text {
    color: #fef08a;
    text-shadow: 0 0 20px rgba(254, 240, 138, 0.85), 0 3px 6px rgba(0,0,0,0.8);
}
[data-bs-theme="dark"] .eotm-avatar-initials {
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    color: #fef08a;
    text-shadow: 0 0 12px rgba(254, 240, 138, 0.6);
}
[data-bs-theme="dark"] .eotm-badge-dept {
    background: rgba(30, 41, 59, 0.85);
    color: #93c5fd;
    border: 1px solid rgba(147, 197, 253, 0.35);
}
[data-bs-theme="dark"] .eotm-badge-pos {
    background: rgba(30, 41, 59, 0.85);
    color: #e9d5ff;
    border: 1px solid rgba(233, 213, 255, 0.35);
}
[data-bs-theme="dark"] .eotm-score-pill {
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid rgba(245, 158, 11, 0.45);
}
[data-bs-theme="dark"] .eotm-score-label {
    color: #fde68a;
}
[data-bs-theme="dark"] .eotm-appreciation-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
[data-bs-theme="dark"] .eotm-appreciation-card p {
    color: #fef08a;
}
[data-bs-theme="dark"] .eotm-appreciation-sub {
    color: #cbd5e1 !important;
}
[data-bs-theme="dark"] .eotm-btn-secondary {
    background: rgba(255, 255, 255, 0.08);
    color: #fef08a;
    border: 1px solid rgba(245, 158, 11, 0.4);
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .eotm-card-container {
        max-width: 95vw !important;
        border-radius: 24px !important;
    }
    .eotm-body-padding {
        padding: 24px 14px 26px 14px !important;
    }
    .eotm-congrats-text {
        font-size: 34px !important;
    }
    .eotm-centerpiece-wrapper {
        width: 180px !important;
        height: 180px !important;
        margin-bottom: 25px !important;
    }
    .eotm-centerpiece-wrapper svg {
        width: 180px !important;
        height: 180px !important;
    }
    .eotm-btn-group {
        flex-direction: column !important;
        width: 100% !important;
        gap: 10px !important;
    }
    .eotm-btn-primary, .eotm-btn-secondary {
        width: 100% !important;
        padding: 11px 18px !important;
    }
}
</style>

<!-- Confetti Canvas (fullscreen, above overlay) -->
<canvas id="eotmConfettiCanvas" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1000000; pointer-events: none;"></canvas>

<script>
    var EOTM_SHOWN_KEY = 'eotm_overlay_shown_{{ auth()->id() ?? 0 }}';

    function closeEotmOverlay() {
        var el = document.getElementById('customEotmOverlay');
        if (el) { el.style.display = 'none'; }
        // Mark as shown so it won't reappear when navigating back
        sessionStorage.setItem(EOTM_SHOWN_KEY, '1');
    }
    function openEotmOverlay() {
        var el = document.getElementById('customEotmOverlay');
        if (el) { el.style.display = 'flex'; }
    }

    // ═══════ Confetti Engine ═══════
    function launchConfetti() {
        var canvas = document.getElementById('eotmConfettiCanvas');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        var colors = ['#f59e0b','#fef08a','#ef4444','#22c55e','#3b82f6','#a855f7','#ec4899','#14b8a6','#ffffff'];
        var pieces = [];
        var totalPieces = 180;

        for (var i = 0; i < totalPieces; i++) {
            var originX = canvas.width * (0.3 + Math.random() * 0.4);
            var originY = canvas.height * 0.35;
            pieces.push({
                x: originX, y: originY,
                vx: (Math.random() - 0.5) * 18,
                vy: -(Math.random() * 14 + 4),
                w: Math.random() * 10 + 5,
                h: Math.random() * 6 + 3,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * 360,
                rotSpeed: (Math.random() - 0.5) * 12,
                gravity: 0.18 + Math.random() * 0.08,
                opacity: 1,
                decay: 0.003 + Math.random() * 0.004
            });
        }

        var frame = 0, maxFrames = 300;
        function animate() {
            frame++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            var alive = false;
            for (var i = 0; i < pieces.length; i++) {
                var p = pieces[i];
                if (p.opacity <= 0) continue;
                alive = true;
                p.vy += p.gravity; p.vx *= 0.985;
                p.x += p.vx; p.y += p.vy;
                p.rotation += p.rotSpeed;
                p.opacity -= p.decay;
                if (p.opacity < 0) p.opacity = 0;
                ctx.save();
                ctx.globalAlpha = p.opacity;
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rotation * Math.PI / 180);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                ctx.restore();
            }
            if (alive && frame < maxFrames) {
                requestAnimationFrame(animate);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                canvas.style.display = 'none';
            }
        }
        requestAnimationFrame(animate);
    }

    document.addEventListener("DOMContentLoaded", function() {
        var overlay = document.getElementById('customEotmOverlay');
        var canvas = document.getElementById('eotmConfettiCanvas');

        // Move overlay to body
        if (overlay && overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }

        // Check if overlay was already shown this session
        if (sessionStorage.getItem(EOTM_SHOWN_KEY)) {
            // Already shown → hide overlay and canvas immediately
            if (overlay) overlay.style.display = 'none';
            if (canvas) canvas.style.display = 'none';
        } else {
            // First time this session → show overlay + confetti, then mark as shown
            sessionStorage.setItem(EOTM_SHOWN_KEY, '1');
            if (overlay) overlay.style.display = 'flex';
            setTimeout(launchConfetti, 400);
        }
    });
</script>
@endif
