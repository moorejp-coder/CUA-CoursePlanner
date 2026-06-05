<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}Busch School Course Planning Bot</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="font-family:'Roboto',sans-serif; background:#efebe9; min-height:100vh; display:flex; flex-direction:column;">

    {{-- CUA Header --}}
    <header style="background:#0a3255; box-shadow:0 2px 12px rgba(0,0,0,0.22); flex-shrink:0;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0 1.25rem; height:62px; max-width:1280px; margin:0 auto;">
            <a href="{{ route('chat') }}">
                <img src="/images/busch_logo_white.png" alt="Busch School of Business"
                     style="height:38px; object-fit:contain;">
            </a>
            <nav style="display:flex; align-items:center; gap:1.25rem;">
                <a href="{{ route('chat') }}"
                   style="color:rgba(255,255,255,0.75); font-size:13px; font-family:'Roboto',sans-serif; text-decoration:none; transition:color 0.15s;"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.75)'">Chat</a>
                <a href="{{ route('profile.academic.edit') }}"
                   style="color:rgba(255,255,255,0.75); font-size:13px; font-family:'Roboto',sans-serif; text-decoration:none; transition:color 0.15s;"
                   onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.75)'">Academic Profile</a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                            style="color:rgba(255,255,255,0.6); font-size:13px; font-family:'Roboto',sans-serif; background:none; border:1px solid rgba(255,255,255,0.3); border-radius:4px; padding:5px 12px; cursor:pointer; transition:all 0.15s;"
                            onmouseover="this.style.borderColor='rgba(255,255,255,0.7)';this.style.color='#fff';"
                            onmouseout="this.style.borderColor='rgba(255,255,255,0.3)';this.style.color='rgba(255,255,255,0.6)';">
                        Sign Out
                    </button>
                </form>
            </nav>
        </div>
        <div style="height:2px; background:linear-gradient(to right,#C9A84C 0%,rgba(201,168,76,0.2) 60%,transparent 100%);"></div>
    </header>

    {{-- Page Content --}}
    <main style="flex:1; padding:2.5rem 1rem;">
        <div style="max-width:720px; margin:0 auto;">
            @isset($header)
                <div style="margin-bottom:1.75rem;">
                    <h1 style="font-family:'Oswald',sans-serif; font-size:1.5rem; font-weight:700; color:#0a3255; text-transform:uppercase; letter-spacing:0.06em; margin:0 0 6px;">
                        {{ $header }}
                    </h1>
                    <div style="height:2px; width:36px; background:#b21f2c;"></div>
                </div>
            @endisset
            {{ $slot }}
        </div>
    </main>

</body>
</html>
