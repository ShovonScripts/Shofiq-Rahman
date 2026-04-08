<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorAchievementRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'title'         => ['required','string','max:255'],
            'year'          => ['nullable','string','max:32'],
            'description_md'=> ['nullable','string'],
            'icon'          => ['nullable','string','max:64'],
            'sort_order'    => ['nullable','integer','min:0','max:100000'],
        ];
    }
}
