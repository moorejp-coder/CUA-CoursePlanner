<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf
        <x-honeypot />

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#B41100] shadow-sm focus:ring-[#B41100]" name="remember">
                <span class="text-sm text-gray-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-[#B41100] hover:underline">
                    Forgot password?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full mt-1">
            Sign In
        </x-primary-button>

        <p class="text-center text-sm text-gray-500 pt-1">
            New student?
            <a href="{{ route('register') }}" class="text-[#B41100] hover:underline font-medium">Create an account</a>
        </p>
    </form>
</x-guest-layout>
