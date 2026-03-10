<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TempMail - Temporary Email</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .container {
            text-align: center;
            max-width: 560px;
            width: 100%;
            padding: 0 20px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #f8fafc;
        }

        .subtitle {
            color: #94a3b8;
            margin-bottom: 40px;
            font-size: 1rem;
        }

        .email-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 20px;
        }

        .email-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .email-address {
            font-size: 1.5rem;
            font-weight: 600;
            color: #38bdf8;
            word-break: break-all;
            padding: 16px;
            background: #0f172a;
            border-radius: 8px;
            border: 1px solid #334155;
            cursor: pointer;
            transition: border-color 0.2s;
            position: relative;
        }

        .email-address:hover {
            border-color: #38bdf8;
        }

        .copy-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 8px;
        }

        .copy-hint.copied {
            color: #4ade80;
        }

        .email-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            font-size: 0.8rem;
            color: #64748b;
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #334155;
        }

        .btn-secondary:hover {
            border-color: #4ade80;
            color: #4ade80;
        }

        .timer {
            color: #fb923c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>TempMail</h1>
        <p class="subtitle">Your temporary email address is ready</p>

        <div class="email-card">
            <div class="email-label">Your Temporary Email</div>
            <div class="email-address" id="emailAddress" onclick="copyEmail()">
                {{ $email->email }}
            </div>
            <p class="copy-hint" id="copyHint">Click to copy</p>

            <div class="email-meta">
                <span>Created: {{ $email->created_at->format('H:i, M d') }}</span>
                <span class="timer">Expires: {{ $email->expires_at->diffForHumans() }}</span>
            </div>
        </div>

        <div class="actions">
            <form action="{{ route('email.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Generate New Email</button>
            </form>
            <button class="btn btn-secondary" onclick="copyEmail()">Copy Email</button>
        </div>
    </div>

    <script>
        function copyEmail() {
            const email = document.getElementById('emailAddress').textContent.trim();
            navigator.clipboard.writeText(email).then(() => {
                const hint = document.getElementById('copyHint');
                hint.textContent = 'Copied!';
                hint.classList.add('copied');
                setTimeout(() => {
                    hint.textContent = 'Click to copy';
                    hint.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>
