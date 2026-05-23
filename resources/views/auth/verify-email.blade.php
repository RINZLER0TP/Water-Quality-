<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Water Quality — Verificar email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=Space+Grotesk:wght@500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">

<canvas id="particles"></canvas>

<div class="card">
    <div class="left">
        <div class="brand">
            <div class="brand-ico">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c0 0-7 6.3-7 11.5a7 7 0 0 0 14 0C19 8.3 12 2 12 2z"/></svg>
            </div>
            <span class="brand-txt">Water Quality</span>
        </div>

        <div class="hero">
            <p class="hero-eyebrow">Plataforma IA · Monitoreo</p>
            <div class="hero-h" id="typingTarget"></div>
            <p class="hero-p">Revisa tu bandeja de entrada por el enlace de verificación. Si no lo recibiste, podemos reenviarlo.</p>
        </div>

        <div class="chips">
            <div class="chip"><div class="chip-v" id="c1">248</div><div class="chip-l">Datasets</div></div>
            <div class="chip"><div class="chip-v" id="c2">37</div><div class="chip-l">Modelos IA</div></div>
            <div class="chip"><div class="chip-v" id="c3">91%</div><div class="chip-l">Accuracy</div></div>
        </div>
    </div>

    <div class="right">
        <div class="fhead">
            <p class="ftag">Verificar email</p>
            <h1 class="ftitle">Casi listo</h1>
            <p class="fsub">Te enviamos un enlace de confirmación a tu correo electrónico.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert" style="display:block;background:#ecfeff;border-color:#bae6fd;color:#0369a1;margin-bottom:1.5rem;">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="btn-wrap" style="margin-top: 0;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn" type="submit" style="margin-bottom: 1rem;">
                    <span class="shine"></span>
                    <span>Reenviar enlace de verificación</span>
                </button>
            </form>
        </div>

        <div class="sep">o</div>
        
        <p class="reg">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;color:#0ea5e9;text-decoration:none;font-weight:600;font-family:inherit;font-size:inherit;cursor:pointer;">
                    Cerrar sesión
                </button>
            </form>
        </p>

        <div class="statusbar"><span class="dot"></span> Sistema operativo · v2.4.1</div>
    </div>
</div>

<script>
/* ---- PARTÍCULAS ---- */
(function(){
    const c = document.getElementById('particles');
    const ctx = c.getContext('2d');
    let W, H, pts = [];

    function resize(){
        W = c.width  = window.innerWidth;
        H = c.height = window.innerHeight;
        build();
    }

    function build(){
        pts = [];
        const n = Math.floor(W * H / 14000);
        for(let i = 0; i < n; i++){
            pts.push({
                x: Math.random() * W,
                y: Math.random() * H,
                vx: (Math.random() - .5) * .4,
                vy: (Math.random() - .5) * .4,
                r:  Math.random() * 1.5 + .5
            });
        }
    }

    function draw(){
        ctx.clearRect(0, 0, W, H);
        pts.forEach(p => {
            p.x += p.vx; p.y += p.vy;
            if(p.x < 0 || p.x > W) p.vx *= -1;
            if(p.y < 0 || p.y > H) p.vy *= -1;
        });
        for(let i = 0; i < pts.length; i++){
            for(let j = i + 1; j < pts.length; j++){
                const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y;
                const d = Math.sqrt(dx*dx + dy*dy);
                if(d < 120){
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(14,165,233,${(1-d/120)*.12})`;
                    ctx.lineWidth = .8;
                    ctx.moveTo(pts[i].x, pts[i].y);
                    ctx.lineTo(pts[j].x, pts[j].y);
                    ctx.stroke();
                }
            }
            ctx.beginPath();
            ctx.arc(pts[i].x, pts[i].y, pts[i].r, 0, Math.PI*2);
            ctx.fillStyle = 'rgba(14,165,233,.2)';
            ctx.fill();
        }
        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', resize);
    resize(); draw();
})();

/* ---- EFECTO DE ESCRITURA ---- */
(function(){
    const el = document.getElementById('typingTarget');
    const line1 = 'Verifica tu correo';
    const line2 = 'para continuar.';
    let phase = 0, ci = 0;

    function render(t1, t2, showCursor){
        const cur = showCursor ? '<span class="cursor"></span>' : '';
        if(phase < 2){
            el.innerHTML = t1 + cur;
        } else {
            el.innerHTML = t1 + '<br><b>' + t2 + '</b>' + cur;
        }
    }

    function tick(){
        if(phase === 0){
            if(ci <= line1.length){
                render(line1.slice(0, ci), '', true);
                ci++;
                setTimeout(tick, ci === 1 ? 900 : 55);
            } else {
                phase = 1; ci = 0; setTimeout(tick, 350);
            }
        } else if(phase === 1){
            phase = 2; ci = 0; setTimeout(tick, 100);
        } else {
            if(ci <= line2.length){
                render(line1, line2.slice(0, ci), true);
                ci++;
                setTimeout(tick, 60);
            } else {
                render(line1, line2, true);
            }
        }
    }
    setTimeout(tick, 800);
})();

/* ---- ANIMACIÓN DE MÉTRICAS ---- */
(function(){
    const metrics = [
        { el: document.getElementById('c1'), target: 248, suffix: '' },
        { el: document.getElementById('c2'), target: 37,  suffix: '' },
        { el: document.getElementById('c3'), target: 91,  suffix: '%' }
    ];
    function easeOut(v){ return 1 - Math.pow(1 - v, 3); }
    function animateMetric(el, target, suffix, dur){
        let start = null;
        function step(ts){
            if(!start) start = ts;
            const p = Math.min((ts - start) / dur, 1);
            el.textContent = Math.round(target * easeOut(p)) + suffix;
            if(p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    metrics.forEach((m, i) => {
        if(!m.el) return;
        m.el.textContent = '0' + m.suffix;
        setTimeout(() => animateMetric(m.el, m.target, m.suffix, 1100), 180 + i * 120);
    });
})();
</script>
</body>
</html>
