<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Planning Advisor — Busch School</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased h-screen flex flex-col overflow-hidden bg-gray-100">

{{-- ===== Header ===== --}}
<header class="bg-cua-blue text-white flex items-center justify-between px-5 py-3 shadow-md shrink-0">
    <div class="flex items-center gap-4">
        <img src="/images/busch_logo.jpg" alt="Busch School of Business" class="h-10 w-auto rounded">
        <div>
            <p class="font-semibold text-base leading-tight">Course Planning Advisor</p>
            <p class="text-xs text-blue-200 leading-tight">Tim & Steph Busch School of Business · CUA</p>
        </div>
    </div>
    <div class="flex items-center gap-4 text-sm">
        <span class="text-blue-100 hidden sm:block">{{ Auth::user()->name }}</span>
        <a href="{{ route('profile.edit') }}"
           class="text-blue-200 hover:text-white transition-colors hidden sm:block">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="bg-cua-red hover:bg-red-800 text-white text-xs px-3 py-1.5 rounded transition-colors">
                Sign Out
            </button>
        </form>
    </div>
</header>

{{-- ===== Main layout ===== --}}
<div class="flex flex-1 overflow-hidden">

    {{-- ===== Sidebar ===== --}}
    <aside class="w-60 bg-cua-blue text-white flex flex-col shrink-0 border-r border-blue-900 hidden md:flex">
        <div class="px-4 pt-5 pb-3 border-b border-blue-700">
            <p class="text-xs font-semibold uppercase tracking-wider text-cua-gold">Quick Start</p>
        </div>

        <nav class="flex flex-col gap-1 p-3 flex-1 overflow-y-auto">
            @foreach([
                ['Explain my degree requirements',   'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['Plan next semester',               'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Explore specializations',          'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['Explore minors',                   'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['Check graduation progress',        'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                ['Forms & requests',                 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ] as [$label, $iconPath])
            <button
                x-data
                @click="$dispatch('quick-send', { message: '{{ $label }}' })"
                class="flex items-center gap-3 text-left px-3 py-2.5 rounded-lg text-sm text-blue-100
                       hover:bg-blue-800 hover:text-white transition-colors group w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-cua-gold group-hover:text-yellow-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
                </svg>
                <span>{{ $label }}</span>
            </button>
            @endforeach
        </nav>

        <div class="px-4 py-4 border-t border-blue-700 text-xs text-blue-300 leading-relaxed">
            For official advising, contact<br>
            <span class="text-cua-gold font-medium">Academic Services</span>
        </div>
    </aside>

    {{-- ===== Chat panel ===== --}}
    <div
        class="flex-1 flex flex-col overflow-hidden"
        x-data="chatApp()"
        x-init="init()"
        @quick-send.window="quickSend($event.detail.message)"
    >
        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto px-4 py-5 space-y-4"
            x-ref="messages"
        >
            <template x-for="(msg, i) in messages" :key="i">
                <div
                    :class="msg.role === 'user'
                        ? 'flex justify-end'
                        : 'flex justify-start'"
                >
                    {{-- Assistant avatar --}}
                    <template x-if="msg.role === 'assistant'">
                        <div class="flex items-end gap-2 max-w-[80%]">
                            <div class="w-7 h-7 rounded-full bg-cua-blue flex items-center justify-center shrink-0 mb-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                            </div>
                            <div class="bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-sm px-4 py-3 text-sm shadow-sm whitespace-pre-wrap leading-relaxed"
                                 x-text="msg.content"></div>
                        </div>
                    </template>

                    {{-- User bubble --}}
                    <template x-if="msg.role === 'user'">
                        <div class="bg-cua-red text-white rounded-2xl rounded-br-sm px-4 py-3 text-sm max-w-[80%] shadow-sm whitespace-pre-wrap leading-relaxed"
                             x-text="msg.content"></div>
                    </template>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="flex items-end gap-2">
                    <div class="w-7 h-7 rounded-full bg-cua-blue flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        </svg>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm">
                        <div class="flex gap-1 items-center h-4">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Error notice --}}
            <div x-show="error" class="flex justify-center">
                <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg px-4 py-2 max-w-sm text-center"
                     x-text="error"></div>
            </div>
        </div>

        {{-- Input bar --}}
        <div class="bg-white border-t border-gray-200 px-4 py-3 shrink-0">
            {{-- Mobile quick-start (shown only on small screens) --}}
            <div class="md:hidden flex gap-2 overflow-x-auto pb-2 mb-2 scrollbar-none">
                @foreach(['Degree requirements', 'Plan semester', 'Specializations', 'Minors', 'Graduation', 'Forms'] as $short)
                <button
                    @click="$dispatch('quick-send', { message: '{{ ['Explain my degree requirements','Plan next semester','Explore specializations','Explore minors','Check graduation progress','Forms & requests'][$loop->index] }}' })"
                    class="shrink-0 text-xs bg-gray-100 hover:bg-cua-blue hover:text-white text-gray-600 px-3 py-1.5 rounded-full transition-colors whitespace-nowrap">
                    {{ $short }}
                </button>
                @endforeach
            </div>

            <form @submit.prevent="send()" class="flex gap-2 items-end">
                <textarea
                    x-model="input"
                    @keydown.enter.exact.prevent="send()"
                    @keydown.enter.shift.exact="/* allow newline */"
                    x-ref="input"
                    rows="1"
                    :disabled="loading"
                    placeholder="Ask about your degree, courses, or graduation requirements…"
                    class="flex-1 resize-none rounded-xl border border-gray-300 px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-cua-blue focus:border-transparent
                           disabled:bg-gray-50 disabled:text-gray-400 max-h-32 leading-relaxed"
                    style="overflow-y:hidden"
                    @input="autoResize($el)"
                ></textarea>
                <button
                    type="submit"
                    :disabled="loading || !input.trim()"
                    class="bg-cua-red hover:bg-red-800 disabled:bg-gray-300 disabled:cursor-not-allowed
                           text-white rounded-xl px-4 py-2.5 transition-colors shrink-0 flex items-center gap-1.5 text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span class="hidden sm:inline">Send</span>
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2 text-center">
                AI guidance is informational — always consult with a
                <a href="https://business.catholic.edu/academics/academic-services/index.html" target="_blank"
                   class="text-cua-blue hover:underline">human advisor</a>
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
                content: "Hi! I'm your Busch School Course Planning Advisor. I can help you with degree requirements, course sequencing, specializations, minors, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nWhat can I help you with today?",
            });
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.input = '';
            this.error = null;
            this.$nextTick(() => this.autoResize(this.$refs.input));

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

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        },
    };
}
</script>

</body>
</html>
