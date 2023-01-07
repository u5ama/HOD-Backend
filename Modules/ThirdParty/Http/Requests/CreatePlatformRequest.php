<?php

namespace Modules\ThirdParty\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlatformRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'platform_name' => 'required',
            'third_party_link' =>'required|unique:online_listing_request|url|active_url',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
