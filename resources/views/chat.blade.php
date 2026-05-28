<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Planning Bot: Busch School of Business</title>

    <link rel="stylesheet" href="/fonts/fonts.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --cua-blue:      #0a3255;
            --cua-blue-mid:  #1a4a6e;
            --cua-red:       #b21f2c;
            --cua-red-dark:  #8c1420;
            --cua-gold:      #b18f50;
            --sandstone:     #f7f3ed;
            --limestone:     #efebe9;
            --border:        #e2ddd8;
            --border-light:  #ede9e4;
            --text-muted:    #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--limestone);
            color: #1a1a1a;
        }

        /* ── Message entry animation ───────────────────────── */
        @keyframes msgIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .msg-in { animation: msgIn 0.18s ease-out forwards; }

        /* ── Rendered HTML in AI bubbles ───────────────────── */
        .html-msg {
            font-family: 'Crimson Text', Georgia, serif;
            font-size: 1.075rem;
            line-height: 1.85;
            color: #1f2937;
        }
        .html-msg p { margin-bottom: 0.7rem; }
        .html-msg p:last-child { margin-bottom: 0; }
        .html-msg ul { margin: 0.5rem 0 0.7rem 1.35rem; list-style: disc; }
        .html-msg ul li { margin-bottom: 0.4rem; }
        .html-msg a { color: var(--cua-blue); text-decoration: underline; text-underline-offset: 2px; }
        .html-msg a:hover { color: var(--cua-red); }

        /* ── Scrollbar ─────────────────────────────────────── */
        .chat-scroll::-webkit-scrollbar { width: 4px; }
        .chat-scroll::-webkit-scrollbar-track { background: transparent; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #cec9c3; border-radius: 4px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: #b8b3ad; }

        /* ── Typing dots ───────────────────────────────────── */
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: .35; }
            30%            { transform: translateY(-5px); opacity: 1; }
        }
        .typing-dot { animation: typingBounce 1.4s ease-in-out infinite; }
        .typing-dot:nth-child(2) { animation-delay: .18s; }
        .typing-dot:nth-child(3) { animation-delay: .36s; }

        /* ── Sidebar nav buttons ───────────────────────────── */
        .qs-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            text-align: left;
            padding: 9px 14px 9px 16px;
            font-size: 13px;
            font-family: 'Roboto', sans-serif;
            color: #4b5563;
            background: transparent;
            border: none;
            border-left: 3px solid transparent;
            cursor: pointer;
            transition: background 0.12s, border-color 0.12s, color 0.12s;
            line-height: 1.45;
        }
        .qs-icon    { color: #b8b3ad; flex-shrink: 0; transition: color 0.12s; }
        .qs-chevron {
            color: #d4cec8;
            flex-shrink: 0;
            margin-left: auto;
            transition: color 0.12s, transform 0.12s;
        }
        .qs-btn:hover {
            background: var(--cua-blue);
            border-left-color: var(--cua-blue);
            color: #fff;
        }
        .qs-btn:hover .qs-icon    { color: rgba(255,255,255,0.85); }
        .qs-btn:hover .qs-chevron { color: rgba(255,255,255,0.6); transform: translateX(2px); }
        .qs-btn:active {
            background: rgba(178, 31, 44, 0.06);
            border-left-color: var(--cua-red);
            color: var(--cua-red);
        }

        /* ── New chat button ───────────────────────────────── */
        .new-chat-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 9px 16px;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 12.5px;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: white;
            background: #B41100;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.12s;
        }
        .new-chat-btn:hover { background: #8C0D00; }

        /* ── Compose ring ──────────────────────────────────── */
        .compose-ring {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            background: #fff;
            border: 1.5px solid #d4cec8;
            border-radius: 14px;
            padding: 4px 4px 4px 2px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .compose-ring:focus-within {
            border-color: var(--cua-blue);
            box-shadow: 0 0 0 3px rgba(10, 50, 85, 0.1);
        }
        .compose-ring textarea {
            flex: 1;
            border: none;
            background: transparent;
            outline: none;
            resize: none;
            font-family: 'Roboto', sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: #1a1a1a;
            padding: 9px 8px 9px 12px;
            min-height: 42px;
            max-height: 140px;
            field-sizing: content;
        }
        .compose-ring textarea::placeholder { color: #a89f97; }
        .compose-ring textarea:disabled { opacity: 0.5; }

        /* ── Attach button ─────────────────────────────────── */
        .attach-btn {
            align-self: flex-end;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #a89f97;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.12s, color 0.12s;
        }
        .attach-btn:hover:not(:disabled) {
            background: rgba(10, 50, 85, 0.07);
            color: var(--cua-blue);
        }
        .attach-btn:disabled { opacity: 0.38; cursor: not-allowed; }

        /* ── Send button ───────────────────────────────────── */
        .send-btn {
            align-self: flex-end;
            height: 38px;
            padding: 0 20px;
            background: var(--cua-red);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.12s, transform 0.1s;
        }
        .send-btn:hover:not(:disabled) { background: var(--cua-red-dark); }
        .send-btn:active:not(:disabled) { transform: scale(0.965); }
        .send-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        /* ── Mobile chips ──────────────────────────────────── */
        .chip {
            flex-shrink: 0;
            font-size: 12.5px;
            font-family: 'Roboto', sans-serif;
            color: #4b5563;
            background: #fff;
            border: 1px solid var(--border);
            padding: 5px 13px;
            border-radius: 20px;
            white-space: nowrap;
            cursor: pointer;
            transition: background 0.12s, color 0.12s, border-color 0.12s;
        }
        .chip:hover {
            background: var(--cua-blue);
            border-color: var(--cua-blue);
            color: #fff;
        }

        /* ── Keyboard hint ─────────────────────────────────── */
        .kbd {
            display: inline-block;
            font-size: 10px;
            background: #f3f0ec;
            border: 1px solid #d4cec8;
            border-bottom-width: 2px;
            border-radius: 3px;
            padding: 1px 4px;
            line-height: 1.4;
            color: #7c7169;
        }

        /* ── Hide Alpine-controlled elements before init ───── */
        [x-cloak] { display: none !important; }

        /* ── Profile update suggestion banner ──────────────── */
        .profile-update-banner {
            background: #fffbea;
            border: 1.5px solid #C9A84C;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 13.5px;
            font-family: 'Roboto', sans-serif;
            color: #1a1a1a;
        }
        .profile-update-banner .pub-text { flex: 1; min-width: 200px; }
        .pub-accept {
            background: #003366;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            transition: background 0.15s;
        }
        .pub-accept:hover { background: #0a3255; }
        .pub-dismiss {
            background: transparent;
            color: #666;
            border: 1px solid #ccc;
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
        }

        /* ── Semester prompt banner ─────────────────────────── */
        .semester-banner {
            background: linear-gradient(90deg, #C9A84C 0%, #b89438 100%);
            color: #1a1a1a;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13.5px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            flex-wrap: wrap;
            cursor: pointer;
        }
        .semester-banner:hover { filter: brightness(0.96); }
        .semester-banner-dismiss {
            margin-left: auto;
            background: rgba(0,0,0,0.12);
            border: none;
            border-radius: 4px;
            padding: 3px 8px;
            cursor: pointer;
            font-size: 13px;
            color: #1a1a1a;
        }

        /* ── Success toast ──────────────────────────────────── */
        .success-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #003366;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
            z-index: 9999;
        }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

{{-- ── HEADER ──────────────────────────────────────────────── --}}
<header class="shrink-0" style="background:var(--cua-blue); box-shadow:0 2px 12px rgba(0,0,0,0.22);">
    <div class="flex items-center justify-between px-5 h-[62px]">

        <a href="{{ route('chat') }}" class="flex items-center gap-3.5 no-underline">
            <img src="/images/busch_logo_white.png" alt="The Busch School of Business at The Catholic University of America" class="h-9 w-auto">
            <div class="hidden sm:block leading-none">
                <p class="font-oswald font-semibold uppercase tracking-wide text-[16px] text-white leading-none"
                   style="letter-spacing:0.04em;">Course Planning Bot</p>
                <p class="text-[10px] mt-1 tracking-wider uppercase font-light"
                   style="color:rgba(255,255,255,0.45);">
                    Busch School of Business &middot; CUA
                </p>
            </div>
        </a>

        <div class="flex items-center gap-2">
            <span class="hidden sm:block text-[13px] px-2" style="color:#fff;">
                {{ Auth::user()->name }}
            </span>
            <a href="{{ route('profile.edit') }}"
               class="hidden sm:block text-[13px] px-3 py-1.5 rounded transition-colors"
               style="color:#fff;"
               onmouseover="this.style.background='rgba(255,255,255,0.1)';"
               onmouseout="this.style.background='transparent';">
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="font-oswald font-semibold uppercase tracking-wide text-[12px] text-white px-4 py-1.5 rounded transition-colors"
                        style="border:1px solid rgba(255,255,255,0.6); letter-spacing:0.07em;"
                        onmouseover="this.style.borderColor='#fff'; this.style.background='rgba(255,255,255,0.1)';"
                        onmouseout="this.style.borderColor='rgba(255,255,255,0.6)'; this.style.background='transparent';">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
    {{-- Gold accent line --}}
    <div class="h-[2px]" style="background:linear-gradient(to right, var(--cua-gold) 0%, rgba(177,143,80,0.2) 60%, transparent 100%);"></div>
</header>

{{-- ── MAIN LAYOUT ─────────────────────────────────────────── --}}
<div class="flex flex-1 overflow-hidden">

    {{-- ── SIDEBAR ──────────────────────────────────────────── --}}
    <aside x-data
           class="hidden md:flex flex-col shrink-0 border-r"
           style="width:252px; background:#fff; border-color:#e5e0d8;">

        {{-- New conversation --}}
        <div class="px-4 py-4 border-b" style="border-color:var(--border);">
            <button class="new-chat-btn" @click="$dispatch('new-chat')">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Conversation
            </button>
        </div>

        {{-- Section label --}}
        <div class="px-5 pt-4 pb-1.5">
            <p class="font-oswald font-semibold uppercase text-[10px] tracking-[0.18em]"
               style="color:var(--cua-blue); opacity:0.5;">Quick Start</p>
        </div>

        {{-- Nav buttons --}}
        <nav class="flex-1 overflow-y-auto py-1">
            @php
            $navItems = [
                [
                    'label' => 'Build my 4-year plan',
                    'path'  => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z',
                ],
                [
                    'label' => 'Check my prerequisites',
                    'path'  => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label' => 'Fastest path to graduation',
                    'path'  => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                ],
                [
                    'label' => 'Which electives can I take now',
                    'path'  => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
                ],
                [
                    'label' => 'Explain my degree requirements',
                    'path'  => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
                ],
                [
                    'label' => 'Forms & requests',
                    'path'  => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                ],
            ];
            @endphp

            @foreach($navItems as $item)
            <button class="qs-btn"
                    @click="$dispatch('quick-send', { message: '{{ $item['label'] }}' })">
                <svg class="qs-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['path'] }}"/>
                </svg>
                <span class="flex-1 min-w-0">{{ $item['label'] }}</span>
                <svg class="qs-chevron w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </button>
            @endforeach

            <div style="height:1px; background:#e8e2db; margin:8px 16px;"></div>
            <a href="{{ route('profile.academic') }}" style="display:flex;align-items:center;gap:10px;width:100%;padding:9px 14px 9px 16px;font-size:13px;font-family:'Roboto',sans-serif;color:#4b5563;border-left:3px solid transparent;text-decoration:none;transition:background 0.12s,border-color 0.12s,color 0.12s;" onmouseover="this.style.background='#0a3255';this.style.borderLeftColor='#0a3255';this.style.color='#fff';" onmouseout="this.style.background='transparent';this.style.borderLeftColor='transparent';this.style.color='#4b5563';">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" style="color:#b8b3ad;flex-shrink:0;">
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                </svg>
                <span>Academic Profile</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="color:#d4cec8;margin-left:auto;">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </a>
        </nav>

        {{-- Sidebar footer --}}
        <div class="px-5 py-4 border-t" style="border-color:var(--border);">
            <p class="text-[11.5px] leading-relaxed" style="color:#a89f97;">
                Questions? <a href="mailto:busch-academic-services@cua.edu"
                   class="font-medium hover:underline"
                   style="color:var(--cua-blue);">Academic Services</a>
            </p>
        </div>
    </aside>

    {{-- ── CHAT PANEL ──────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden"
         x-data="chatApp()"
         @quick-send.window="quickSend($event.detail.message)"
         @new-chat.window="newChat()">

        {{-- Semester prompt banner (Phase 7) --}}
        <div x-cloak x-show="semesterBanner"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="semester-banner shrink-0"
             @click="sendSemesterPrompt()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink:0;">
                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
            </svg>
            <span>A new semester has started. Tell me what courses you completed and I will update your academic profile.</span>
            <button class="semester-banner-dismiss"
                    @click.stop="dismissSemesterBanner()"
                    aria-label="Dismiss">✕</button>
        </div>

        {{-- Profile update suggestion banner (Phase 6) --}}
        <div x-cloak x-show="profileUpdate !== null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="shrink-0 px-4 py-2.5" style="background:#fef9ec; border-bottom:1px solid #e8d98a;">
            <div class="profile-update-banner max-w-3xl mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#C9A84C" viewBox="0 0 16 16" style="flex-shrink:0;">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
                <div class="pub-text" x-text="profileUpdateMessage()"></div>
                <button class="pub-accept" @click="acceptProfileUpdate()">Accept</button>
                <button class="pub-dismiss" @click="profileUpdate = null">Dismiss</button>
            </div>
        </div>

        {{-- Success toast --}}
        <div x-cloak x-show="successToast"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="success-toast">
            Profile updated successfully!
        </div>

        {{-- Messages scroll area --}}
        <div class="flex-1 overflow-y-auto chat-scroll"
             style="background:var(--limestone); padding: 1.75rem clamp(1rem, 4vw, 3.5rem);"
             x-ref="messages">

            <div class="max-w-3xl mx-auto space-y-5">

                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">

                        {{-- AI bubble --}}
                        <template x-if="msg.role === 'assistant'">
                            <div class="msg-in"
                                 style="max-width:82%; background:#fff;
                                        border:1px solid #e5e0d8;
                                        border-radius:16px;
                                        padding:15px 20px;
                                        box-shadow:0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.06);">
                                {{-- Forms list: hardcoded Blade HTML, never user-generated content --}}
                                <template x-if="msg.type === 'forms-list'">
                                    <div class="html-msg">
                                        <p>Here are the most common Busch School forms and requests. Click any item to open the form directly:</p>
                                        <ul>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSd_IFxpMN3DHd2sMTxxlBo5rWtKciue-zsSieDG7yLQfbi45Q/viewform" target="_blank" rel="noopener noreferrer">Internship for Credit Application</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSeKVk2VXIJ6K4jQQEYEpElk1TJE2JmtDDPwpdVytCx78DlSPA/viewform" target="_blank" rel="noopener noreferrer">Late Registration / Special Academic Requests</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSfQEBsGO1QhNz8OnLyi4b9KBnkHC2Qd3BuO3ChcLWZW_NqThQ/viewform" target="_blank" rel="noopener noreferrer">Class Registration Help</a></li>
                                            <li><a href="https://business.catholic.edu/_media/busch-incomplete-request-form-dec2023.pdf" target="_blank" rel="noopener noreferrer">Request for Incomplete</a></li>
                                            <li><a href="https://enrollment-services.catholic.edu/forms/registration-status-change-form.pdf" target="_blank" rel="noopener noreferrer">Pass/Fail Registration Change</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSfp12NrXfiqwtqO7j5vjpEZa9yJav_fChqBQ5Th-2rgtpNdbQ/viewform" target="_blank" rel="noopener noreferrer">Declare / Change Specialization</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSfSqf3vwGbOxgrYr1GOw2UG984c4vFbshK16_vjnaJLza3IPA/viewform" target="_blank" rel="noopener noreferrer">Add / Remove a Minor</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSc0hLnRtHFFh9U1U5LIC1RuGXiPdsU5J70iVfrt0_38kQHuPQ/viewform" target="_blank" rel="noopener noreferrer">Class Substitution Request</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSd7rYkqMw0cpLKuTid62XmO_1Z7xC-wtM7vpxnql0DD6A0_Sw/viewform" target="_blank" rel="noopener noreferrer">Expected Graduation Term Change</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSdRzq3MtT-CVee6N42IFIGBzkdG4IKxm6eSyiEHMEa-OLT1xQ/viewform" target="_blank" rel="noopener noreferrer">Career Discernment Exemption (Transfer Students)</a></li>
                                            <li><a href="https://docs.google.com/forms/d/e/1FAIpQLScNx2uBAqVdcg8invm7rsXt2uzU2TxIV9vOu2Bk67_dhc33Cw/viewform" target="_blank" rel="noopener noreferrer">Special Permission to Over-Elect</a></li>
                                            <li><a href="https://enrollment-services.catholic.edu/forms/double-major-application.pdf" target="_blank" rel="noopener noreferrer">Double Major Application</a></li>
                                        </ul>
                                        <p>For anything not listed here, email <a href="mailto:busch-academic-services@cua.edu">busch-academic-services@cua.edu</a></p>
                                    </div>
                                </template>
                                {{-- All other AI responses are plain text — never rendered as HTML --}}
                                <template x-if="msg.type !== 'forms-list'">
                                    <p style="font-family:'Crimson Text',Georgia,serif; font-size:1.075rem;
                                              line-height:1.85; color:#1f2937; white-space:pre-wrap;
                                              font-feature-settings:'kern' 1,'liga' 1;"
                                       x-text="stripMarkdown(msg.content)"></p>
                                </template>
                            </div>
                        </template>

                        {{-- User bubble --}}
                        <template x-if="msg.role === 'user'">
                            <div class="msg-in"
                                 style="max-width:75%; background:var(--cua-blue);
                                        border-radius:14px 14px 3px 14px;
                                        padding:13px 18px;
                                        box-shadow:0 2px 10px rgba(10,50,85,0.25);">
                                <p style="font-family:'Crimson Text',Georgia,serif; font-size:1.05rem;
                                          line-height:1.75; color:#fff; white-space:pre-wrap;"
                                   x-text="msg.content"></p>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Typing indicator --}}
                <div x-cloak x-show="loading"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="flex justify-start">
                    <div style="background:#fff; border:1px solid #e5e0d8;
                                border-radius:16px;
                                padding:13px 18px;
                                box-shadow:0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.06);">
                        <div class="flex gap-1.5 items-center" style="height:16px;">
                            <span class="typing-dot w-2 h-2 rounded-full" style="background:#c8c2bb;"></span>
                            <span class="typing-dot w-2 h-2 rounded-full" style="background:#c8c2bb;"></span>
                            <span class="typing-dot w-2 h-2 rounded-full" style="background:#c8c2bb;"></span>
                        </div>
                    </div>
                </div>

                {{-- Error banner --}}
                <div x-cloak x-show="error"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="flex justify-center">
                    <div class="flex items-center gap-2 text-sm px-4 py-2.5 rounded-lg"
                         style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239;">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span x-text="error"></span>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Compose area ──────────────────────────────── --}}
        <div class="shrink-0 border-t" style="background:#fff; border-color:var(--border);">
            <div style="padding:10px clamp(1rem, 4vw, 3.5rem) 10px;">

                {{-- Mobile chips --}}
                <div class="md:hidden flex gap-2 overflow-x-auto pb-2.5" style="scrollbar-width:none;">
                    @foreach([
                        ['c' => '4-Year Plan',    'f' => 'Build my 4-year plan'],
                        ['c' => 'Prerequisites',  'f' => 'Check my prerequisites'],
                        ['c' => 'Fastest Path',   'f' => 'Fastest path to graduation'],
                        ['c' => 'Electives',      'f' => 'Which electives can I take now'],
                        ['c' => 'Degree Reqs',    'f' => 'Explain my degree requirements'],
                        ['c' => 'Forms',          'f' => 'Forms & requests'],
                    ] as $item)
                    <button class="chip"
                            @click="$dispatch('quick-send', { message: '{{ $item['f'] }}' })">
                        {{ $item['c'] }}
                    </button>
                    @endforeach
                </div>

                {{-- Compose ring --}}
                <form @submit.prevent="send()">
                    <div class="compose-ring">
                        <textarea
                            x-model="input"
                            @keydown.enter.exact.prevent="send()"
                            @keydown.enter.shift.exact="/* allow newline */"
                            x-ref="input"
                            :disabled="loading"
                            placeholder="Ask about your degree, courses, specializations, or graduation requirements…"
                        ></textarea>

                        <button type="submit" class="send-btn"
                                :disabled="loading || !input.trim()">
                            Send
                        </button>
                    </div>

                    <p class="hidden sm:block text-[11px] mt-1.5 pl-1 select-none" style="color:#a89f97;">
                        <span class="kbd">Enter</span> to send &nbsp;&middot;&nbsp;
                        <span class="kbd">Shift+Enter</span> for new line
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── FOOTER ───────────────────────────────────────────────── --}}
<div class="shrink-0 py-2 px-6 text-center" style="background:#071e38;">
    <p class="text-xs font-light tracking-wide" style="color:rgba(255,255,255,0.8);">
        AI guidance is informational. Always verify with a
        <a href="https://business.catholic.edu/academics/academic-services/index.html"
           target="_blank"
           class="underline underline-offset-2 transition-colors hover:text-white"
           style="color:rgba(255,255,255,0.8);">human advisor</a>
        before finalizing your schedule or degree plan.
    </p>
