<x-layouts.guest>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <div class="space-y-2">
                <label class="label" for="email">{{ __('Email Address') }}</label>
                <input id="email" name="email" type="email" required autofocus placeholder="email@example.com" class="input" value="{{ old('email') }}" />
            </div>

            <button type="submit" class="btn btn-primary w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <a class="font-medium text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200" href="{{ route('login') }}" wire:navigate>{{ __('log in') }}</a>
        </div>
    </div>
</x-layouts.guest>
