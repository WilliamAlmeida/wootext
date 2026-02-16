<?php

namespace App\Livewire\Pages\Home;

use Livewire\Component;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.public')]
class Home extends Component
{
    public function render(): View
    {
        return view('pages.home');
    }
}
