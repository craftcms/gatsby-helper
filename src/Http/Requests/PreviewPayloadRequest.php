<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Not `token`, which Craft reads as a route token on every request.
 *
 * @since 3.0.0
 */
class PreviewPayloadRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'previewToken' => ['required', 'string', 'max:255'],
        ];
    }
}
