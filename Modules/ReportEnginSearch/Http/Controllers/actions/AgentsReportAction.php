<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use App\User;
use Exception;
use GuzzleHttp\Client;
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

        $data = $this->callPythonUrlAction->execute($data, $path);

        return json_decode($data, true);

        // Create a Guzzle client
        $client = new Client();



        try {
            // Send POST request to the Flask API
            return $client->post('http://127.0.0.1:4000/reports/agents-report', $data)->getBody();

            // Check for a successful status code (HTTP 200)
            if ($reports->getStatusCode() === 200) {
                $agents_report = json_decode($reports->getBody(), true);

                dd($agents_report);

                foreach ($agents_report as $userId => $reportData) {
                    // Check if the userId matches the user's id
                    foreach ($users as $user) {
                        if ($userId == $user->id) {
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
            }

            // Handle the case when $response is null (for default)
            return response()->json(['error' => 'Invalid slug provided'], 400);
        } catch (Exception $e) {
            // Handle exceptions, connection issues, server errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
