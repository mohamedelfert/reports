<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use Exception;
use GuzzleHttp\Client;

class OverAllActivitiesAction
{
    public function execute($data)
    {
        // Create a Guzzle client
        $client = new Client();

        // Send POST request to the Flask API
        try {
            $overall_report = $client->post('http://127.0.0.1:4000/reports/overall-activities', $data);

            // Check for a successful status code (HTTP 200)
            if ($overall_report != null) {
                if ($overall_report->getStatusCode() === 200) {
                    // Return the records received from the Flask API as a JSON response
                    return json_decode($overall_report->getBody(), true);
                }
            }

            // Return an error response if the request was not successful
            return response()->json(['error' => 'Failed to fetch reports from the Flask API'], 500);
        } catch (Exception $e) {
            // Handle exceptions (e.g., connection issues, server errors)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
