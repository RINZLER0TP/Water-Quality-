<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=Space+Grotesk:wght@500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-900 water-app-bg overflow-x-hidden">
        <canvas id="particles" style="position:fixed;inset:0;z-index:0;pointer-events:none;opacity:0.6;"></canvas>
        <div class="min-h-screen water-app-bg overflow-x-hidden relative">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-sky-100 bg-white/75 backdrop-blur-xl shadow-[0_8px_32px_rgba(14,165,233,.08)]">
                    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>

        <script>
        /* ---- PARTÍCULAS GLOBALES ---- */
        (function(){
            const c = document.getElementById('particles');
            if(!c) return;
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
        </script>
    </body>
</html>
