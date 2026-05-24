<x-guest-layout>
    <p class="mb-5 text-sm text-gray-600 leading-relaxed">
        Enter your email address and we'll send you a link to reset your password.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <x-primary-button class="w-full mt-1">
            Send Reset Link
        </x-primary-button>

        <p class="text-center text-sm text-gray-500 pt-1">
            Remembered your password?
            <a href="{{ route('login') }}" class="text-[#B41100] hover:underline font-medium">Sign in</a>
        </p>
    </form>
</x-guest-layout>
