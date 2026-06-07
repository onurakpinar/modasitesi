<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->input('company_website'))) {
            throw new HttpResponseException(
                redirect()->route('pages.contact')->with('contact_success', true)
            );
        }

        $this->merge([
            'name' => strip_tags((string) $this->input('name', '')),
            'email' => strip_tags((string) $this->input('email', '')),
            'subject' => strip_tags((string) $this->input('subject', '')),
            'message' => strip_tags((string) $this->input('message', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ad',
            'email' => 'e-posta',
            'subject' => 'konu',
            'message' => 'mesaj',
            'privacy_acknowledged' => 'gizlilik onayı',
        ];
    }

}
