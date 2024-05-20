<?php

namespace Modules\ReportEnginSearch\Http\Controllers\actions\meetings;

use App\User;
use Exception;
use Modules\ReportEnginSearch\Http\Controllers\actions\CallPythonUrlAction;
use Yajra\DataTables\Facades\DataTables;

class MeetingsOverallReportAction
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

        // Prepare request data for the Flask API
        $data = [
            'json' => [
                'slug' => $request->input('slug'),
                'type' => $request->input('type'),
                'subdomain_id' => $request->input('subdomain_id'),
                'users_ids' => $request->input('users_ids') ?? null,
                'groups_ids' => $request->input('groups_ids') ?? null,
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]
        ];

        $path = $request->input('slug');

        try {
            $meetings_overall = collect(json_decode($this->callPythonUrlAction->execute($path, $data), true));

//            dd($meetings_overall);

            // get user ids from the report
            $user_ids = $meetings_overall->keys();

//            dd($user_ids);

            // filter users with report data
            $filtered_users = $users->whereIn('id', $user_ids);

            // Return the users with report data
            $data = Datatables::of($filtered_users)
                ->addColumn('report', function ($user) use ($meetings_overall) {
                    return $meetings_overall[$user->id];
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
