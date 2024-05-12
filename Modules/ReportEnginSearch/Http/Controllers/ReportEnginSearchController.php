<?php

namespace Modules\ReportEnginSearch\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\ReportEnginSearch\Http\Controllers\actions\AgentsReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\MeetingsByAgentReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\MeetingsByTeamReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\MeetingsOverallReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\MeetingsReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\OverAllActivitiesAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\PerformanceReportAction;
use Modules\ReportEnginSearch\Http\Controllers\actions\ScheduledMeetingsReportAction;
use Modules\ReportEnginSearch\Http\Requests\ReportRequest;

class ReportEnginSearchController extends Controller
{
    public function __construct(
        OverAllActivitiesAction       $overAllActivitiesAction,
        AgentsReportAction            $agentsReportAction,
        PerformanceReportAction       $performanceReportAction,
        MeetingsOverallReportAction   $meetingsOverallReportAction,
        MeetingsReportAction          $meetingsReportAction,
        ScheduledMeetingsReportAction $scheduledMeetingsReportAction,
        MeetingsByAgentReportAction $meetingsByAgentReportAction,
        MeetingsByTeamReportAction $meetingsByTeamReportAction
    )
    {
        $this->overAllActivitiesAction = $overAllActivitiesAction;
        $this->agentsReportAction = $agentsReportAction;
        $this->performanceReportAction = $performanceReportAction;
        $this->meetingsOverallReportAction = $meetingsOverallReportAction;
        $this->meetingsReportAction = $meetingsReportAction;
        $this->scheduledMeetingsReportAction = $scheduledMeetingsReportAction;
        $this->meetingsByAgentReportAction = $meetingsByAgentReportAction;
        $this->meetingsByTeamReportAction = $meetingsByTeamReportAction;
    }


//    public function index(Request $request)
//    {
//        /*
//            1. Angular: they will send api request to laravel (PL: 5, CP: 3)
//            2. Laravel: will query users with (PL: 5, CP: 1)
//            3. Pluck Users IDs and Group IDs
//            4. Send request to PY
//        */
//        // (2) Get Users
//        $pl = $request->input('pl', 5);
//        $cp = $request->input('cp', 1);
//        $skip = $pl * ($cp - 1);
//        $users = User::skip($skip)->take($pl)->get();
//        // (3) Users IDs
//        $users_ids = with(clone $users)->pluck('id')->toArray();
//        $groups_ids = with(clone $users)->pluck('group_id')->toArray();
//        // (4) Python Request: Sub-domain ID, Users IDs, Start and End Dates
//        try {
//            $report_data = collect([]); // Should be replaced with PY Request
//            return Datatables::of($users)
//                ->addColumn('report', function ($user) use ($report_data) { // 100ms
//                    return with(clone $report_data)->filter(function ($user_report) use ($user) {
//                        return $user_report->user_id == $user->id;
//                    });
//                })
//                ->make(true);
//        } catch (Exception $e) {
//            // Handle exceptions (e.g., connection issues, server errors)
//            return response()->json(['error' => $e->getMessage()], 500);
//        }
//    }

    public function reports(ReportRequest $request)
    {
        switch ($request->input('slug')) {
            case('overall-activities'):
                $response = $this->overAllActivitiesAction->execute($request);
                break;
            case('agents-report'):
                $response = $this->agentsReportAction->execute($request);
                break;
            case('performance-report'):
                $response = $this->performanceReportAction->execute($request);
                break;
            case('meetings-overall'):
                $response = $this->meetingsOverallReportAction->execute($request);
                break;
            case('meetings'):
                $response = $this->meetingsReportAction->execute($request);
                break;
            case('scheduled-meetings'):
                $response = $this->scheduledMeetingsReportAction->execute($request);
                break;
            case('by-agent-report'):
                $response = $this->meetingsByAgentReportAction->execute($request);
                break;
            case('by-team-report'):
                $response = $this->meetingsByTeamReportAction->execute($request);
                break;
            default:
                $response = null;
                break;
        }

        return $response;
    }
}
