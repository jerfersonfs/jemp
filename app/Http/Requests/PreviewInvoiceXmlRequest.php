<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewInvoiceXmlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['xml_file' => ['required', 'file', 'mimetypes:application/xml,text/xml,text/plain', 'extensions:xml', 'max:2048']];
    }

    public function messages(): array
    {
        return [
            'xml_file.required' => 'Selecione o XML da NF-e.',
            'xml_file.file' => 'Envie um arquivo XML válido.',
            'xml_file.mimetypes' => 'O conteúdo do arquivo precisa ser XML ou texto XML.',
            'xml_file.extensions' => 'O arquivo deve ter a extensão .xml.',
            'xml_file.max' => 'O XML deve ter no máximo 2 MB.',
        ];
    }
}
