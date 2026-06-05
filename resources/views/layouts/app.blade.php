<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SendGrid Dashboard') - Email Receiving System</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        :root {
            --bg-primary: #0a0f1d;
            --bg-secondary: #111827;
            --bg-tertiary: #1f2937;
            --accent-primary: #6366f1; /* Premium Indigo */
            --accent-secondary: #ec4899; /* Vibrant Pink */
            --accent-glow: rgba(99, 102, 241, 0.15);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --glass-bg: rgba(17, 24, 39, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --sidebar-width: 260px;
            --status-success: #10b981;
            --status-warning: #f59e0b;
            --status-danger: #ef4444;
            --status-info: #3b82f6;
            --font-display: 'Outfit', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: var(--font-body);
            overflow-x: hidden;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        aside {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px;
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
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .sidebar-menu li a:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.03);
        }

        .sidebar-menu li.active a {
            color: #fff;
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15) 0%, rgba(236, 72, 153, 0.05) 100%);
            border-left: 3px solid var(--accent-primary);
        }

        .sidebar-menu li.active a i {
            color: var(--accent-primary);
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--glass-border);
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* Main Workspace */
        main {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 24px;
        }

        /* Top Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
        }

        .header-title h1 {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-title p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .badge-pulse {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--status-success);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-pulse span {
            width: 8px;
            height: 8px;
            background-color: var(--status-success);
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        /* Glass Cards */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.1);
        }

        /* Premium Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            border: 1px solid var(--glass-border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }
/* =========================
   Sidebar Bottom Section
========================= */

.sidebar-bottom {
    margin-top: auto;
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

/* Logout Button */

.sidebar-logout {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    padding: 13px 16px;

    border-radius: 14px;
    border: 1px solid rgba(239, 68, 68, 0.25);

    background: linear-gradient(
        135deg,
        rgba(239, 68, 68, 0.12) 0%,
        rgba(127, 29, 29, 0.10) 100%
    );

    color: #ff7b7b;

    font-size: 0.95rem;
    font-weight: 600;

    cursor: pointer;

    transition: all 0.3s ease;
}

/* Hover */

.sidebar-logout:hover {
    background: linear-gradient(
        135deg,
        rgba(239, 68, 68, 0.18) 0%,
        rgba(127, 29, 29, 0.18) 100%
    );

    border-color: rgba(239, 68, 68, 0.45);

    transform: translateY(-2px);

    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.15);
}

/* Icon */

.sidebar-logout i {
    font-size: 0.95rem;
}

/* Version Text */

.sidebar-version {
    margin-top: 18px;

    text-align: center;

    font-size: 12px;

    color: #6b7280;

    line-height: 1.7;
}
        /* Flex Utilities */
        .flex-row { display: flex; align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-12 { gap: 12px; }
        .gap-24 { gap: 24px; }
        .w-full { width: 100%; }

        /* Responsive Layout */
        @media (max-width: 1024px) {
            aside {
                width: 70px;
            }
            .sidebar-brand span, .sidebar-menu li a span, .sidebar-footer {
                display: none;
            }
            main {
                margin-left: 70px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside>
        <div class="sidebar-brand">
            <i class="fa-solid fa-satellite-dish"></i>
            <span>SendGrid Inbound</span>
        </div>
        <ul class="sidebar-menu">
            <li class="{{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="{{ str_starts_with(Route::currentRouteName(), 'emails') ? 'active' : '' }}">
                <a href="{{ route('emails.index') }}">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Inbox</span>
                </a>
            </li>
            <li class="{{ Route::currentRouteName() == 'analytics' ? 'active' : '' }}">
                <a href="{{ route('analytics') }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li class="{{ Route::currentRouteName() == 'logs' ? 'active' : '' }}">
                <a href="{{ route('logs') }}">
                    <i class="fa-solid fa-terminal"></i>
                    <span>Logs & Audits</span>
                </a>
            </li>
        </ul>


     <div class="sidebar-bottom">

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit" class="sidebar-logout">
            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>
        </button>
    </form>

    <div class="sidebar-version">
        <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</p>
        <p>PHP v{{ PHP_VERSION }}</p>
    </div>

</div>
    </aside>

    <!-- Main Workspace -->
    <main>
        <!-- Top Header -->
        <header>
            <div class="header-title">
                <h1>@yield('header-title', 'Dashboard')</h1>
                <p>@yield('header-subtitle', 'Real-time SendGrid Email Receiver & Analytics')</p>
            </div>
            <div class="header-actions">
                <div class="badge-pulse">
                    <span></span>
                    Live webhook active
                </div>
            </div>
        </header>

        <!-- Alert messages -->
        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--status-success); color: var(--status-success); padding: 12px 20px; border-radius: 12px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Content -->
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
