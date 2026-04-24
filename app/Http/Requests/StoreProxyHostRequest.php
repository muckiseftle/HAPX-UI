<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProxyHostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'hostnames' => 'nullable|array',
            'hostnames.*' => 'required|string|max:255',
            'mode' => 'required|in:http,tcp',
            'listen_address' => 'required|string',
            'listen_port' => 'required|integer|min:1|max:65535',
            'certificate_path' => 'nullable|string',
            'tls_termination' => 'nullable|boolean',
            'balance_algorithm' => 'nullable|string',
            'backends' => 'required|array|min:1',
            'backends.*.name' => 'required|string',
            'backends.*.address' => 'required|string',
            'backends.*.port' => 'required|integer|min:1|max:65535',
        ];
    }

    protected function prepareForValidation()
    {
        // Filtere leere Hostnames raus
        $hostnames = array_filter($this->hostnames ?? [], fn($h) => !empty($h));
        
        $this->merge([
            'tls_termination' => $this->has('tls_termination'),
            'hostnames' => array_values($hostnames),
        ]);
    }
}
