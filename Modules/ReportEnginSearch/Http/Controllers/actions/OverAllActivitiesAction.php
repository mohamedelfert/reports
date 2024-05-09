<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use Exception;

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

        try {
            $overall_reports = collect(json_decode($this->callPythonUrlAction->execute($path, $data), true));

            return response()->json([
                "data" => $overall_reports,
                "message" => "Retrieved all reports successfully.",
                "status" => 200
            ]);
        } catch (Exception $e) {
            // Handle exceptions, connection issues, server errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
