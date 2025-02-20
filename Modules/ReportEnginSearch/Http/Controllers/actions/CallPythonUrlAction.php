<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions;

use Exception;
use GuzzleHttp\Client;

class CallPythonUrlAction
{
    public function execute($path, $data)
    {
        // Create a Guzzle client
        $client = new Client();

        $data['headers'] = [
            'Authorization' => 'Bearer ' . env('AUTHORIZATION_TOKEN')
        ];

        // Send POST request to the Flask API
        try {
            $reports = $client->post(env('BASE_URL') . $path, $data);

            // Check for a successful status code (HTTP 200)
            if ($reports->getStatusCode() === 200) {
                // Return the records received from the Flask API as a JSON response
                return $reports->getBody();
            }

            return null;
        } catch (Exception $e) {
            // Handle exceptions (e.g., connection issues, server errors)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
