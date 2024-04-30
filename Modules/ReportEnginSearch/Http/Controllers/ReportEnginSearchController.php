<?php

namespace Modules\ReportEnginSearch\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ReportEnginSearch\Http\Controllers\actions\AgentsReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\OverAllActivitiesAction;
use Modules\ReportEnginSearch\Http\Requests\ReportRequest;
use Yajra\DataTables\Facades\DataTables;

class ReportEnginSearchController extends Controller
{
    /**
     * Constructor function
     * @param OverAllActivitiesAction $overAllActivitiesAction
     * @param AgentsReportAction $agentsReportAction
     */
    public function __construct(
        OverAllActivitiesAction $overAllActivitiesAction,
        AgentsReportAction      $agentsReportAction

    )
    {
        $this->overAllActivitiesAction = $overAllActivitiesAction;
        $this->agentsReportAction = $agentsReportAction;
    }


    public function index(Request $request)
    {
        /*
            1. Angular: they will send api request to laravel (PL: 5, CP: 3)
            2. Laravel: will query users with (PL: 5, CP: 1)
            3. Pluck Users IDs and Group IDs
            4. Send request to PY
        */
        // (2) Get Users
        $pl = $request->input('pl', 5);
        $cp = $request->input('cp', 1);
        $skip = $pl * ($cp - 1);
        $users = User::skip($skip)->take($pl)->get();
        // (3) Users IDs
        $users_ids = with(clone $users)->pluck('id')->toArray();
        $groups_ids = with(clone $users)->pluck('group_id')->toArray();
        // (4) Python Request: Sub-domain ID, Users IDs, Start and End Dates
        try {
            $report_data = collect([]); // Should be replaced with PY Request
            return Datatables::of($users)
                ->addColumn('report', function ($user) use ($report_data) { // 100ms
                    return with(clone $report_data)->filter(function ($user_report) use ($user) {
                        return $user_report->user_id == $user->id;
                    });
                })
                ->make(true);
        } catch (Exception $e) {
            // Handle exceptions (e.g., connection issues, server errors)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reports(ReportRequest $request)
    {
        $authorizationToken = $request->bearerToken();

        if ($request->input('slug') && $request->input('slug') === 'agents-report') {
            $page_length = $request->input('page_length', 10);
            $current_page = $request->input('current_page', 1);
            $skip = $page_length * ($current_page - 1);
            $users = User::skip($skip)->take($page_length)->get();

            /// Get user IDs without keys and filter out null or empty values
            $users_ids = $users->pluck('id')->toArray();
            $groups_ids = $users->pluck('group_id')->toArray();
        } else {
            $users_ids = $request->input('users_ids');
            $groups_ids = $request->input('groups_ids');
        }

        // Prepare request data for the Flask API
        $data = [
            'json' => [
                'slug' => $request->input('slug'),
                'type' => $request->input('type'),
                "page_length" => $request->input('page_length'),
                "current_page" => $request->input('current_page'),
                'subdomain_id' => $request->input('subdomain_id'),
                'charts' => $request->input('charts'),
                'users_ids' => $users_ids ?? null,
                'groups_ids' => $groups_ids ?? null,
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $authorizationToken
            ]
        ];

        switch ($request->input('slug')) {
            case('overall-activities'):
                $response = $this->overAllActivitiesAction->execute($data);
                break;
            case('agents-report'):
                $response = $this->agentsReportAction->execute($data);
                break;
            default:
                $response = null;
                break;
        }

        return $response;
    }
}
