<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'HomeSupply.co' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute -left-40 top-20 h-96 w-96 rounded-full bg-emerald-500/20 blur-[100px] pointer-events-none"></div>
    <div class="absolute -right-40 bottom-20 h-96 w-96 rounded-full bg-cyan-500/20 blur-[100px] pointer-events-none"></div>

    <main class="relative z-10 w-full max-w-md px-4 py-8">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="h-12 w-12 rounded-2xl bg-emerald-500/80 shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">HomeSupply<span class="text-emerald-400">.co</span></h1>
            </div>
            <p class="text-sm text-slate-400">Marketplace Perlengkapan Rumah Zaman Now</p>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-rose-200">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="glass-card rounded-3xl p-8 border border-white/5 shadow-2xl">
            @yield('content')
        </div>
    </main>
</body>
</html>