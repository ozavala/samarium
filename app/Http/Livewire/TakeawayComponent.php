<?php

namespace App\Http\Livewire;

use Livewire\Component;

use App\Takeaway;

class TakeawayComponent extends Component
{
    public $displayingTakeaway = null;

    public $modes = [
        'create' => false,
        'list' => false,
        'display' => false,
    ];

    protected $listeners = [
        'displayTakeaway',
    ];

    public function render()
    {
        return view('livewire.takeaway-component');
    }

    /* Clear modes */
    public function clearModes()
    {
        foreach ($this->modes as $key => $val) {
            $this->modes[$key] = false;
        }
    }

    /* Enter and exit mode */
    public function enterMode($modeName)
    {
        $this->clearModes();

        $this->modes[$modeName] = true;
    }

    public function exitMode($modeName)
    {
        $this->modes[$modeName] = false;
    }

    public function displayTakeaway($takeawayId)
    {
        $takeaway = Takeaway::find($takeawayId);

        $this->displayingTakeaway = $takeaway;
        $this->enterMode('display');
    }
}
