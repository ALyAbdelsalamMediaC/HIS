<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Toast extends Component
{
    public $messages;
    public $type;
    public $toastId;

    public function __construct($messages = [], $type = 'success')
    {
        // Ensure $messages is an array
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $this->messages = $messages;
        $this->type = $type;
        $this->toastId = uniqid('toast_');
    }

    public function render()
    {
        return view('components.toast');
    }
}
