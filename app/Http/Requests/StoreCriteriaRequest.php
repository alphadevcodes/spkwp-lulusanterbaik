<?php

namespace App\Http\Requests;

use App\Enums\CriteriaAttribute;
use App\Enums\CriteriaCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:criterias,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(CriteriaCategory::class)],
            'attribute' => ['required', Rule::enum(CriteriaAttribute::class)],
            'weight' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode',
            'name' => 'nama',
            'category' => 'kategori',
            'attribute' => 'atribut',
            'weight' => 'bobot',
        ];
    }
}
