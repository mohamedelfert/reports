<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions\leads;

use Exception;
use Modules\ReportEnginSearch\Http\Controllers\actions\CallPythonUrlAction;

class LeadsOverallAction
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
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]
        ];

        $path = $request->input('slug');

        try {
            $leads_overall = collect(json_decode($this->callPythonUrlAction->execute($path, $data), true));

            return response()->json([
                "data" => $leads_overall,
                "message" => "Retrieved all reports successfully.",
                "status" => 200
            ]);
        } catch (Exception $e) {
            // Handle exceptions, connection issues, server errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
