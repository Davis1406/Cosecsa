<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;

/*
|--------------------------------------------------------------------------
| API Routes — COSECSA Examiner Mobile App
|--------------------------------------------------------------------------
*/

// ── Public ────────────────────────────────────────────────────────────────────
Route::post('/examiner/login',  [AuthController::class, 'login']);

// ── Authenticated (Sanctum token) ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/examiner/me',     [AuthController::class, 'me']);
    Route::post('/examiner/logout', [AuthController::class, 'logout']);

    // Exam data
    Route::get('/exam/groups',                              [ExamController::class, 'getGroups']);
    Route::get('/exam/candidates/{examType}/{groupId}',     [ExamController::class, 'getCandidates']);

    // Mark submission
    Route::post('/exam/submit',  [ExamController::class, 'submitMarks']);

    // Offline batch sync
    Route::post('/exam/sync',    [ExamController::class, 'syncBatch']);

    // Results
    Route::get('/exam/my-results', [ExamController::class, 'getMyResults']);
});
