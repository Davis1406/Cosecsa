<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\ProgrammesController;
use App\Http\Controllers\HospitalProgrammesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TraineeController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\CountryRepsController;               
use App\Http\Controllers\FellowsController;   
use App\Http\Controllers\PromotionController;  
use App\Http\Controllers\MembersController; 
use App\Http\Controllers\FellowsModel;
use App\Http\Controllers\ExamsController;


Route::get('/', [AuthController::class,'login'])->name('login');
Route::post('login', [AuthController::class,'AuthLogin']);
Route::get('logout', [AuthController::class,'logout']);
Route::get('forget-password', [AuthController::class,'forgetpassword']);
Route::post('forget-password', [AuthController::class,'PostForgetPassword']);
Route::get('reset/{token}', [AuthController::class,'ResetPassword']);
Route::post('reset/{token}', [AuthController::class,'PostReset']);


// Admin Routes
Route::group(['middleware' => 'admin'], function(){

    Route::get('admin/dashboard', [DashboardController::class,'dashboard']);
    Route::get('admin/list ', [AdminController::class,'list']);
    Route::get('admin/add ', [AdminController::class,'add']);
    Route::post('admin/add ', [AdminController::class,'insert']);
    Route::get('admin/edit/{id} ', [AdminController::class,'edit']);
    Route::post('admin/edit/{id} ', [AdminController::class,'update']);
    Route::get('admin/delete/{id} ', [AdminController::class,'delete']);

    //Hospital Routes;
    Route::get('admin/hospital/list ', [HospitalController::class,'hospital']);
    Route::get('admin/hospital/add ',  [HospitalController::class,'add']);
    Route::post('admin/hospital/add', [HospitalController::class,'insert']);
    Route::get('admin/hospital/view_hospital/{id}', [HospitalController::class,'view']);
    Route::get('admin/hospital/edit_hospital/{id} ', [HospitalController::class,'edit']);
    Route::post('admin/hospital/edit_hospital/{id} ', [HospitalController::class,'update']);
    Route::get('admin/hospital/delete/{id} ', [HospitalController::class,'delete']);
    
   //Programmes Routes
   Route::get('admin/programmes/list', [ProgrammesController::class, 'list']);
   Route::get('admin/programmes/add_programmes', [ProgrammesController::class, 'add']);
   Route::post('admin/programmes/add_programmes', [ProgrammesController::class, 'insert']);
   Route::get('admin/programmes/edit_programmes/{id}', [ProgrammesController::class, 'edit']);
   Route::post('admin/programmes/edit_programmes/{id}', [ProgrammesController::class, 'update']);
   Route::get('admin/programmes/delete/{id} ', [ProgrammesController::class,'delete']);

 //HospitalProgrammes Routes
  Route::get('admin/hospitalprogrammes/list', [HospitalProgrammesController::class, 'list']);
  Route::get('admin/hospitalprogrammes/add', [HospitalProgrammesController::class, 'add']);
  Route::post('admin/hospitalprogrammes/add', [HospitalProgrammesController::class, 'insert']);
  Route::get('admin/hospitalprogrammes/import',  [HospitalProgrammesController::class,'import']);
  Route::post('admin/hospitalprogrammes/import', [HospitalProgrammesController::class, 'importData'])->name('hospitalprogrammes.import.data');
  Route::get('admin/hospitalprogrammes/edit/{id}', [HospitalProgrammesController::class, 'edit']);
  Route::post('admin/hospitalprogrammes/edit/{id} ', [HospitalProgrammesController::class,'update']);
  Route::get('admin/hospitalprogrammes/delete/{id} ', [HospitalProgrammesController::class,'delete']);

  //Profile Settings
  Route::get('profile/change_password', [UserController::class, 'changePassword']);
  Route::post('profile/change_password', [UserController::class, 'updatePassword']);

  //Trainees Route
  Route::get('admin/associates/trainees/trainees', [TraineeController::class,'list']);
  Route::get('admin/associates/trainees/add',  [TraineeController::class,'add']);
  Route::post('admin/associates/trainees/add', [TraineeController::class,'insert'])->name('admin.associates.trainees.add');
  Route::get('admin/associates/trainees/import',  [TraineeController::class,'import']);
  Route::post('admin/associates/trainees/import', [TraineeController::class, 'importData'])->name('trainees.import.data');
  Route::get('admin/associates/trainees/view/{id}',  [TraineeController::class,'view'])->name('trainees.view');
  Route::get('admin/associates/trainees/edit/{id} ', [TraineeController::class,'edit']);
  Route::post('admin/associates/trainees/edit/{id} ', [TraineeController::class,'update']);
  Route::get('admin/associates/trainees/delete/{id}', [TraineeController::class,'delete']);

  //Candidates Route
  Route::get('admin/associates/candidates/list', [CandidatesController::class,'list']);
  Route::get('admin/associates/candidates/add',  [CandidatesController::class,'add']);
  Route::post('admin/associates/candidates/add', [CandidatesController::class,'insert'])->name('admin.associates.candidates.add');
  Route::get('admin/associates/candidates/import',  [CandidatesController::class,'import']);
  Route::post('admin/associates/candidates/import', [CandidatesController::class, 'importData'])->name('candidates.import.data');
  Route::get('admin/associates/candidates/view/{id}',  [CandidatesController::class,'view'])->name('candidates.view');
  Route::get('admin/associates/candidates/edit/{id} ', [CandidatesController::class,'edit']);
  Route::post('admin/associates/candidates/edit/{id} ', [CandidatesController::class,'update']);
  Route::get('admin/associates/candidates/delete/{id}', [CandidatesController::class,'delete']);

//Trainers Route
Route::get('admin/associates/trainers/list', [TrainerController::class,'list']);
Route::get('admin/associates/trainers/add',  [TrainerController::class,'add']);
Route::post('admin/associates/trainers/add', [TrainerController::class,'insert'])->name('admin.associates.trainers.add');
Route::get('admin/associates/trainers/import',  [TrainerController::class,'import']);
Route::post('admin/associates/trainers/import', [TrainerController::class, 'importData'])->name('trainers.import.data');
Route::get('admin/associates/trainers/view/{id}',  [TrainerController::class,'view'])->name('trainers.view');
Route::get('admin/associates/trainers/edit/{id} ', [TrainerController::class,'edit']);
Route::post('admin/associates/trainers/edit/{id} ', [TrainerController::class,'update']);
Route::get('admin/associates/trainers/delete/{id}', [TrainerController::class,'delete']);


//CR's Route
Route::get('admin/associates/reps/list', [CountryRepsController::class,'list']);
Route::get('admin/associates/reps/add',  [CountryRepsController::class,'add']);
Route::post('admin/associates/reps/add', [CountryRepsController::class,'insert'])->name('admin.associates.reps.add');
Route::get('admin/associates/reps/import',  [CountryRepsController::class,'import']);
Route::post('admin/associates/reps/import', [CountryRepsController::class, 'importData'])->name('reps.import.data');
Route::get('admin/associates/reps/view/{id}',  [CountryRepsController::class,'view'])->name('reps.view');
Route::get('admin/associates/reps/edit/{id} ', [CountryRepsController::class,'edit']);
Route::post('admin/associates/reps/edit/{id} ', [CountryRepsController::class,'update']);
Route::get('admin/associates/reps/delete/{id}', [CountryRepsController::class,'delete']);


//Fellows's Route
Route::get('admin/associates/fellows/list', [FellowsController::class,'list']);
Route::get('admin/associates/fellows/add',  [FellowsController::class,'add']);
Route::post('admin/associates/fellows/add', [FellowsController::class,'insert'])->name('admin.associates.fellows.add');
Route::get('admin/associates/fellows/import_fellows', [FellowsController::class,'import']);
Route::post('admin/associates/fellows/import', [FellowsController::class, 'importFellows'])->name('fellows.import.data');
Route::get('admin/associates/fellows/view/{id}',  [FellowsController::class,'view'])->name('fellows.view');
Route::get('admin/associates/fellows/edit/{id} ', [FellowsController::class,'edit']);
Route::post('admin/associates/fellows/edit/{id} ', [FellowsController::class,'update']);
Route::get('admin/associates/fellows/delete/{id}', [FellowsController::class,'delete']);


//Members's Route
Route::get('admin/associates/members/list', [MembersController::class,'list']);
Route::get('admin/associates/members/add',  [MembersController::class,'add']);
Route::post('admin/associates/members/add', [MembersController::class,'insert'])->name('admin.associates.members.add');
Route::get('admin/associates/members/import_members', [MembersController::class,'import']);
Route::post('admin/associates/members/import', [MembersController::class, 'importMembers'])->name('members.import.data');
Route::get('admin/associates/members/view/{id}',  [MembersController::class,'view'])->name('members.view');
Route::get('admin/associates/members/edit/{id}', [MembersController::class,'edit']);
Route::post('admin/associates/members/edit/{id}', [MembersController::class,'update']);
Route::get('admin/associates/members/delete/{id}', [MembersController::class,'delete']);

//Examiners's Route
Route::get('admin/exams/examiners', [ExamsController::class,'list']);
Route::get('admin/exams/add_examiner',  [ExamsController::class,'add']);
Route::post('admin/exams/add_examiner', [ExamsController::class,'insert'])->name('examiners.add');
Route::post('admin/exams/import', [ExamsController::class, 'importExaminers'])->name('exams.import.data');;
Route::get('admin/exams/import', [ExamsController::class, 'import']);
Route::get('admin/exams/view_examiner/{id}',  [ExamsController::class,'view'])->name('examiner.view');
Route::get('admin/exams/edit_examiner/{id}', [ExamsController::class,'edit']);
Route::post('admin/exams/edit_examiner/{id}', [ExamsController::class,'update'])->name('examiner.update');
Route::get('admin/exams/delete/{id}', [ExamsController::class,'delete']);
Route::get('admin/exams/exam_results', [ExamsController::class,'adminResults']);
Route::get('admin/exams/gs_results', [ExamsController::class,'gsResults']);
Route::get('admin/exams/station_results/{candidate_id}/{station_id}', [ExamsController::class, 'viewCandidateStationResult']);
Route::get('admin/exams/gs_station_results/{candidate_id}/{station_id}', [ExamsController::class, 'viewGsStationResult']);



//Promotion Route
Route::get('admin/associates/promotion/promote_trainees', [PromotionController::class,'promotion']);
Route::get('admin/associates/promotion/promote_candidates', [PromotionController::class,'cadidatesPromotion']);
Route::post('admin/associates/promotion/promote_trainees', [PromotionController::class,'update']);


});


