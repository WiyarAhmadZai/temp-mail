<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TempMail - Free temporary email address. Receive emails instantly, no registration required.">
    <title>TempMail - @yield('title', 'Temporary Email')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: { 900: '#0b1120', 800: '#0f172a', 700: '#1e293b', 600: '#334155' },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-dot': 'pulseDot 2s ease-in-out infinite',
                        'skeleton': 'skeleton 1.5s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: { from: { opacity: 0 }, to: { opacity: 1 } },
                        slideUp: { from: { opacity: 0, transform: 'translateY(8px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
                        pulseDot: { '0%, 100%': { opacity: 1 }, '50%': { opacity: 0.3 } },
                        skeleton: { '0%': { backgroundPosition: '-200% 0' }, '100%': { backgroundPosition: '200% 0' } },
                    }
                }
            }
        }
    </script>
    <style>
        .skeleton-line {
            background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
            background-size: 200% 100%;
            animation: skeleton 1.5s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full bg-dark-800 text-slate-200 antialiased">
    <!-- Toast notification -->
    <div id="toast" class="fixed top-5 right-5 z-50 hidden">
        <div class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg animate-slide-up">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span id="toastText">Copied!</span>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 z-40 border-b border-dark-600 bg-dark-700/95 backdrop-blur">
        <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-white hover:text-sky-400 transition">
                <svg class="h-6 w-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                TempMail
            </a>
            @hasSection('email')
            <div class="flex items-center gap-3">
                <button onclick="copyEmail('{{ $email->email }}')" class="hidden sm:flex items-center gap-2 rounded-lg border border-dark-600 bg-dark-800 px-3 py-1.5 text-sm text-sky-400 hover:border-sky-500 transition">
                    <span class="max-w-[200px] truncate">{{ $email->email }}</span>
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </div>
            @endif
        </div>
    </nav>

    <main class="mx-auto w-full max-w-5xl px-4 py-6">
        @yield('content')
    </main>

    <script>
        function copyEmail(email) {
            navigator.clipboard.writeText(email).then(() => showToast('Email copied to clipboard!'));
        }

        function showToast(text) {
            const toast = document.getElementById('toast');
            document.getElementById('toastText').textContent = text;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 2500);
        }
    </script>
    @yield('scripts')
</body>
</html>
