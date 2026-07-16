<!DOCTYPE html>
<html lang="en" class="h-full" x-data="theme()" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farmmantra</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --bg: #0f172a;
            --card: rgba(255,255,255,0.05);
            --card-border: rgba(255,255,255,0.1);
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --input-bg: rgba(255,255,255,0.06);
            --input-border: rgba(255,255,255,0.12);
            --input-focus: #6366f1;
        }
        html.dark { --bg: #0f172a; }
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); min-height: 100vh; min-height: 100dvh; overflow: auto; }

        .login-bg {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 25%, #312e81 50%, #1e1b4b 75%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3;
            animation: orbFloat 20s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: #6366f1; top: -10%; left: -5%; animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: #8b5cf6; bottom: -10%; right: -5%; animation-delay: -5s; }
        .orb-3 { width: 250px; height: 250px; background: #06b6d4; top: 50%; left: 60%; animation-delay: -10s; }
        .orb-4 { width: 200px; height: 200px; background: #10b981; top: 20%; right: 30%; animation-delay: -15s; }
        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -40px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(40px, 30px) scale(1.05); }
        }

        .grid-bg {
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        .login-container {
            position: relative; z-index: 10; display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 1.5rem;
        }

        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.05);
            width: 100%; max-width: 440px;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0; transform: translateY(30px) scale(0.95);
        }
        @keyframes cardAppear {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .glass-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px; padding: 14px 16px 14px 48px;
            color: #f1f5f9; font-size: 0.9rem; width: 100%;
            transition: all 0.3s ease; outline: none;
        }
        .glass-input::placeholder { color: rgba(255,255,255,0.3); }
        .glass-input:focus {
            border-color: #6366f1;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15), 0 0 20px rgba(99,102,241,0.1);
        }

        .glass-btn {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none; border-radius: 14px; padding: 14px 24px;
            color: white; font-size: 0.95rem; font-weight: 600; width: 100%;
            cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .glass-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        }
        .glass-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.4);
        }
        .glass-btn:active { transform: translateY(0); }

        .input-group { position: relative; }
        .input-icon {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.3); z-index: 5; transition: color 0.3s;
        }
        .input-group:focus-within .input-icon { color: #6366f1; }

        .brand-logo {
            width: 72px; height: 72px; border-radius: 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 30px rgba(99,102,241,0.4);
            animation: logoPulse 3s ease-in-out infinite;
        }
        @keyframes logoPulse {
            0%, 100% { box-shadow: 0 8px 30px rgba(99,102,241,0.4); }
            50% { box-shadow: 0 8px 40px rgba(99,102,241,0.6); }
        }

        .feature-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 50px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            font-size: 0.75rem; color: rgba(255,255,255,0.6);
            animation: pillFade 0.6s ease forwards; opacity: 0;
        }
        .feature-pill:nth-child(1) { animation-delay: 0.3s; }
        .feature-pill:nth-child(2) { animation-delay: 0.45s; }
        .feature-pill:nth-child(3) { animation-delay: 0.6s; }
        @keyframes pillFade { to { opacity: 1; } }

        .error-shake { animation: shake 0.5s ease; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }

        .password-toggle {
            position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.3); cursor: pointer; z-index: 5; transition: color 0.3s;
        }
        .password-toggle:hover { color: rgba(255,255,255,0.6); }

        .particles { position: absolute; inset: 0; overflow: hidden; z-index: 1; }
        .particle {
            position: absolute; width: 3px; height: 3px; border-radius: 50%;
            background: rgba(99,102,241,0.5);
            animation: particleFloat linear infinite;
        }
        @keyframes particleFloat {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="login-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="orb orb-4"></div>
        <div class="grid-bg"></div>
    </div>

    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="glass-card p-8 sm:p-10">
            {{-- Logo & Brand --}}
            <div class="text-center mb-8">
                <div class="brand-logo mx-auto mb-5">
                    <span class="text-white font-black text-2xl tracking-tight">FM</span>
                </div>
                <h1 class="text-2xl font-bold text-white mb-1">Farmmantra</h1>
                <p class="text-sm" style="color:rgba(255,255,255,0.4)">Agro Chemicals Limited</p>
            </div>

            {{-- Error --}}
            @if($errors->any())
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { $el.classList.add('error-shake'); setTimeout(() => show = false, 3000) }, 100)"
                 class="mb-5 rounded-xl p-4 flex items-center gap-3"
                 style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2)">
                <i class="fas fa-exclamation-circle text-red-400"></i>
                <p class="text-sm text-red-300">{{ $errors->first() }}</p>
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('web.login.submit') }}" class="space-y-5">
                @csrf
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="glass-input" placeholder="Email address">
                </div>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" required
                        class="glass-input pr-12" placeholder="Password">
                    <span class="password-toggle" onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password'; this.querySelector('i').classList.toggle('fa-eye'); this.querySelector('i').classList.toggle('fa-eye-slash')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <button type="submit" class="glass-btn mt-2">
                    <span class="relative z-10 flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Sign In
                    </span>
                </button>
            </form>

            {{-- Feature pills --}}
            <div class="flex flex-wrap gap-2 justify-center mt-6">
                <span class="feature-pill"><i class="fas fa-shield-halved text-indigo-400"></i> Secure Login</span>
                <span class="feature-pill"><i class="fas fa-bolt text-amber-400"></i> Real-time</span>
                <span class="feature-pill"><i class="fas fa-mobile-screen text-cyan-400"></i> Mobile Ready</span>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-6 pt-5" style="border-top:1px solid rgba(255,255,255,0.06)">
                <p class="text-xs" style="color:rgba(255,255,255,0.25)">Franchise Distribution Management System</p>
                <p class="text-xs mt-1" style="color:rgba(255,255,255,0.15)">&copy; {{ date('Y') }} Farmmantra Agro Chemicals Ltd</p>
            </div>
        </div>
    </div>

    <script>
        function theme() {
            return {
                dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggle() { this.dark = !this.dark; localStorage.setItem('theme', this.dark ? 'dark' : 'light'); }
            }
        }

        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = (8 + Math.random() * 12) + 's';
            p.style.animationDelay = Math.random() * 10 + 's';
            p.style.width = p.style.height = (2 + Math.random() * 3) + 'px';
            p.style.background = ['rgba(99,102,241,0.5)', 'rgba(139,92,246,0.5)', 'rgba(6,182,212,0.5)', 'rgba(16,185,129,0.5)'][Math.floor(Math.random() * 4)];
            particlesContainer.appendChild(p);
        }
    </script>
</body>
</html>
