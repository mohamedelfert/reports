<?php

namespace Modules\ReportEnginSearch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (request()->input('slug') && request()->input('slug') === 'overall-activities') {
            $charts = 'required|array';
        } else {
            $charts = 'nullable|array';
        }

        if (request()->input('slug') && request()->input('slug') === 'performance-report') {
            $user_id = 'required';
            $type = 'nullable';
        } elseif (request()->input('slug') && request()->input('slug') === 'by-agent-report') {
            $user_id = 'nullable';
            $type = 'nullable';
        } elseif (request()->input('slug') && request()->input('slug') === 'by-team-report') {
            $user_id = 'nullable';
            $type = 'nullable';
        }else {
            $user_id = 'nullable';
            $type = 'required|string';
        }

        return [
            'slug' => 'required',
            'type' => $type,
            'subdomain_id' => 'required|integer',
            'charts' => $charts,
            'users_ids' => 'nullable|array',
            'user_id' => $user_id,
            'groups_ids' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
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

