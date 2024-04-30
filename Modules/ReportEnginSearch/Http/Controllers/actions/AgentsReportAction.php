<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use App\User;
use Exception;
use GuzzleHttp\Client;
use Yajra\DataTables\Facades\DataTables;

class AgentsReportAction
{
    public function execute($data)
    {
        // Create a Guzzle client
        $client = new Client();

        $page_length = $data['page_length'] ?? 10;
        $current_page = $data['current_page'] ?? 1;
        $skip = $page_length * ($current_page - 1);
        $users = User::skip($skip)->take($page_length)->get();

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
