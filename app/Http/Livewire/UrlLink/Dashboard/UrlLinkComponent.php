<?php

namespace App\Http\Livewire\UrlLink\Dashboard;

use Livewire\Component;

use App\Traits\ModesTrait;

class UrlLinkComponent extends Component
{
    use ModesTrait;

    public $modes = [
        'create' => false,
        'list' => false,
        'display' => false,
    ];

    protected $listeners = [
        'urlLinkCreateCancelled',
        'urlLinkCreateCompleted',
    ];

    public function render()
    {
        return view('livewire.url-link.dashboard.url-link-component');
    }

    public function urlLinkCreateCancelled()
    {
        $this->exitMode('create');
    }

    public function urlLinkCreateCompleted()
    {
        $this->exitMode('create');
    }
}
