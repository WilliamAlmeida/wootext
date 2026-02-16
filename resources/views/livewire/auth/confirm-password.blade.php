<x-layouts.guest>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirm password')"
            :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="space-y-2">
                <label class="label" for="password">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="{{ __('Password') }}" class="input" />
            </div>

            <button type="submit" class="btn btn-primary w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </button>
        </form>
    </div>
</x-layouts.guest>
