<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TempMail - @yield('title', 'Temporary Email')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .navbar {
            width: 100%;
            padding: 16px 24px;
            background: #1e293b;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: #f8fafc;
            text-decoration: none;
        }

        .navbar-email {
            font-size: 0.85rem;
            color: #38bdf8;
            cursor: pointer;
        }

        .main {
            flex: 1;
            width: 100%;
            max-width: 720px;
            padding: 32px 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }

        .btn-secondary {
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #334155;
        }
        .btn-secondary:hover { border-color: #38bdf8; color: #38bdf8; }

        .btn-sm { padding: 6px 14px; font-size: 0.8rem; }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .text-muted { color: #64748b; }
        .text-sky { color: #38bdf8; }
        .text-orange { color: #fb923c; }
        .text-sm { font-size: 0.8rem; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }

        @yield('styles')
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">TempMail</a>
        @hasSection('email')
            <span class="navbar-email" onclick="navigator.clipboard.writeText('{{ $email->email }}')">
                {{ $email->email }}
            </span>
        @endif
    </nav>

    <div class="main">
        @yield('content')
    </div>
</body>
</html>
