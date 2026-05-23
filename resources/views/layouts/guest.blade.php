<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-100">
        <div class="water-shell min-h-screen">
            <div class="water-orb left-[-4rem] top-16 h-56 w-56 bg-cyan-400/30"></div>
            <div class="water-orb water-orb--soft right-[-5rem] top-36 h-72 w-72 bg-sky-500/20 animation-delay-400"></div>
            <div class="water-orb bottom-[-4rem] left-1/4 h-64 w-64 bg-blue-400/20 animation-delay-600"></div>

            <div class="relative mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="water-panel grid w-full gap-8 rounded-[2rem] p-4 shadow-2xl lg:grid-cols-[1.05fr_0.95fr] lg:p-6">
                    <div class="relative overflow-hidden rounded-[1.75rem] bg-[linear-gradient(180deg,rgba(8,15,31,0.72),rgba(3,7,18,0.92))] px-6 py-10 sm:px-8 sm:py-12 lg:px-10">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(56,189,248,0.15),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.12),transparent_34%)]"></div>
                        <div class="relative max-w-xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.45em] text-sky-300">Water Quality</p>
                            <h1 class="mt-5 text-4xl font-semibold leading-tight text-white sm:text-5xl">
                                Una interfaz azul, limpia y más humana para trabajar con agua.
                            </h1>
                            <p class="mt-5 max-w-lg text-sm leading-7 text-slate-300 sm:text-base">
                                Accede al sistema con una atmósfera más clara y acuosa, pensada para datasets, análisis y futuras tareas de aprendizaje automático sin caer en el típico look oscuro de laboratorio.
                            </p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="water-card rounded-2xl p-4 transition duration-300 hover:-translate-y-0.5">
                                    <p class="text-sm font-semibold text-sky-300">Flujo</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-300">Transiciones suaves y una navegación más amable.</p>
                                </div>
                                <div class="water-card rounded-2xl p-4 transition duration-300 hover:-translate-y-0.5">
                                    <p class="text-sm font-semibold text-sky-300">Agua</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-300">Azules profundos con brillo controlado.</p>
                                </div>
                                <div class="water-card rounded-2xl p-4 transition duration-300 hover:-translate-y-0.5">
                                    <p class="text-sm font-semibold text-sky-300">ML</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-300">Listo para datasets y análisis técnico.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative rounded-[1.75rem] p-3">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
