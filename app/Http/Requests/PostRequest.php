<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            // 開始日
            // 'startDate' => ['required', 'string', 'regex:^\d{4}/\d{1,2}/\d{1,2}$'], 

            // 'start_date' => ['required', 'date', 'regex:#^\d{4}/\d{1,2}/\d{1,2}$#'],
            'start_date' => ['required', 'regex:#^\d{4}/\d{1,2}/\d{1,2}$#'],

            // 終了日
            // 'end_date' => ['required', 'string', 'regex:^\d{4}/\d{1,2}/\d{1,2}$'], 

            // 'end_date' => ['required', 'date', 'regex:#^\d{4}/\d{1,2}/\d{1,2}$#']
            'end_date' => ['required', 'regex:#^\d{4}/\d{1,2}/\d{1,2}$#'],
        ];
    }

    public function messages()
    {
        // return [
        //     'start_date.required' => ':input / 必須項目です。正しい形式で入力してください。',
        //     'end_date.required' => ':input / 必須項目です。正しい形式で入力してください。'
        // ];

        return [
            'start_date.required' => '開始日は必須項目です。',
            'start_date.regex' => '開始日 ":input" は yyyy/mm/dd の形式で入力してください。',
            'start_date.date_format' => '開始日 ":input" は正しい日付形式で入力してください。',
    
            'end_date.required' => '終了日は必須項目です。',
            'end_date.regex' => '終了日 ":input" は yyyy/mm/dd の形式で入力してください。',
            'end_date.date_format' => '終了日 ":input" は正しい日付形式で入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付にしてください。',
        ];
    }
}
