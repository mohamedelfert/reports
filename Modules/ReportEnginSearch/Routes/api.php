<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\ReportEnginSearch\Http\Controllers\ReportEnginSearchController;

Route::post('/reports/get-reports', [ReportEnginSearchController::class, 'reports']);
