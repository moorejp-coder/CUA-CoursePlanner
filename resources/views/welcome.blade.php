<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Course Planning Bot — Busch School of Business | The Catholic University of America</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500&family=Crimson+Text:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="font-family: 'Roboto', sans-serif;">

    {{-- ── Navigation ────────────────────────────────────────────────── --}}
    <nav class="bg-[#0a3255] sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-3">
                    <img src="/images/busch_logo.jpg" alt="Busch School of Business" class="h-10 object-contain" style="mix-blend-mode: multiply;">
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}"
                       class="text-white/70 hover:text-white text-sm transition-colors duration-150 px-3 py-2 hidden sm:block">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-[#b21f2c] hover:bg-[#8c1420] text-white text-sm font-bold uppercase tracking-wider px-5 py-2 rounded transition-colors duration-150"
                       style="font-family: 'Oswald', sans-serif;">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ── Hero ───────────────────────────────────────────────────────── --}}
    <section
        class="relative overflow-hidden py-24 sm:py-32"
        style="background-color: #0a3255; background-image: repeating-linear-gradient(135deg, rgba(255,255,255,0.02) 0px, rgba(255,255,255,0.02) 1px, transparent 1px, transparent 14px);">

        {{-- Decorative red bar on the left edge --}}
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#b21f2c]"></div>

        <div class="max-w-4xl mx-auto px-6 text-center text-white">

            <p class="text-[#C9A84C] text-xs uppercase tracking-widest mb-5" style="font-family: 'Oswald', sans-serif; letter-spacing: 0.2em;">
                Tim &amp; Steph Busch School of Business &nbsp;·&nbsp; The Catholic University of America
            </p>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold uppercase leading-tight mb-6"
                style="font-family: 'Oswald', sans-serif;">
                Your Academic Advisor.<br class="hidden sm:block"> Available 24/7.
            </h1>

            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="h-px w-16 bg-[#C9A84C]/60"></div>
                <div class="h-1 w-10 bg-[#b21f2c]"></div>
                <div class="h-px w-16 bg-[#C9A84C]/60"></div>
            </div>

            <p class="text-lg sm:text-xl text-white/75 leading-relaxed mb-10 max-w-2xl mx-auto"
               style="font-family: 'Crimson Text', serif;">
                Upload your Academic Planning Worksheet and get instant, personalized guidance on degree requirements, course sequencing, and your path to graduation — no appointment needed.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto bg-[#b21f2c] hover:bg-[#8c1420] text-white font-bold uppercase tracking-wider px-8 py-4 rounded text-sm transition-colors duration-150 text-center"
                   style="font-family: 'Oswald', sans-serif; letter-spacing: 0.1em;">
                    Start Planning — It's Free
                </a>
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto border border-white/30 hover:border-white/70 text-white/80 hover:text-white px-8 py-4 rounded text-sm transition-colors duration-150 text-center"
                   style="font-family: 'Roboto', sans-serif;">
                    Sign In to Your Account
                </a>
            </div>

            <p class="mt-8 text-xs text-white/30">
                For Busch School undergraduate students &nbsp;·&nbsp; Free to use &nbsp;·&nbsp; No appointment needed
            </p>
        </div>
    </section>

    {{-- ── Social proof strip ──────────────────────────────────────────── --}}
    <div class="bg-[#071e38] py-4 border-b border-white/5">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-12 text-center">
                <div class="text-white/60 text-xs uppercase tracking-widest" style="font-family: 'Oswald', sans-serif;">
                    <span class="text-[#C9A84C] text-2xl font-bold block">12</span>
                    BSBA Specializations
                </div>
                <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                <div class="text-white/60 text-xs uppercase tracking-widest" style="font-family: 'Oswald', sans-serif;">
                    <span class="text-[#C9A84C] text-2xl font-bold block">4</span>
                    APW Versions Supported
                </div>
                <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                <div class="text-white/60 text-xs uppercase tracking-widest" style="font-family: 'Oswald', sans-serif;">
                    <span class="text-[#C9A84C] text-2xl font-bold block">24/7</span>
                    Instant Answers
                </div>
                <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                <div class="text-white/60 text-xs uppercase tracking-widest" style="font-family: 'Oswald', sans-serif;">
                    <span class="text-[#C9A84C] text-2xl font-bold block">Free</span>
                    No Cost to Students
                </div>
            </div>
        </div>
    </div>

    {{-- ── Features ───────────────────────────────────────────────────── --}}
    <section class="bg-[#efebe9] py-20">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold uppercase text-[#0a3255] mb-3"
                    style="font-family: 'Oswald', sans-serif;">
                    Everything You Need to Plan Your Degree
                </h2>
                <div class="w-12 h-1 bg-[#b21f2c] mx-auto"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Card 1 --}}
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group">
                    <div class="h-1.5 bg-[#b21f2c]"></div>
                    <div class="p-8">
                        <div class="w-12 h-12 bg-[#0a3255]/10 rounded-lg flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-[#0a3255]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="font-bold uppercase text-[#0a3255] mb-3 text-base"
                            style="font-family: 'Oswald', sans-serif;">
                            Know Your Requirements
                        </h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Upload your APW and instantly see which degree requirements you've completed, what's in progress, and exactly what remains before graduation.
                        </p>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group">
                    <div class="h-1.5 bg-[#b21f2c]"></div>
                    <div class="p-8">
                        <div class="w-12 h-12 bg-[#0a3255]/10 rounded-lg flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-[#0a3255]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold uppercase text-[#0a3255] mb-3 text-base"
                            style="font-family: 'Oswald', sans-serif;">
                            Plan Your Courses
                        </h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Get personalized course sequencing advice, prerequisite guidance, and scheduling recommendations across all 12 BSBA specializations and BSAccounting.
                        </p>
                    </div>
                </div>

                {{-- Card 3 --}}
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group">
                    <div class="h-1.5 bg-[#b21f2c]"></div>
                    <div class="p-8">
                        <div class="w-12 h-12 bg-[#0a3255]/10 rounded-lg flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-[#0a3255]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold uppercase text-[#0a3255] mb-3 text-base"
                            style="font-family: 'Oswald', sans-serif;">
                            Find Every Form
                        </h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Direct links to internship approval, directed study registration, minor declaration, and every Busch School administrative form — all in one place.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ── How it works ───────────────────────────────────────────────── --}}
    <section class="bg-white py-20">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold uppercase text-[#0a3255] mb-3"
                    style="font-family: 'Oswald', sans-serif;">
                    Up and Running in Three Steps
                </h2>
                <div class="w-12 h-1 bg-[#b21f2c] mx-auto"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
                <div>
                    <div class="text-6xl font-bold text-[#b21f2c] leading-none mb-4"
                         style="font-family: 'Oswald', sans-serif;">01</div>
                    <h3 class="font-bold uppercase text-[#0a3255] mb-3 text-base"
                        style="font-family: 'Oswald', sans-serif;">
                        Create Your Account
                    </h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Register with your CUA email address. Takes under a minute.
                    </p>
                </div>
                <div class="relative">
                    <div class="hidden md:block absolute top-8 -left-5 w-10 h-px bg-gray-200"></div>
                    <div class="text-6xl font-bold text-[#b21f2c] leading-none mb-4"
                         style="font-family: 'Oswald', sans-serif;">02</div>
                    <h3 class="font-bold uppercase text-[#0a3255] mb-3 text-base"
                        style="font-family: 'Oswald', sans-serif;">
                        Upload Your APW
                    </h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Export your Academic Planning Worksheet from Cardinal Station as a CSV and drop it into the chat.
                    </p>
                </div>
                <div class="relative">
                    <div class="hidden md:block absolute top-8 -left-5 w-10 h-px bg-gray-200"></div>
                    <div class="text-6xl font-bold text-[#b21f2c] leading-none mb-4"
                         style="font-family: 'Oswald', sans-serif;">03</div>
                    <h3 class="font-bold uppercase text-[#0a3255] mb-3 text-base"
                        style="font-family: 'Oswald', sans-serif;">
                        Ask Anything
                    </h3>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Get instant, personalized answers based on your actual academic record. No waiting, no appointment.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Quote / CTA band ───────────────────────────────────────────── --}}
    <section class="bg-[#0a3255] py-16 relative overflow-hidden"
             style="background-image: repeating-linear-gradient(135deg, rgba(255,255,255,0.015) 0px, rgba(255,255,255,0.015) 1px, transparent 1px, transparent 14px);">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#b21f2c]"></div>
        <div class="max-w-3xl mx-auto px-6 text-center text-white">
            <p class="text-[#C9A84C] text-xs uppercase tracking-widest mb-6" style="font-family: 'Oswald', sans-serif; letter-spacing: 0.2em;">
                Built for Busch School Students
            </p>
            <h2 class="text-3xl sm:text-4xl font-bold uppercase mb-6"
                style="font-family: 'Oswald', sans-serif;">
                Ready to Take Control<br>of Your Degree Plan?
            </h2>
            <p class="text-white/65 mb-10 leading-relaxed" style="font-family: 'Crimson Text', serif; font-size: 1.125rem;">
                No scheduling. No 48-hour wait for an email reply. Just clear, accurate advising — whenever you need it.
            </p>
            <a href="{{ route('register') }}"
               class="inline-block bg-[#b21f2c] hover:bg-[#8c1420] text-white font-bold uppercase tracking-wider px-10 py-4 rounded text-sm transition-colors duration-150"
               style="font-family: 'Oswald', sans-serif; letter-spacing: 0.1em;">
                Create a Free Account
            </a>
            <p class="mt-5 text-xs text-white/30">Already have an account? <a href="{{ route('login') }}" class="text-white/50 hover:text-white underline transition-colors">Sign in here.</a></p>
        </div>
    </section>

    {{-- ── Footer ─────────────────────────────────────────────────────── --}}
    <footer class="bg-[#071e38] py-10">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <img src="/images/busch_logo.jpg" alt="Tim & Steph Busch School of Business" class="h-10 object-contain" style="mix-blend-mode: multiply;">

                <p class="text-white/30 text-xs text-center leading-relaxed">
                    &copy; {{ date('Y') }} James Moore &nbsp;&middot;&nbsp; Tim &amp; Steph Busch School of Business &nbsp;&middot;&nbsp; The Catholic University of America<br>
                    This tool is for informational purposes only. For official advising decisions, please consult
                    <a href="https://business.catholic.edu/undergraduate/academic-services/" class="hover:text-white/60 underline transition-colors" target="_blank" rel="noopener">Academic Services</a>.
                </p>

                <div class="flex gap-5 text-xs text-white/40">
                    <a href="{{ route('login') }}" class="hover:text-white/70 transition-colors">Sign In</a>
                    <a href="{{ route('register') }}" class="hover:text-white/70 transition-colors">Register</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
