<?php

namespace App\Livewire\Pages\Settings;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.auth')]
class Index extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'profile';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('pages.settings.index');
    }
}
