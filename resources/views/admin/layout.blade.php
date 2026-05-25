<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Busch School Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --navy: #0a3255;
            --navy-dark: #071e38;
            --red: #B41100;
            --red-dark: #8C0D00;
            --gold: #C9A84C;
            --border: #e2ddd8;
            --sand: #f7f3ed;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f0f0ee; color: #1a1a1a; margin: 0; }

        .admin-shell { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: var(--navy-dark);
            display: flex; flex-direction: column;
            position: sticky; top: 0; height: 100vh;
        }
        .sidebar-logo {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-logo img { height: 36px; }
        .sidebar-label {
            font-family: 'Oswald', sans-serif;
            font-size: 10px; font-weight: 600;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            padding: 1rem 1.25rem 0.4rem;
        }
        .sidebar-nav { flex: 1; padding: 0.25rem 0; overflow-y: auto; }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 0.65rem 1.25rem;
            font-size: 13.5px; font-weight: 500;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.06); }
        .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--gold);
        }
        .nav-link svg { width: 16px; height: 16px; flex-shrink: 0; opacity: 0.7; }
        .nav-link.active svg { opacity: 1; }
        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 12px; color: rgba(255,255,255,0.4);
        }
        .sidebar-footer a { color: rgba(255,255,255,0.55); text-decoration: none; }
        .sidebar-footer a:hover { color: #fff; }

        /* Main area */
        .main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar {
            background: #fff; border-bottom: 1px solid var(--border);
            padding: 0.85rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 10;
        }
        .topbar-title {
            font-family: 'Oswald', sans-serif;
            font-size: 18px; font-weight: 600;
            color: var(--navy); letter-spacing: 0.02em;
        }
        .topbar-user {
            font-size: 13px; color: #6b7280;
            display: flex; align-items: center; gap: 10px;
        }
        .topbar-user form button {
            background: none; border: none; cursor: pointer;
            font-size: 13px; color: var(--red); font-family: 'Roboto', sans-serif;
            padding: 0; text-decoration: underline;
        }
        .content { padding: 1.75rem; flex: 1; }

        /* Cards */
        .stat-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .stat-label { font-size: 11px; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase; color: #6b7280; margin-bottom: 0.35rem; }
        .stat-value { font-family: 'Oswald', sans-serif; font-size: 30px; font-weight: 700; color: var(--navy); line-height: 1; }

        .card { background: #fff; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin-bottom: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .card-header { background: var(--sand); border-bottom: 1px solid var(--border); padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-family: 'Oswald', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: var(--navy); }
        .card-body { padding: 1.25rem; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #6b7280; padding: 0.6rem 1rem; border-bottom: 1px solid var(--border); background: #faf8f6; text-align: left; white-space: nowrap; }
        td { padding: 0.65rem 1rem; font-size: 13.5px; color: #374151; border-bottom: 1px solid #f0ede9; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #faf8f6; }
        .tr-link { cursor: pointer; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-admin { background: #fef3c7; color: #92400e; }
        .badge-dean  { background: #dbeafe; color: #1e40af; }
        .badge-student { background: #f3f4f6; color: #374151; }
        .badge-ok { background: #d1fae5; color: #065f46; }
        .badge-warn { background: #fef3c7; color: #92400e; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; font-family: 'Roboto', sans-serif; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
        .btn-primary { background: var(--red); color: #fff; }
        .btn-primary:hover { background: var(--red-dark); color: #fff; }
        .btn-secondary { background: transparent; color: var(--navy); border: 1.5px solid var(--navy); }
        .btn-secondary:hover { background: var(--navy); color: #fff; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }

        /* Forms */
        .field-label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 0.3rem; }
        .text-input, textarea { width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #d1d5db; border-radius: 6px; font-size: 13.5px; font-family: 'Roboto', sans-serif; transition: border-color 0.15s; background: #fff; }
        .text-input:focus, textarea:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 2px rgba(10,50,85,0.1); }
        select.text-input { cursor: pointer; }

        /* Alerts */
        .alert-success { background: #d1fae5; border-left: 4px solid #10b981; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: 13.5px; color: #065f46; }
        .alert-warning { background: #fffbea; border-left: 4px solid var(--gold); padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: 13.5px; color: #6b4d00; }

        /* Pagination */
        .pagination { display: flex; gap: 4px; margin-top: 1rem; }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 4px; font-size: 13px; text-decoration: none; color: #374151; border: 1px solid var(--border); }
        .pagination a:hover { background: var(--sand); }
        .pagination .active span { background: var(--navy); color: #fff; border-color: var(--navy); }
        .pagination .disabled span { opacity: 0.4; }
    </style>
</head>
<body>
<div class="admin-shell">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="/images/busch_logo_white.png" alt="Busch School">
        </div>

        <div class="sidebar-label">Main Menu</div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.students') }}" class="nav-link {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Students
            </a>
            <a href="{{ route('admin.requirements') }}" class="nav-link {{ request()->routeIs('admin.requirements') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Requirements
            </a>
            <a href="{{ route('admin.system-prompt') }}" class="nav-link {{ request()->routeIs('admin.system-prompt') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                System Prompt
            </a>
            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Users
            </a>
            <a href="{{ route('admin.stats') }}" class="nav-link {{ request()->routeIs('admin.stats') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Statistics
            </a>
        </nav>

        <div class="sidebar-footer">
            <div style="margin-bottom:0.4rem;">{{ auth()->user()->name }}</div>
            <a href="{{ route('chat') }}">← Back to App</a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-area">
        <header class="topbar">
            <span class="topbar-title">@yield('heading', 'Admin Dashboard')</span>
            <div class="topbar-user">
                <span>{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign Out</button>
                </form>
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>

</div>
</body>
</html>
