<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Active Sessions') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('If you believe your account has been accessed from another device, sign out of all sessions immediately. This will end every active login — including this one.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('logout.everywhere') }}">
        @csrf
        <x-danger-button>
            {{ __('Sign Out of All Devices') }}
        </x-danger-button>
    </form>
</section>
