<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'leave_type_id' =>'required',
            'from'          =>'required|date_format:d/m/Y',
            'to'            =>'required|date_format:d/m/Y',
            'reason'        =>'max:100',
        ];
    }

        public function messages()
    {
        return [
            'leave_type_id.required'    => 'Please enter leave type!',
            'from.required'             => 'Please enter from date!',
            'from.date_format'          => 'From must be in dd/mm/yyyy format!',
            'to.required'               => 'Please enter to date',
            'to.date_format'            => 'To must be in dd/mm/yyyy format!',
            'reason.max'                => 'You have reached the maximum characters (100) for reason.',
        ];
    }
}
