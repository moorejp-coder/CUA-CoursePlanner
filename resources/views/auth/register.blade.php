<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" :value="__('CUA Email Address')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <p class="text-xs text-gray-400 text-center -mt-1">
            For Busch School undergraduate students only.
        </p>

        <x-primary-button class="w-full mt-1">
            Create Account
        </x-primary-button>

        <p class="text-center text-sm text-gray-500 pt-1">
            Already have an account?
            <a href="{{ route('login') }}" class="text-[#B41100] hover:underline font-medium">Sign in</a>
        </p>
    </form>
</x-guest-layout>
