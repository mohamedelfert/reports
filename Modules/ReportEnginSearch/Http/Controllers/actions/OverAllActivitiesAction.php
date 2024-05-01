<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

class OverAllActivitiesAction
{
    public function __construct(CallPythonUrlAction $callPythonUrlAction)
    {
        $this->callPythonUrlAction = $callPythonUrlAction;
    }

    public function execute($request)
    {
        // Prepare request data for the Flask API
        $data = [
            'json' => [
                'slug' => $request->input('slug'),
                'type' => $request->input('type'),
                'subdomain_id' => $request->input('subdomain_id'),
                'charts' => $request->input('charts'),
                'users_ids' => $request->input('users_ids') ?? null,
                'groups_ids' => $request->input('groups_ids') ?? null,
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]
        ];

        $path = $request->input('slug');

        $overall_reports = $this->callPythonUrlAction->execute($path, $data);

        return json_decode($overall_reports, true);
    }
}
