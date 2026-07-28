<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — ServiceKU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .glow-orb {
            background: radial-gradient(circle at center, rgba(99, 102, 241, 0.15), transparent 70%);
        }
        .glow-orb-2 {
            background: radial-gradient(circle at center, rgba(139, 92, 246, 0.1), transparent 70%);
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-20px) scale(1.02); }
        }
        .animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
    </style>
</head>
<body class="antialiased">

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- ===== LEFT: BRANDING PANEL (dark) ===== -->
        <div class="relative flex-1 bg-slate-900 flex items-center justify-center px-8 py-12 lg:py-0 overflow-hidden min-h-[50vh] lg:min-h-screen">
            <!-- Glow orbs -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full glow-orb animate-float-slow"></div>
                <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full glow-orb-2 animate-float-slow" style="animation-delay: -4s;"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-radial from-indigo-500/5 to-transparent rounded-full"></div>
                <!-- Subtle grid pattern -->
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width%3D%2260%22 height%3D%2260%22 viewBox%3D%220 0 60 60%22 xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg fill%3D%22none%22 fill-rule%3D%22evenodd%22%3E%3Cg fill%3D%22%23ffffff%22 fill-opacity%3D%220.4%22%3E%3Cpath d%3D%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E');"></div>
            </div>

            <div class="relative z-10 max-w-md text-center lg:text-left">
                <!-- Logo -->
                <div class="flex items-center justify-center lg:justify-start gap-3 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-premium">SK</div>
                    <span class="text-2xl font-bold text-white">ServiceKU</span>
                </div>

                <!-- Value Proposition -->
                <h2 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                    Streamline Your<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Repair Center</span><br>
                    Operations
                </h2>
                <p class="mt-4 text-base text-slate-400 leading-relaxed max-w-sm mx-auto lg:mx-0">
                    The all-in-one SaaS platform for electronics repair shops. Manage tickets, inventory, POS, and multi-branch operations from a single dashboard.
                </p>

                <!-- Testimonial mini -->
                <div class="mt-10 p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm inline-block mx-auto lg:mx-0">
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-1.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-500 border-2 border-slate-800 flex items-center justify-center text-white text-[10px] font-bold">RW</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 border-2 border-slate-800 flex items-center justify-center text-white text-[10px] font-bold">DP</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 border-2 border-slate-800 flex items-center justify-center text-white text-[10px] font-bold">MS</div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">500+ Repair Shops</p>
                            <p class="text-xs text-slate-500">trust ServiceKU daily</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== RIGHT: FORM PANEL (white) ===== -->
        <div class="flex-1 bg-white flex items-center justify-center px-6 py-12 lg:py-0">
            <div class="w-full max-w-sm">

                <!-- Form Header -->
                <div class="text-center lg:text-left mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">Welcome Back</h1>
                    <p class="text-sm text-slate-500 mt-1">Sign in to your repair center dashboard</p>
                </div>

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            autofocus
                            placeholder="you@repairshop.com"
                            class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-white/80 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm transition-all duration-200 focus:border-slate-800 focus:ring-2 focus:ring-slate-800/20"
                        />
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="block w-full px-4 py-3 rounded-xl border border-slate-200 bg-white/80 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm transition-all duration-200 focus:border-slate-800 focus:ring-2 focus:ring-slate-800/20"
                        />
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me + Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded-md border-slate-300 text-slate-800 focus:ring-slate-800 transition-all"
                            />
                            <span class="text-sm text-slate-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-gradient-to-r from-slate-800 to-slate-700 text-white text-sm font-bold hover:from-slate-900 hover:to-slate-800 hover:shadow-soft-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 shadow-soft"
                    >
                        Sign In
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-slate-400">or continue with</span>
                    </div>
                </div>

                <!-- Social Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <button class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </button>
                    <button class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                        GitHub
                    </button>
                </div>

                <!-- Register Link -->
                <p class="text-center text-sm text-slate-500 mt-8">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                        Create one
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>