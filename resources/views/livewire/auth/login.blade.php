<x-layouts.guest>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="space-y-2">
                <label class="label" for="email">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" required autofocus autocomplete="email" placeholder="email@example.com" class="input" value="{{ old('email') }}" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="label mb-0" for="password">{{ __('Password') }}</label>
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="{{ __('Password') }}" class="input" />
            </div>

            <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                <input type="checkbox" name="remember" class="checkbox" {{ old('remember') ? 'checked' : '' }} />
                <span>{{ __('Remember me') }}</span>
            </label>

            <div class="flex items-center justify-end">
                <button type="submit" class="btn btn-primary w-full" data-test="login-button">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <a class="font-medium text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200" href="{{ route('register') }}" wire:navigate>{{ __('Sign up') }}</a>
            </div>
        @endif
    </div>
</x-layouts.guest>
