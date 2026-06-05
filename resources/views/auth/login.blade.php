<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — SendGrid Inbound</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:           #0b0d1a;
            --card:         #111425;
            --input-bg:     #181c30;
            --border:       #1e2240;
            --border-focus: #7c5ce8;
            --txt:          #e6e9f8;
            --txt-sub:      #7a81a8;
            --txt-muted:    #404668;
            --purple:       #7c5ce8;
            --pink:         #d63fc8;
            --green:        #22c55e;
            --red:          #ef4444;
            --grad:         linear-gradient(135deg, #7c5ce8, #d63fc8);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--txt);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        /* ── Animated background mesh ── */
        .bg-mesh {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .35;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .blob-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #7c5ce8, transparent 70%);
            top: -150px; left: -150px;
            animation-delay: 0s;
        }
        .blob-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #d63fc8, transparent 70%);
            bottom: -100px; right: -100px;
            animation-delay: -5s;
        }
        .blob-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, #1e3a8a, transparent 70%);
            top: 50%; left: 60%;
            animation-delay: -8s;
        }
        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.08); }
        }

        /* subtle dot grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Card ── */
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(17,20,37,.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px;
            padding: 44px 40px;
            box-shadow:
                0 0 0 1px rgba(124,92,232,.12),
                0 32px 64px rgba(0,0,0,.6),
                inset 0 1px 0 rgba(255,255,255,.06);
            animation: rise .55s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(32px) scale(.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);   }
        }

        /* top glow line */
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 30px; right: 30px;
            height: 3px;
            background: var(--grad);
            border-radius: 0 0 2px 2px;
            opacity: .7;
        }

        /* ── Logo / header ── */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
            text-decoration: none;
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: var(--grad);
            border-radius: 11px;
            display: grid;
            place-items: center;
            box-shadow: 0 4px 16px rgba(124,92,232,.4);
        }
        .logo-icon svg { width: 20px; height: 20px; }
        .logo-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--txt);
            letter-spacing: -.2px;
        }

        .heading {
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: -.5px;
            line-height: 1.2;
            margin-bottom: 6px;
        }
        .subheading {
            font-size: .88rem;
            color: var(--txt-sub);
            margin-bottom: 28px;
        }

        /* ── Alerts ── */
        .alert {
            border-radius: 10px;
            padding: 11px 14px;
            font-size: .83rem;
            margin-bottom: 20px;
        }
        .alert-error {
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.25);
            color: #fca5a5;
        }
        .alert-error ul { padding-left: 16px; }
        .alert-success {
            background: rgba(34,197,94,.08);
            border: 1px solid rgba(34,197,94,.25);
            color: #86efac;
        }

        /* ── Form ── */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--txt-sub);
            margin-bottom: 7px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%; transform: translateY(-50%);
            width: 15px; height: 15px;
            color: var(--txt-muted);
            pointer-events: none;
            transition: color .2s;
        }
        .input-wrap:focus-within .input-icon { color: var(--purple); }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--txt);
            font-family: inherit;
            font-size: .9rem;
            padding: 11px 40px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        input::placeholder { color: var(--txt-muted); }
        input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(124,92,232,.14);
        }
        input.is-invalid { border-color: var(--red); }

        .field-error {
            font-size: .77rem;
            color: #fca5a5;
            margin-top: 5px;
        }

        .toggle-pw {
            position: absolute;
            right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            color: var(--txt-muted);
            display: grid; place-items: center;
            padding: 4px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--txt-sub); }
        .toggle-pw svg { width: 15px; height: 15px; }

        /* ── Row: remember + forgot ── */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .84rem;
            color: var(--txt-sub);
            cursor: pointer;
            user-select: none;
        }
        .check-label input[type="checkbox"] {
            appearance: none;
            width: 15px; height: 15px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: background .2s, border-color .2s;
        }
        .check-label input:checked {
            background: var(--purple);
            border-color: var(--purple);
        }
        .check-label input:checked::after {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='white'%3E%3Cpath d='M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.75.75 0 0 1 1.06-1.06L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0z'/%3E%3C/svg%3E") center / 10px no-repeat;
        }

        .forgot-link {
            font-size: .84rem;
            color: var(--purple);
            text-decoration: none;
            transition: color .2s;
        }
        .forgot-link:hover { color: var(--pink); }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--grad);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: .95rem;
            font-weight: 600;
            letter-spacing: .02em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 20px rgba(124,92,232,.35);
            transition: opacity .2s, transform .15s, box-shadow .2s;
            position: relative;
            overflow: hidden;
        }
        .btn-submit:hover {
            opacity: .9;
            box-shadow: 0 6px 28px rgba(124,92,232,.5);
        }
        .btn-submit:active { transform: scale(.985); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

        .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Footer ── */
        .card-footer {
            margin-top: 22px;
            text-align: center;
            font-size: .84rem;
            color: var(--txt-sub);
        }
        .card-footer a {
            color: var(--purple);
            text-decoration: none;
            font-weight: 500;
            transition: color .2s;
        }
        .card-footer a:hover { color: var(--pink); }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 0;
            font-size: .75rem;
            color: var(--txt-muted);
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* live badge bottom-right */
        .live {
            position: fixed;
            bottom: 20px; right: 20px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 7px;
            background: rgba(17,20,37,.9);
            border: 1px solid rgba(34,197,94,.25);
            border-radius: 20px;
            padding: 5px 13px;
            font-size: .72rem;
            font-weight: 600;
            color: var(--green);
            backdrop-filter: blur(12px);
        }
          .sidebar-brand {
             padding: 24px 11px 24px 0px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-brand i {
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5rem;
        }

        .live-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 1.8s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:.3; transform:scale(.7); }
        }

        /* version bottom-left */
        .version {
            position: fixed;
            bottom: 20px; left: 20px;
            z-index: 2;
            font-family: 'JetBrains Mono', monospace;
            font-size: .68rem;
            color: var(--txt-muted);
        }

        @media(max-width:480px){
            .card { padding: 32px 24px; }
        }
    </style>
</head>
<body>

{{-- Animated blobs --}}
<div class="bg-mesh">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="card">


     <div class="sidebar-brand">
            <i class="fa-solid fa-satellite-dish"></i>
            <span>SendGrid Inbound</span>
        </div>

    <h1 class="heading">Sign in</h1>
    <p class="subheading">Access your inbound email dashboard</p>

    {{-- Status message --}}
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label for="email">Email address</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input
                    id="email" name="email" type="email"
                    class="@error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required autofocus autocomplete="username"
                >
            </div>
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                    id="password" name="password" type="password"
                    class="@error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    required autocomplete="current-password"
                >
                <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Show/hide password">
                    <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="form-options">
            <label class="check-label">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-submit" id="loginBtn">
            <span id="btnText">Sign In</span>
            <span class="spinner" id="btnSpinner"></span>
        </button>

        @if (Route::has('register'))
            <div class="card-footer">
                Don't have an account? <a href="{{ route('register') }}">Create one</a>
            </div>
        @endif
    </form>
</div>



<script>
    function togglePw() {
        const inp = document.getElementById('password');
        const ico = document.getElementById('eyeIcon');
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        ico.innerHTML = show
            ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
            : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        document.getElementById('btnText').style.display = 'none';
        document.getElementById('btnSpinner').style.display = 'block';
        btn.disabled = true;
    });
</script>
</body>
</html>