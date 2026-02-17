<?php

namespace App\Livewire\Pages\Kanban3;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Board extends Component
{
    public function render()
    {
        return view('pages.kanban3.board');
    }
}
