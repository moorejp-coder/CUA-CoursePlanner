<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Advisor — Busch School of Business</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-serif { font-family: 'EB Garamond', Georgia, serif; }

        /* Scrollbar styling */
        .chat-scroll::-webkit-scrollbar { width: 4px; }
        .chat-scroll::-webkit-scrollbar-track { background: transparent; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

        /* Typing dots */
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: .4; }
            30% { transform: translateY(-4px); opacity: 1; }
        }
        .typing-dot { animation: typingBounce 1.2s ease-in-out infinite; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }

        /* Textarea auto-resize */
        textarea { field-sizing: content; min-height: 2.5rem; max-height: 8rem; }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden bg-[#F5F3EF]">

{{-- ══════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════ --}}
<header class="bg-cua-blue shrink-0 shadow-lg z-10">
    <div class="flex items-center justify-between px-6 py-0 h-16">

        {{-- Left: logo + wordmark --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('chat') }}" class="flex items-center gap-3">
                <img src="/images/busch_logo.jpg"
                     alt="Busch School of Business"
                     class="h-9 w-auto rounded-sm shadow-sm">
                <div class="hidden sm:block leading-tight">
                    <p class="font-serif text-white text-base font-medium tracking-wide leading-none">Busch School of Business</p>
                    <p class="text-[11px] text-blue-200 tracking-widest uppercase mt-0.5">Academic Advisor</p>
                </div>
            </a>
        </div>

        {{-- Right: user + nav --}}
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 text-sm text-blue-200 mr-1">
                <div class="w-7 h-7 rounded-full bg-cua-gold/20 border border-cua-gold/40 flex items-center justify-center">
                    <span class="text-cua-gold text-xs font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <span class="text-blue-100 text-sm">{{ Auth::user()->name }}</span>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="hidden sm:inline-flex items-center gap-1 text-xs text-blue-200 hover:text-white transition-colors px-2 py-1 rounded hover:bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 text-xs bg-cua-red hover:bg-red-800 text-white px-3 py-1.5 rounded transition-colors font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </div>

    {{-- Gold accent line --}}
    <div class="h-[3px] bg-gradient-to-r from-cua-gold/80 via-cua-gold to-cua-gold/40"></div>
</header>

{{-- ══════════════════════════════════════════════
     MAIN LAYOUT
══════════════════════════════════════════════ --}}
<div class="flex flex-1 overflow-hidden">

    {{-- ══════ SIDEBAR ══════ --}}
    <aside class="hidden md:flex w-64 bg-cua-blue flex-col shrink-0 border-r border-white/10">

        {{-- Sidebar header --}}
        <div class="px-5 pt-6 pb-4 border-b border-white/10">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-cua-gold mb-1">Quick Start</p>
            <p class="text-xs text-blue-200/70 leading-snug">Select a topic to begin your advising session</p>
        </div>

        {{-- Quick-start buttons --}}
        <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1">
            @php
            $prompts = [
                ['label' => 'Explain my degree requirements',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Plan next semester',               'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['label' => 'Explore specializations',          'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['label' => 'Explore minors',                   'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['label' => 'Check graduation progress',        'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                ['label' => 'Forms & requests',                 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ];
            @endphp

            @foreach($prompts as $prompt)
            <button
                @click="$dispatch('quick-send', { message: '{{ $prompt['label'] }}' })"
                class="w-full flex items-center gap-3 text-left px-3 py-2.5 rounded-lg text-sm
                       text-blue-100 hover:text-white hover:bg-white/10
                       transition-all duration-150 group">
                <span class="flex-shrink-0 w-7 h-7 rounded-md bg-white/5 group-hover:bg-cua-gold/20
                             flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-cua-gold/70 group-hover:text-cua-gold transition-colors"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $prompt['icon'] }}" />
                    </svg>
                </span>
                <span class="leading-snug text-[13px]">{{ $prompt['label'] }}</span>
            </button>
            @endforeach
        </nav>

        {{-- Sidebar footer --}}
        <div class="px-5 py-4 border-t border-white/10">
            <p class="text-[11px] text-blue-300/60 leading-relaxed">
                Questions? Contact
                <a href="mailto:busch-academic-services@cua.edu"
                   class="text-cua-gold/80 hover:text-cua-gold transition-colors">
                    Academic Services
                </a>
            </p>
        </div>
    </aside>

    {{-- ══════ CHAT PANEL ══════ --}}
    <div
        class="flex-1 flex flex-col overflow-hidden"
        x-data="chatApp()"
        x-init="init()"
        @quick-send.window="quickSend($event.detail.message)"
    >
        {{-- ── Messages area ── --}}
        <div
            class="flex-1 overflow-y-auto chat-scroll px-4 sm:px-8 py-6 space-y-5"
            x-ref="messages"
        >
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">

                    {{-- Assistant message --}}
                    <template x-if="msg.role === 'assistant'">
                        <div class="flex items-end gap-2.5 max-w-[75%]">
                            <div class="w-8 h-8 rounded-full bg-cua-blue border-2 border-white shadow flex items-center justify-center shrink-0 mb-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cua-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                            </div>
                            <div class="bg-white border border-gray-200/80 text-gray-800 rounded-2xl rounded-bl-sm
                                        px-4 py-3 shadow-sm text-sm leading-relaxed whitespace-pre-wrap">
                                <p x-text="msg.content"></p>
                            </div>
                        </div>
                    </template>

                    {{-- User message --}}
                    <template x-if="msg.role === 'user'">
                        <div class="max-w-[75%]">
                            <div class="bg-cua-red text-white rounded-2xl rounded-br-sm
                                        px-4 py-3 shadow-sm text-sm leading-relaxed whitespace-pre-wrap"
                                 x-text="msg.content"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start items-end gap-2.5">
                <div class="w-8 h-8 rounded-full bg-cua-blue border-2 border-white shadow flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cua-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                    </svg>
                </div>
                <div class="bg-white border border-gray-200/80 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm">
                    <div class="flex gap-1.5 items-center h-4">
                        <span class="typing-dot w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                        <span class="typing-dot w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                        <span class="typing-dot w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                    </div>
                </div>
            </div>

            {{-- Error notice --}}
            <div x-show="error" class="flex justify-center">
                <div class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700
                            text-xs rounded-lg px-4 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-text="error"></span>
                </div>
            </div>
        </div>

        {{-- ── Input bar ── --}}
        <div class="bg-white border-t border-gray-200 px-4 sm:px-8 py-4 shrink-0 shadow-[0_-1px_3px_rgba(0,0,0,0.04)]">

            {{-- Mobile quick-start chips --}}
            <div class="md:hidden flex gap-2 overflow-x-auto pb-3 -mx-1 px-1 scrollbar-none">
                @foreach([
                    ['chip' => 'Degree requirements', 'full' => 'Explain my degree requirements'],
                    ['chip' => 'Plan semester',        'full' => 'Plan next semester'],
                    ['chip' => 'Specializations',      'full' => 'Explore specializations'],
                    ['chip' => 'Minors',               'full' => 'Explore minors'],
                    ['chip' => 'Graduation',           'full' => 'Check graduation progress'],
                    ['chip' => 'Forms',                'full' => 'Forms & requests'],
                ] as $item)
                <button
                    @click="$dispatch('quick-send', { message: '{{ $item['full'] }}' })"
                    class="shrink-0 text-xs font-medium bg-gray-100 hover:bg-cua-blue hover:text-white
                           text-gray-600 px-3 py-1.5 rounded-full transition-colors whitespace-nowrap border border-gray-200 hover:border-transparent">
                    {{ $item['chip'] }}
                </button>
                @endforeach
            </div>

            {{-- Compose area --}}
            <form @submit.prevent="send()" class="flex items-end gap-3">
                <div class="flex-1 relative">
                    <textarea
                        x-model="input"
                        @keydown.enter.exact.prevent="send()"
                        @keydown.enter.shift.exact="/* allow newline */"
                        x-ref="input"
                        :disabled="loading"
                        placeholder="Ask about your degree, courses, specializations, or graduation…"
                        class="w-full resize-none rounded-xl border border-gray-300 bg-gray-50
                               px-4 py-2.5 pr-4 text-sm text-gray-800 placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-cua-blue/30 focus:border-cua-blue
                               focus:bg-white disabled:opacity-50 transition-colors leading-relaxed"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    :disabled="loading || !input.trim()"
                    class="shrink-0 h-10 w-10 flex items-center justify-center rounded-xl
                           bg-cua-red hover:bg-red-800 disabled:bg-gray-200 disabled:cursor-not-allowed
                           text-white shadow-sm transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>

            {{-- Disclaimer --}}
            <p class="text-[11px] text-gray-400 mt-2.5 text-center leading-relaxed">
                AI guidance is informational — always consult with a
                <a href="https://business.catholic.edu/academics/academic-services/index.html"
                   target="_blank"
                   class="text-cua-blue hover:text-cua-red underline underline-offset-2 transition-colors">
                    human advisor
                </a>
                before finalizing your schedule or degree plan.
            </p>
        </div>
    </div>
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
                content: "Hello! I'm the Busch School Academic Advisor.\n\nI can help you with degree requirements, course sequencing, specializations, minors, prerequisites, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nTo get started, tell me your degree program, catalog year, and where you are in your studies — or choose a topic from the sidebar.",
            });
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
            this.$nextTick(() => this.send());
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
