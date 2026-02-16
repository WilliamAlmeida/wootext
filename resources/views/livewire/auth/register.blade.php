<x-layouts.guest>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="space-y-2">
                <label class="label" for="name">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" required autofocus autocomplete="name" placeholder="{{ __('Full name') }}" class="input" value="{{ old('name') }}" />
            </div>

            <div class="space-y-2">
                <label class="label" for="email">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" required autocomplete="email" placeholder="email@example.com" class="input" value="{{ old('email') }}" />
            </div>

            <div class="space-y-2">
                <label class="label" for="password">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="{{ __('Password') }}" class="input" />
            </div>

            <div class="space-y-2">
                <label class="label" for="password_confirmation">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="{{ __('Confirm password') }}" class="input" />
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="btn btn-primary w-full">
                    {{ __('Create account') }}
                </button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <a class="font-medium text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200" href="{{ route('login') }}" wire:navigate>{{ __('Log in') }}</a>
        </div>
    </div>
</x-layouts.guest>
