<?php

namespace App\Livewire;

use Livewire\Component;

class MultiStepForm extends Component
{
    // DON'T CHANGE THIS START
    public int $step = 1;

    public string $primaryColor = '#2563eb';
    public string $buttonColor = '#2563eb';
    // DON'T CHANGE THIS END

    // FORM INPUTS
    public string $name = '';
    public string $email = '';
    public string $message = '';

    public function rules(): array
    {
        return match ($this->step) {
            1 => ['name' => 'required|min:2'],
            2 => ['email' => 'required|email'],
            3 => ['message' => 'required|min:10'],
            default => [],
        };
    }

    public function nextStep()
    {
        $this->validate($this->rules());
        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'message' => 'required|min:10',
        ]);

        session()->flash('success', 'Form submitted successfully!');
        $this->reset(['name', 'email', 'message']);
        $this->step = 1;

        return redirect()->to('/'); // zorg voor redirect na submit
    }

    public function render()
    {
        return view('livewire.multi-step-form');
    }
}
