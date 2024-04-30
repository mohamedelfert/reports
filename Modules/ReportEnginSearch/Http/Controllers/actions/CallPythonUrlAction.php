<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use Exception;
use GuzzleHttp\Client;

class CallPythonUrlAction
{
    public function execute($data, $path)
    {
        // Create a Guzzle client
        $client = new Client();

        // Send POST request to the Flask API
        try {
            $overall_report = $client->post(env('BASE_URL') . $path, $data);

            // Check for a successful status code (HTTP 200)
            if ($overall_report->getStatusCode() === 200) {
                // Return the records received from the Flask API as a JSON response
                return $overall_report->getBody();
            }

            return null;
        } catch (Exception $e) {
            // Handle exceptions (e.g., connection issues, server errors)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
