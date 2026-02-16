<?php

namespace App\Livewire\Settings;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Appearance extends Component
{
    public string $appearance = 'system';

    public function mount(): void
    {
        $this->appearance = auth()->user()->appearance ?? 'system';
    }

    public function updatedAppearance(): void
    {
        auth()->user()->update([
            'appearance' => $this->appearance,
        ]);

        $this->dispatch('appearance-updated', appearance: $this->appearance);
    }

    public function render(): View
    {
        return view('livewire.settings.appearance')->layout('layouts.auth');
    }
}
