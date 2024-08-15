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


Route::get('/', [AuthController::class,'login']);
Route::post('login', [AuthController::class,'AuthLogin']);
Route::get('logout', [AuthController::class,'logout']);
Route::get('forget-password', [AuthController::class,'forgetpassword']);
Route::post('forget-password', [AuthController::class,'PostForgetPassword']);
Route::get('reset/{token}', [AuthController::class,'ResetPassword']);
Route::post('reset/{token}', [AuthController::class,'PostReset']);


// Route::get('admin/dashboard', function () {
//     return view('admin.dashboard');
// });

// Route::get('admin/list', function () {
//     return view('admin.list');
// });


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

});

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

// Fellows Route
Route::get('admin/associates/fellows_members/coming_soon', [FellowsController::class,'coming_soon']);



Route::group(['middleware' => 'trainee'], function(){

    Route::get('trainee/dashboard ', [DashboardController::class,'dashboard']);

});
