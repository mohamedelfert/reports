<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use App\User;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class AgentsReportAction
{
    public function __construct(CallPythonUrlAction $callPythonUrlAction)
    {
        $this->callPythonUrlAction = $callPythonUrlAction;
    }

    public function execute($request)
    {
        $length = $data['length'] ?? 10;
        $page = $data['page'] ?? 1;
        $skip = $length * ($page - 1);
        $users = User::skip($skip)->take($length)->get();

        $authorizationToken = $request->bearerToken();

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
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $authorizationToken
            ]
        ];

        $path = $request->input('slug');

        try {
            $agents_report = json_decode($this->callPythonUrlAction->execute($data, $path), true);

            foreach ($agents_report as $userId => $reportData) {
                // Check if the userId matches the user's id
                foreach ($users as $user) {
                    if ($userId === $user->id) {
                        // Match found, add report data to the user
                        $user->report = $reportData;
                        break;
                    }
                }
            }

            // Return the users with report data
            $data = Datatables::of($users)
                ->addColumn('report', function ($user) {
                    // Return the report data for the user
                    return $user->report;
                })
                ->make(true);

            return response()->json([
                "data" => $data,
                "message" => "Retrieved all reports successfully.",
                "status" => 200
            ]);
        } catch (Exception $e) {
            // Handle exceptions, connection issues, server errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
