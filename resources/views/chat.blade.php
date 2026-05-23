<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Planning Bot — Busch School of Business</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Source+Sans+3:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --cua-blue: #004B9D;
            --cua-red:  #CC0000;
            --cua-dark: #1A1A1A;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Source Sans 3', sans-serif;
            background: #F5F5F5;
            color: var(--cua-dark);
        }

        .font-oswald { font-family: 'Oswald', sans-serif; }

        /* Scrollbar */
        .chat-scroll::-webkit-scrollbar { width: 5px; }
        .chat-scroll::-webkit-scrollbar-track { background: #f0f0f0; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #c8c8c8; border-radius: 3px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: #aaa; }

        /* Typing dots */
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: .35; }
            30%           { transform: translateY(-5px); opacity: 1; }
        }
        .typing-dot { animation: typingBounce 1.3s ease-in-out infinite; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }

        /* Textarea */
        textarea {
            field-sizing: content;
            min-height: 52px;
            max-height: 140px;
        }

        /* Sidebar nav buttons */
        .qs-btn {
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            padding: 12px 20px;
            font-size: 15px;
            font-family: 'Source Sans 3', sans-serif;
            color: #333;
            background: transparent;
            border: none;
            border-left: 3px solid transparent;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
            line-height: 1.4;
        }
        .qs-btn:hover {
            background: #EBF2FF;
            border-left-color: var(--cua-blue);
            color: var(--cua-blue);
        }
        .qs-btn:active {
            background: #FDECEA;
            border-left-color: var(--cua-red);
            color: var(--cua-red);
        }

        /* Mobile chips */
        .chip {
            flex-shrink: 0;
            font-size: 13px;
            font-family: 'Source Sans 3', sans-serif;
            color: #555;
            background: #fff;
            border: 1px solid #d8d8d8;
            padding: 5px 14px;
            border-radius: 20px;
            white-space: nowrap;
            cursor: pointer;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }
        .chip:hover {
            background: var(--cua-blue);
            border-color: var(--cua-blue);
            color: #fff;
        }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

{{-- ═══════════════════════════════
     TOP UTILITY BAR
═══════════════════════════════ --}}
<div style="background:var(--cua-blue);" class="shrink-0 py-1.5 px-5">
    <p class="text-white text-xs font-light tracking-wide">
        The Catholic University of America &nbsp;|&nbsp; Busch School of Business
    </p>
</div>

{{-- ═══════════════════════════════
     HEADER
═══════════════════════════════ --}}
<header class="bg-white shrink-0 border-b-[3px]" style="border-bottom-color:var(--cua-red);">
    <div class="flex items-center justify-between px-5 h-[68px]">

        {{-- Logo + title --}}
        <a href="{{ route('chat') }}" class="flex items-center gap-4 no-underline">
            <img src="/images/busch_logo.jpg"
                 alt="Busch School of Business"
                 class="h-11 w-auto">
            <div class="hidden sm:block">
                <p class="font-oswald font-bold uppercase tracking-wider text-xl leading-none"
                   style="color:var(--cua-blue);">Course Planning Bot</p>
                <p class="text-xs text-gray-500 mt-0.5 tracking-wide">Busch School of Business · CUA</p>
            </div>
        </a>

        {{-- User + actions --}}
        <div class="flex items-center gap-3">
            <span class="hidden sm:block text-sm text-gray-600">{{ Auth::user()->name }}</span>
            <a href="{{ route('profile.edit') }}"
               class="hidden sm:block text-sm text-gray-500 hover:text-gray-800 transition-colors px-2 py-1">
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="font-oswald font-semibold uppercase tracking-wide text-sm text-white px-4 py-2 transition-opacity hover:opacity-90"
                        style="background:var(--cua-red);">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ═══════════════════════════════
     MAIN LAYOUT
