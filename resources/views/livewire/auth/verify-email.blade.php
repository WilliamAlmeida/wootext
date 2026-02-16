<x-layouts.guest>
    <div class="mt-4 flex flex-col gap-6">
        <p class="text-center text-sm text-neutral-600 dark:text-neutral-300">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <p class="text-center text-sm font-medium text-green-700 dark:text-green-400">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-full">
                    {{ __('Resend verification email') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost w-full justify-center text-sm" data-test="logout-button">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest>
