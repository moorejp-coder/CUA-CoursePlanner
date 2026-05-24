<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Planning Bot — Busch School of Business</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

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

        /* ── File attachment tag ───────────────────────────── */
        .file-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(10, 50, 85, 0.06);
            border: 1px solid rgba(10, 50, 85, 0.18);
            color: var(--cua-blue);
            font-size: 12.5px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            padding: 3px 10px 3px 8px;
            border-radius: 20px;
        }
        .file-tag-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 240px;
        }
        .file-tag-remove {
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
            line-height: 1;
            padding: 0;
            flex-shrink: 0;
            transition: color 0.12s;
        }
        .file-tag-remove:hover { color: var(--cua-red); }

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
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

{{-- ── HEADER ──────────────────────────────────────────────── --}}
<header class="shrink-0" style="background:var(--cua-blue); box-shadow:0 2px 12px rgba(0,0,0,0.22);">
    <div class="flex items-center justify-between px-5 h-[62px]">

        <a href="{{ route('chat') }}" class="flex items-center gap-3.5 no-underline">
            <img src="/images/busch_logo.jpg" alt="Busch School of Business" class="h-9 w-auto" style="mix-blend-mode: multiply;">
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
            <span class="hidden sm:block text-[13px] px-2" style="color:rgba(255,255,255,0.5);">
                {{ Auth::user()->name }}
            </span>
            <a href="{{ route('profile.edit') }}"
               class="hidden sm:block text-[13px] px-3 py-1.5 rounded transition-colors"
               style="color:rgba(255,255,255,0.6);"
               onmouseover="this.style.color='#fff'; this.style.background='rgba(255,255,255,0.1)';"
               onmouseout="this.style.color='rgba(255,255,255,0.6)'; this.style.background='transparent';">
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="font-oswald font-semibold uppercase tracking-wide text-[12px] text-white px-4 py-1.5 rounded transition-colors"
                        style="border:1px solid rgba(255,255,255,0.2); letter-spacing:0.07em;"
                        onmouseover="this.style.borderColor='rgba(255,255,255,0.5)'; this.style.background='rgba(255,255,255,0.1)';"
                        onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.background='transparent';">
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
                    'label' => 'Explain my degree requirements',
                    'path'  => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
                ],
                [
                    'label' => 'Plan next semester',
                    'path'  => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z',
                ],
                [
                    'label' => 'Explore specializations',
                    'path'  => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z',
                ],
                [
                    'label' => 'Explore minors',
                    'path'  => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
                ],
                [
                    'label' => 'Check graduation progress',
                    'path'  => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
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
                                <template x-if="msg.html">
                                    <div class="html-msg" x-html="msg.content"></div>
                                </template>
                                <template x-if="!msg.html">
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
                <div x-show="loading"
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
                <div x-show="error"
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
                        ['c' => 'Degree reqs',    'f' => 'Explain my degree requirements'],
                        ['c' => 'Plan semester',   'f' => 'Plan next semester'],
                        ['c' => 'Specializations', 'f' => 'Explore specializations'],
                        ['c' => 'Minors',          'f' => 'Explore minors'],
                        ['c' => 'Graduation',      'f' => 'Check graduation progress'],
                        ['c' => 'Forms',           'f' => 'Forms & requests'],
                    ] as $item)
                    <button class="chip"
                            @click="$dispatch('quick-send', { message: '{{ $item['f'] }}' })">
                        {{ $item['c'] }}
                    </button>
                    @endforeach
                </div>

                {{-- Hidden file input --}}
                <input type="file" x-ref="fileInput" accept=".csv,.pdf" class="hidden"
                       @change="handleFileSelect($event)">

                {{-- File tag --}}
                <div x-show="fileName"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mb-2">
                    <span class="file-tag">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span class="file-tag-name" x-text="fileName"></span>
                        <button type="button" class="file-tag-remove" @click="removeFile()" aria-label="Remove file">&times;</button>
                    </span>
                </div>

                {{-- Compose ring --}}
                <form @submit.prevent="send()">
                    <div class="compose-ring">
                        <button type="button" class="attach-btn"
                                @click="$refs.fileInput.click()"
                                :disabled="loading"
                                title="Attach APW (.csv) or graduation report (.pdf)">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </button>

                        <textarea
                            x-model="input"
                            @keydown.enter.exact.prevent="send()"
                            @keydown.enter.shift.exact="/* allow newline */"
                            x-ref="input"
                            :disabled="loading"
                            placeholder="Ask about your degree, courses, specializations, or graduation requirements…"
                        ></textarea>

                        <button type="submit" class="send-btn"
                                :disabled="loading || (!input.trim() && !file)">
                            Send
                        </button>
                    </div>

                    <p class="hidden sm:block text-[11px] mt-1.5 pl-1 select-none" style="color:#a89f97;">
                        <span class="kbd">Enter</span> to send &nbsp;&middot;&nbsp;
                        <span class="kbd">Shift+Enter</span> for new line &nbsp;&middot;&nbsp;
                        <span class="kbd">📎</span> to attach APW (.csv) or graduation report (.pdf)
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── FOOTER ───────────────────────────────────────────────── --}}
<div class="shrink-0 py-2 px-6 text-center" style="background:#071e38;">
    <p class="text-xs font-light tracking-wide" style="color:rgba(255,255,255,0.5);">
        AI guidance is informational — always verify with a
        <a href="https://business.catholic.edu/academics/academic-services/index.html"
           target="_blank"
           class="underline underline-offset-2 transition-colors hover:text-white"
           style="color:rgba(255,255,255,0.5);">human advisor</a>
        before finalizing your schedule or degree plan.
    </p>
