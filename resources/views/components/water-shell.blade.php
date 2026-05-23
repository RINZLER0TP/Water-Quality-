<div class="py-6 sm:py-8 relative z-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row w-full rounded-[28px] overflow-hidden shadow-[0_12px_60px_rgba(14,165,233,0.125)] bg-white border border-slate-200" style="animation: cardIn 0.7s 0.1s cubic-bezier(0.22, 0.68, 0, 1.15) forwards; opacity: 0; transform: translateY(28px);">
            
            {{-- Columna Izquierda --}}
            <div class="lg:w-[320px] shrink-0 bg-[linear-gradient(150deg,#075985_0%,#0ea5e9_52%,#06b6d4_100%)] p-6 lg:p-8 flex flex-col gap-8 relative overflow-hidden text-white">
                <div class="absolute w-[340px] h-[340px] rounded-full bg-white/5 -top-20 -right-20 pointer-events-none"></div>
                <div class="absolute w-[200px] h-[200px] rounded-full bg-white/5 -bottom-12 -left-12 pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col h-full justify-between gap-10">
                    @if(isset($aside))
                        {{ $aside }}
                    @else
                        <div class="brand">
                            <div class="brand-ico">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c0 0-7 6.3-7 11.5a7 7 0 0 0 14 0C19 8.3 12 2 12 2z"/></svg>
                            </div>
                            <span class="brand-txt">Water Quality</span>
                        </div>

                        <div class="hero">
                            <p class="hero-eyebrow">Plataforma IA · Monitoreo</p>
                            <div class="hero-h" id="typingTarget">Análisis inteligente<br><b>del agua potable.</b></div>
                            <p class="hero-p">Sube datasets CSV, entrena modelos con Weka y predice la potabilidad con precisión científica.</p>
                        </div>

                        <div class="chips">
                            <div class="chip"><div class="chip-v" id="c1">248</div><div class="chip-l">Datasets</div></div>
                            <div class="chip"><div class="chip-v" id="c2">37</div><div class="chip-l">Modelos IA</div></div>
                            <div class="chip"><div class="chip-v" id="c3">91%</div><div class="chip-l">Accuracy</div></div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Columna Derecha --}}
            <div class="flex-1 p-6 sm:p-8 lg:p-10 bg-white overflow-y-auto relative">
                {{ $slot }}
            </div>

        </div>
    </div>
</div>

<script>
/* ---- EFECTO DE ESCRITURA Y MÉTRICAS (SOLO SI EXISTEN) ---- */
(function(){
    // Typing
    const el = document.getElementById('typingTarget');
    if(el) {
        const line1 = 'Panel de control';
        const line2 = 'Operaciones';
        let phase = 0, ci = 0;
        function render(t1, t2, showCursor){
            const cur = showCursor ? '<span class="cursor"></span>' : '';
            if(phase < 2) el.innerHTML = t1 + cur;
            else el.innerHTML = t1 + '<br><b>' + t2 + '</b>' + cur;
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
    }

    // Metrics
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
