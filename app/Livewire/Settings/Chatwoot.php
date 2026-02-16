<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Chatwoot extends Component
{
    public string $email = '';

    public string $password = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
    }

    /**
     * Sign in to Chatwoot with the provided credentials.
     */
    public function signInChatwoot(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
        ]);

        $chatwootService = app(\App\Services\ChatwootService::class);
        $response = $chatwootService->authenticate($validated['email'], $validated['password']);

        if (!$response['success']) {
            $this->addError('email', 'Invalid credentials. Please try again.');
            return;
        }

        $user->update([
            'account_id' => $response['data']['account_id'],
        ]);

        $this->dispatch('chatwoot-authenticated', name: $user->name);
    }

    public function logoutChatwoot(): void
    {
        $user = Auth::user();
        $user->update([
            'account_id' => null,
        ]);

        $this->dispatch('chatwoot-logged-out', name: $user->name);
    }

    /**
     * Render the component within the app layout (with sidebar).
     */
    public function render(): View
    {
        return view('livewire.settings.chatwoot')->layout('layouts.auth');
    }
}
