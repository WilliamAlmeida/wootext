<x-layouts.guest>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="space-y-2">
                <label class="label" for="email">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" required autocomplete="email" value="{{ request('email') }}" class="input" />
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
                <button type="submit" class="btn btn-primary w-full" data-test="reset-password-button">
                    {{ __('Reset password') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
