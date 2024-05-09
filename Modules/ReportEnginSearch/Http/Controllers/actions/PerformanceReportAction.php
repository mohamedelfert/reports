<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use Exception;

class PerformanceReportAction
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
                'subdomain_id' => $request->input('subdomain_id'),
                'user_id' => $request->input('user_id') ?? null,
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]
        ];

        $path = $request->input('slug');

        try {
            $performance_report = collect(json_decode($this->callPythonUrlAction->execute($path, $data), true));

            //    dd($performance_report);

            return response()->json([
                "data" => $performance_report,
                "message" => "Retrieved all reports successfully.",
                "status" => 200
            ]);
        } catch (Exception $e) {
            // Handle exceptions, connection issues, server errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