</div>

<script>
function chatApp() {
    return {
        messages: [],
        input: '',
        loading: false,
        error: null,
        file: null,
        fileName: null,

        init() {
            this.messages.push({
                role: 'assistant',
                content: "Hello! I'm the Busch School Course Planning Bot.\n\nI can help you with degree requirements, course sequencing, specializations, minors, prerequisites, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nTo get started, tell me your degree program, catalog year, and where you are in your studies — or choose a topic from the sidebar.",
            });
        },

        newChat() {
            this.messages = [];
            this.input = '';
            this.error = null;
            this.loading = false;
            this.file = null;
            this.fileName = null;
            this.init();
            this.$nextTick(() => this.scrollToBottom());
        },

        stripMarkdown(text) {
            return text
                .replace(/\*\*(.*?)\*\*/gs, '$1')
                .replace(/\*(.*?)\*/gs, '$1');
        },

        handleFileSelect(event) {
            const f = event.target.files[0];
            if (!f) return;
            this.file = f;
            this.fileName = f.name;
            event.target.value = '';
        },

        removeFile() {
            this.file = null;
            this.fileName = null;
        },

        async send() {
            const text = this.input.trim();
            if ((!text && !this.file) || this.loading) return;

            this.error = null;
            this.input = '';

            let messageText = text;

            if (this.file) {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const form = new FormData();
                form.append('file', this.file);
                form.append('_token', csrf);

                this.loading = true;
                this.scrollToBottom();

                let extracted;
                try {
                    const uploadRes = await fetch('/api/upload', { method: 'POST', body: form });
                    const uploadData = await uploadRes.json();
                    if (!uploadRes.ok) {
                        this.error = uploadData.error ?? 'File upload failed. Please try again.';
                        this.loading = false;
                        return;
                    }
                    extracted = uploadData.text;
                } catch {
                    this.error = 'Could not upload the file. Check your connection and try again.';
                    this.loading = false;
                    return;
                }

                if (extracted.startsWith('APW:')) {
                    messageText = messageText
                        ? extracted + '\n\nAdditional student question: ' + messageText
                        : extracted;
                } else {
                    if (!messageText) {
                        messageText = 'Please analyze my uploaded document and tell me where I stand on my degree requirements.';
                    }
                    messageText = `The student has uploaded their Academic Planning Worksheet or graduation progress report. Here is the content:\n\n${extracted}\n\nStudent question: ${messageText}`;
                }

                this.removeFile();
            }

            const history = this.messages.map(m => ({ role: m.role, content: m.content }));
            const displayText = text || 'Please analyze my uploaded document and tell me where I stand on my degree requirements.';
            this.messages.push({ role: 'user', content: displayText });
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
                    body: JSON.stringify({ message: messageText, history }),
                });

                const data = await res.json();

                if (!res.ok) {
                    this.error = data.error ?? 'Something went wrong. Please try again.';
                } else {
                    this.messages.push({ role: 'assistant', content: data.message });
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
                this.messages.push({ role: 'assistant', html: true, content: `<p>Here are the most common Busch School forms and requests. Click any item to open the form directly:</p>
<ul>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSd_IFxpMN3DHd2sMTxxlBo5rWtKciue-zsSieDG7yLQfbi45Q/viewform" target="_blank">Internship for Credit Application</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSeKVk2VXIJ6K4jQQEYEpElk1TJE2JmtDDPwpdVytCx78DlSPA/viewform" target="_blank">Late Registration / Special Academic Requests</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSfQEBsGO1QhNz8OnLyi4b9KBnkHC2Qd3BuO3ChcLWZW_NqThQ/viewform" target="_blank">Class Registration Help</a></li>
  <li><a href="https://business.catholic.edu/_media/busch-incomplete-request-form-dec2023.pdf" target="_blank">Request for Incomplete</a></li>
  <li><a href="https://enrollment-services.catholic.edu/forms/registration-status-change-form.pdf" target="_blank">Pass/Fail Registration Change</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSfp12NrXfiqwtqO7j5vjpEZa9yJav_fChqBQ5Th-2rgtpNdbQ/viewform" target="_blank">Declare / Change Specialization</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSfSqf3vwGbOxgrYr1GOw2UG984c4vFbshK16_vjnaJLza3IPA/viewform" target="_blank">Add / Remove a Minor</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSc0hLnRtHFFh9U1U5LIC1RuGXiPdsU5J70iVfrt0_38kQHuPQ/viewform" target="_blank">Class Substitution Request</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSd7rYkqMw0cpLKuTid62XmO_1Z7xC-wtM7vpxnql0DD6A0_Sw/viewform" target="_blank">Expected Graduation Term Change</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSdRzq3MtT-CVee6N42IFIGBzkdG4IKxm6eSyiEHMEa-OLT1xQ/viewform" target="_blank">Career Discernment Exemption (Transfer Students)</a></li>
  <li><a href="https://docs.google.com/forms/d/e/1FAIpQLScNx2uBAqVdcg8invm7rsXt2uzU2TxIV9vOu2Bk67_dhc33Cw/viewform" target="_blank">Special Permission to Over-Elect</a></li>
  <li><a href="https://enrollment-services.catholic.edu/forms/double-major-application.pdf" target="_blank">Double Major Application</a></li>
</ul>
<p>For anything not listed here, email <a href="mailto:busch-academic-services@cua.edu">busch-academic-services@cua.edu</a></p>` });
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