═══════════════════════════════ --}}
<div class="flex flex-1 overflow-hidden">

    {{-- ═══ SIDEBAR ═══ --}}
    <aside x-data class="hidden md:flex w-64 bg-white flex-col shrink-0 border-r border-gray-200">

        {{-- Section heading --}}
        <div class="px-5 pt-6 pb-4 border-b border-gray-200">
            <p class="font-oswald font-bold uppercase tracking-widest text-base"
               style="color:var(--cua-blue);">Quick Start</p>
        </div>

        {{-- Prompt buttons --}}
        <nav class="flex-1 overflow-y-auto">
            @php
            $prompts = [
                'Explain my degree requirements',
                'Plan next semester',
                'Explore specializations',
                'Explore minors',
                'Check graduation progress',
                'Forms & requests',
            ];
            @endphp

            @foreach($prompts as $prompt)
            <button
                class="qs-btn"
                @click="$dispatch('quick-send', { message: '{{ $prompt }}' })">
                {{ $prompt }}
            </button>
            @endforeach
        </nav>

        {{-- Sidebar footer --}}
        <div class="px-5 py-5 border-t border-gray-200">
            <p class="text-sm text-gray-500 leading-relaxed">
                Questions?
                <a href="mailto:busch-academic-services@cua.edu"
                   class="transition-colors hover:underline"
                   style="color:var(--cua-blue);">
                    Contact Academic Services
                </a>
            </p>
        </div>
    </aside>

    {{-- ═══ CHAT PANEL ═══ --}}
    <div
        class="flex-1 flex flex-col overflow-hidden"
        x-data="chatApp()"
        @quick-send.window="quickSend($event.detail.message)"
    >
        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto chat-scroll px-5 sm:px-10 py-7 space-y-5 bg-[#F5F5F5]"
            x-ref="messages"
        >
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">

                    {{-- AI message --}}
                    <template x-if="msg.role === 'assistant'">
                        <div class="max-w-[78%] bg-white border border-gray-200 shadow-sm"
                             style="border-radius:2px 14px 14px 14px; padding:18px 22px;">
                            <p class="text-base leading-[1.75] text-gray-800 whitespace-pre-wrap"
                               x-text="stripMarkdown(msg.content)"></p>
                        </div>
                    </template>

                    {{-- User message --}}
                    <template x-if="msg.role === 'user'">
                        <div class="max-w-[78%] text-white shadow-sm"
                             style="background:var(--cua-blue); border-radius:14px 14px 2px 14px; padding:18px 22px;">
                            <p class="text-base leading-[1.75] whitespace-pre-wrap"
                               x-text="msg.content"></p>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white border border-gray-200 shadow-sm px-5 py-4"
                     style="border-radius:2px 14px 14px 14px;">
                    <div class="flex gap-1.5 items-center" style="height:18px;">
                        <span class="typing-dot w-2 h-2 rounded-full" style="background:#aaa;"></span>
                        <span class="typing-dot w-2 h-2 rounded-full" style="background:#aaa;"></span>
                        <span class="typing-dot w-2 h-2 rounded-full" style="background:#aaa;"></span>
                    </div>
                </div>
            </div>

            {{-- Error --}}
            <div x-show="error" class="flex justify-center">
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm px-5 py-3 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-text="error"></span>
                </div>
            </div>
        </div>

        {{-- Input area --}}
        <div class="bg-white border-t border-gray-200 px-5 sm:px-10 pt-4 pb-3 shrink-0">

            {{-- Mobile quick-start chips --}}
            <div class="md:hidden flex gap-2 overflow-x-auto pb-3" style="scrollbar-width:none;">
                @foreach([
                    ['c' => 'Degree reqs',     'f' => 'Explain my degree requirements'],
                    ['c' => 'Plan semester',    'f' => 'Plan next semester'],
                    ['c' => 'Specializations',  'f' => 'Explore specializations'],
                    ['c' => 'Minors',           'f' => 'Explore minors'],
                    ['c' => 'Graduation',       'f' => 'Check graduation progress'],
                    ['c' => 'Forms',            'f' => 'Forms & requests'],
                ] as $item)
                <button class="chip"
                    @click="$dispatch('quick-send', { message: '{{ $item['f'] }}' })">
                    {{ $item['c'] }}
                </button>
                @endforeach
            </div>

            {{-- Compose --}}
            <form @submit.prevent="send()" class="flex items-end gap-3">
                <textarea
                    x-model="input"
                    @keydown.enter.exact.prevent="send()"
                    @keydown.enter.shift.exact="/* allow newline */"
                    x-ref="input"
                    :disabled="loading"
                    placeholder="Ask about your degree, courses, specializations, or graduation requirements…"
                    class="flex-1 resize-none border border-gray-300 rounded px-4 py-3 text-base
                           text-gray-800 placeholder-gray-400 leading-relaxed bg-[#FAFAFA]
                           focus:outline-none focus:bg-white transition-colors disabled:opacity-50"
                    style="font-family:'Source Sans 3',sans-serif;"
                    onfocus="this.style.borderColor='var(--cua-blue)'"
                    onblur="this.style.borderColor=''"
                ></textarea>
                <button
                    type="submit"
                    :disabled="loading || !input.trim()"
                    class="shrink-0 font-oswald font-semibold uppercase tracking-wide text-sm text-white
                           px-5 py-3 transition-opacity disabled:opacity-30 disabled:cursor-not-allowed"
                    style="background:var(--cua-red); border-radius:3px;">
                    Send
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     FOOTER UTILITY BAR
═══════════════════════════════ --}}
<div style="background:var(--cua-blue);" class="shrink-0 py-2 px-5 text-center">
    <p class="text-white text-xs font-light">
        AI guidance is informational — always consult with a
        <a href="https://business.catholic.edu/academics/academic-services/index.html"
           target="_blank"
           class="underline underline-offset-2 text-white hover:text-blue-200 transition-colors">
            human advisor
        </a>
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

        init() {
            this.messages.push({
                role: 'assistant',
                content: "Hello! I'm the Busch School Course Planning Bot.\n\nI can help you with degree requirements, course sequencing, specializations, minors, prerequisites, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nTo get started, tell me your degree program, catalog year, and where you are in your studies — or choose a topic from the sidebar.",
            });
        },

        stripMarkdown(text) {
            return text
                .replace(/\*\*(.*?)\*\*/gs, '$1')
                .replace(/\*(.*?)\*/gs, '$1');
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.input = '';
            this.error = null;

            const history = this.messages.map(m => ({ role: m.role, content: m.content }));
            this.messages.push({ role: 'user', content: text });
            this.loading = true;
            this.scrollToBottom();

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
