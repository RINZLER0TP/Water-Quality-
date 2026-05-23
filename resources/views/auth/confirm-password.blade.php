<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Water Quality — Confirmar contraseña</title>
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
            <p class="hero-p">Confirma tu contraseña antes de continuar con esta acción sensible en el sistema.</p>
        </div>

        <div class="chips">
            <div class="chip"><div class="chip-v" id="c1">248</div><div class="chip-l">Datasets</div></div>
            <div class="chip"><div class="chip-v" id="c2">37</div><div class="chip-l">Modelos IA</div></div>
            <div class="chip"><div class="chip-v" id="c3">91%</div><div class="chip-l">Accuracy</div></div>
        </div>
    </div>

    <div class="right">
        <div class="fhead">
            <p class="ftag">Zona segura</p>
            <h1 class="ftitle">Confirmar contraseña</h1>
            <p class="fsub">Por seguridad, requerimos confirmar tu identidad.</p>
        </div>

        @if ($errors->any())
            <div class="alert" style="display:block;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            @csrf

            <div class="field" style="animation: up .45s .60s ease forwards;">
                <label for="password">Contraseña</label>
                <div class="iw">
                    <svg class="iico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required autofocus>
                    <button class="eye" id="eyeBtn" type="button" aria-label="Ver contraseña">
                        <svg id="eyeIco" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="btn-wrap" style="margin-top: 1.5rem; animation: up .45s .75s ease forwards;">
                <button class="btn" type="submit">
                    <span class="shine"></span>
                    <span>Confirmar identidad</span>
                </button>
            </div>
        </form>

        <div class="statusbar" style="opacity:0; animation: up .4s .9s ease forwards;"><span class="dot"></span> Sistema operativo · v2.4.1</div>
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
    const line1 = 'Verifica tu identidad';
    const line2 = 'antes de seguir.';
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

/* ---- OJO — mostrar/ocultar contraseña ---- */
document.getElementById('eyeBtn').addEventListener('click', function(){
    const pw  = document.getElementById('password');
    const ico = document.getElementById('eyeIco');
    if(pw.type === 'password'){
        pw.type = 'text';
        ico.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        pw.type = 'password';
        ico.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>';
    }
});
</script>
</body>
</html>
