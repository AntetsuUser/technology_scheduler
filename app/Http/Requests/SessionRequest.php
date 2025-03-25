<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'deletes' => ['required', 'array', 'min:1'], // 配列で少なくとも1つのチェックが必要

            // 'deletes.*' => ['exists:Excel_info,id'], // dbにidが含まれているかチェック
        ];
    }

    /**
     * Get the custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deletes.required' => '削除するオブジェクトを1つ以上選択してください。',
            'deletes.array' => '削除対象のチェックボックスは配列形式で送信してください。',
            'deletes.min' => '削除するオブジェクトを1つ以上選択してください。',

            // 'deletes.*.exists' => '選択したオブジェクトが無効です。',
        ];
    }
}