</div>

<script>
function chatApp() {
    return {
        messages: [{
            role: 'assistant',
            content: "Hello! I'm the Busch School Course Planning Bot.\n\nI can help you with degree requirements, course sequencing, specializations, minors, prerequisites, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nTo get started, tell me your degree program, catalog year, and where you are in your studies, or choose a topic from the sidebar.",
        }],
        input: '',
        loading: false,
        error: null,
        profileUpdate: null,
        semesterBanner: false,
        successToast: false,

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            const autoMsg = urlParams.get('msg');
            if (autoMsg) {
                this.input = autoMsg;
                this.$nextTick(() => this.send());
            }

            this.semesterBanner = @json($showSemesterBanner);
        },

        sendSemesterPrompt() {
            this.semesterBanner = false;
            this.dismissSemesterBanner();
            this.input = 'A new semester has started. Tell me what courses you completed and I will update your academic profile.';
            this.send();
        },

        dismissSemesterBanner() {
            this.semesterBanner = false;
            fetch('/api/profile/dismiss-prompt', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            }).catch(() => {});
        },

        async acceptProfileUpdate() {
            if (!this.profileUpdate) return;
            const update = this.profileUpdate;
            this.profileUpdate = null;

            try {
                await fetch('/api/profile/suggest-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(update),
                });
                this.successToast = true;
                setTimeout(() => { this.successToast = false; }, 3000);
            } catch {
                this.error = 'Could not update profile. Please try again.';
            }
        },

        profileUpdateMessage() {
            if (!this.profileUpdate) return '';
            const p = this.profileUpdate;
            const statusLabel = (p.status || '').replace(/_/g, ' ');
            let msg = `Your advisor suggests marking ${p.course_code} as ${statusLabel}`;
            const details = [];
            if (p.grade) details.push('Grade: ' + p.grade);
            if (p.semester) details.push(p.semester);
            if (details.length) msg += ' (' + details.join(', ') + ')';
            return msg + '. Accept or Dismiss?';
        },

        extractProfileUpdate(text) {
            const match = text.match(/\[PROFILE_UPDATE:\s*(\{[^}]+\})\]/s);
            if (!match) return { text, update: null };
            try {
                const update = JSON.parse(match[1]);
                const cleanText = text.replace(match[0], '').trim();
                return { text: cleanText, update };
            } catch {
                return { text: text.replace(match[0], '').trim(), update: null };
            }
        },

        newChat() {
            this.messages = [{
                role: 'assistant',
                content: "Hello! I'm the Busch School Course Planning Bot.\n\nI can help you with degree requirements, course sequencing, specializations, minors, prerequisites, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nTo get started, tell me your degree program, catalog year, and where you are in your studies, or choose a topic from the sidebar.",
            }];
            this.input = '';
            this.error = null;
            this.loading = false;
            this.profileUpdate = null;
            this.$nextTick(() => this.scrollToBottom());
        },

        stripMarkdown(text) {
            return text
                .replace(/\*\*(.*?)\*\*/gs, '$1')
                .replace(/\*(.*?)\*/gs, '$1');
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.error = null;
            this.input = '';

            const history = this.messages.map(m => ({ role: m.role, content: m.content }));
            this.messages.push({ role: 'user', content: text });
            if (!this.loading) {
                this.loading = true;
                this.scrollToBottom();
            }

            try {
                const res = await fetch('/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: text, history }),
                });

                const data = await res.json();

                if (!res.ok) {
                    this.error = data.error ?? 'Something went wrong. Please try again.';
                } else {
                    const { text, update } = this.extractProfileUpdate(data.message);
                    this.messages.push({ role: 'assistant', content: text });
                    if (update) {
                        this.profileUpdate = update;
                    }
                }
            } catch {
                this.error = 'Could not reach the server. Check your connection and try again.';
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        quickSend(prompt) {
            if (prompt === 'Forms & requests') {
                this.messages.push({ role: 'user', content: prompt });
                this.messages.push({ role: 'assistant', type: 'forms-list', content: '' });
                this.scrollToBottom();
                return;
            }
            this.input = prompt;
            this.send();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    };
}
</script>

</body>
</html>
