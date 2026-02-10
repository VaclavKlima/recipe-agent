<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Appearance extends Component
{
    public string $locale = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->locale = Auth::user()->locale ?? config('app.locale');

        if (! array_key_exists($this->locale, $this->locales())) {
            $this->locale = config('app.locale');
        }
    }

    /**
     * Update the language preference for the authenticated user.
     */
    public function updateLocale(): void
    {
        $this->validate();

        $user = Auth::user();
        $user->locale = $this->locale;
        $user->save();

        App::setLocale($this->locale);

        $this->dispatch('locale-updated');
    }

    /**
     * Get the available locale options.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function locales(): array
    {
        return config('app.locales', []);
    }

    /**
     * Get the validation rules for the component.
     *
     * @return array<string, array<int, string|\Illuminate\Contracts\Validation\Rule>>
     */
    protected function rules(): array
    {
        return [
            'locale' => [
                'required',
                Rule::in(array_keys($this->locales())),
            ],
        ];
    }
}
