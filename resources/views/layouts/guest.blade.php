<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Course Planning Bot') }} — The Catholic University of America</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@400;500&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="font-family: 'Roboto', sans-serif;">
        <div
            class="min-h-screen flex items-center justify-center p-4 sm:p-6"
            style="background-color: #003366; background-image: repeating-linear-gradient(45deg, rgba(255,255,255,0.025) 0px, rgba(255,255,255,0.025) 1px, transparent 1px, transparent 12px);">

            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

                    <!-- Card Header -->
                    <div class="px-8 pt-8 pb-5 text-center">
                        <a href="/">
                            <img
                                src="/images/cua_logo.png"
                                alt="Tim & Steph Busch School of Business"
                                class="mx-auto mb-4 object-contain"
                                style="max-height: 80px;">
                        </a>

                        <h1 class="text-2xl font-bold uppercase tracking-wide text-[#B41100]" style="font-family: 'Oswald', sans-serif;">
                            Course Planning Bot
                        </h1>

                        <p class="text-xs text-gray-500 mt-1 tracking-wide">
                            The Catholic University of America
                        </p>

                        <div class="mt-4 border-t-2 border-[#C9A84C]"></div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-8 pb-6">
                        {{ $slot }}
                    </div>

                    <!-- Card Footer -->
                    <div class="px-8 pb-6">
                        <p class="text-xs text-gray-400 text-center leading-relaxed">
                            For official advising, contact the
                            <a href="https://business.catholic.edu/undergraduate/academic-services/" class="text-[#B41100] hover:underline" target="_blank" rel="noopener">Busch School Academic Services</a> office.
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-center text-xs text-white/40">
                    &copy; {{ date('Y') }} The Catholic University of America
                </p>
            </div>
        </div>
    </body>
</html>