Route::group(['middleware' => 'trainee'], function(){

    Route::get('trainee/dashboard ', [DashboardController::class,'dashboard']);

});

//Examiner Routes 

Route::group(['middleware' => 'examiner'], function(){

  Route::get('examiner/dashboard ', [DashboardController::class,'examinerform'])->name('dashboard');
  Route::get('examiner/examiner_form ', [CandidatesController::class,'mcsexaminerform']);
  Route::get('examiner/general_surgery ', [CandidatesController::class,'gsexaminerform']);
  Route::get('examiner/view_results/{candidate_id}/{station_id}', [CandidatesController::class, 'viewCandidateResults']);
  Route::get('examiner/results', [CandidatesController::Class,'results']);
  Route::get('examiner/resubmit/{candidate_id}/{station_id}', [CandidatesController::class, 'resubmit'])->name('examiner.resubmit');
  Route::post('examiner/resubmit/{candidate_id}/{station_id}', [CandidatesController::class, 'updateEvaluation'])->name('candidateform.update');
  Route::post('examiner/examiner_form', [CandidatesController::class, 'storeEvaluation'])->name('examiner.add');
  Route::post('examiner/general_surgery', [CandidatesController::class, 'storegsEvaluation'])->name('gs.add');
  Route::get('examiner/change_password', [ExamsController::class, 'changePassword']);
  Route::post('examiner/change_password', [ExamsController::class, 'updatePassword']);
  Route::get('/get-candidates/{groupId}', [CandidatesController::class, 'getGsCandidatesByGroup']);
  Route::get('/get-mcs-candidates/{groupId}', [CandidatesController::class, 'getMcsCandidatesByGroup']);

});

