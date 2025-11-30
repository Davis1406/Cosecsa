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
use App\Http\Controllers\ExamsController;


// Route::get('/', [AuthController::class,'login'])->name('login');
// Route::post('login', [AuthController::class,'AuthLogin']);
// Route::get('logout', [AuthController::class,'logout']);

Route::get('/', [AuthController::class,'login'])->name('login');
Route::post('login', [AuthController::class,'AuthLogin']);
Route::get('logout', [AuthController::class,'logout']);
Route::get('forget-password', [AuthController::class,'forgetpassword']);
Route::post('forget-password', [AuthController::class,'PostForgetPassword']);
Route::get('reset/{token}', [AuthController::class,'ResetPassword']);
Route::post('reset/{token}', [AuthController::class,'PostReset']);

// ADD THESE NEW ROUTES:
// Route::get('select-role', [AuthController::class, 'showRoleSelection'])->middleware('auth')->name('select.role');
// Route::post('select-role', [AuthController::class, 'selectRole'])->middleware('auth');
// Route::post('switch-role', [AuthController::class, 'switchRole'])->middleware('auth')->name('switch.role');

Route::get('select-role', [AuthController::class, 'showRoleSelection'])->name('select.role');
Route::post('select-role', [AuthController::class, 'selectRole']);


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
Route::post('admin/exams/view_examiner/{id}', [ExamsController::class, 'view']);

// Route::get('admin/exams/view_examiner/{id}',  [ExamsController::class,'view'])->name('examiner.view');
Route::get('admin/exams/edit_examiner/{id}', [ExamsController::class,'edit']);
Route::post('admin/exams/edit_examiner/{id}', [ExamsController::class,'update'])->name('examiner.update');
Route::get('admin/exams/examiner-confirmation', [ExamsController::class, 'ExaminerconfirmationView'])->name('examiner.examiner_confirmation');
Route::get('admin/exams/delete/{id}', [ExamsController::class,'delete']);
Route::get('admin/exams/exam_results', [ExamsController::class,'adminResults']);
Route::get('admin/exams/gs_results', [ExamsController::class,'gsResults']);
Route::get('admin/exams/station_results/{candidate_id}/{station_id}', [ExamsController::class, 'viewCandidateStationResult']);
Route::get('admin/exams/gs_station_results/{candidate_id}/{station_id}', [ExamsController::class, 'viewGsStationResult']);
// FCS Programme Results
    Route::get('admin/exams/fcs_cardiothoracic_results', [ExamsController::class, 'cardiothoracicResults']);
    Route::get('admin/exams/fcs_urology_results', [ExamsController::class, 'urologyResults']);
    Route::get('admin/exams/fcs_paediatric_results', [ExamsController::class, 'paediatricResults']);
    Route::get('admin/exams/fcs_ent_results', [ExamsController::class, 'entResults']);
    Route::get('admin/exams/fcs_plastic_surgery_results', [ExamsController::class, 'plasticSurgeryResults']);
    Route::get('admin/exams/fcs_neurosurgery_results', [ExamsController::class, 'neurosurgeryResults']);
    Route::get('admin/exams/fcs_orthopaedics_results', [ExamsController::class, 'orthopaedicsResults']);
    Route::get('admin/exams/fcs_paediatric_ortho_results', [ExamsController::class, 'paediatricOrthopaedicsResults']);

// FCS Station Results
    Route::get('admin/exams/fcs-station-results/{candidate_id}/{station_id}/{exam_format}/{table}', [ExamsController::class, 'viewFcsStationResults']);


// Show attendance confirmation page (GET)
Route::get('admin/exams/confirm-attendance/{examiner_id}', [ExamsController::class, 'showAttendanceConfirmation'])->name('exams.confirm.attendance');
// Register attendance via Form (POST) - with CSRF protection
Route::post('admin/exams/confirm-attendance-registration/{examiner_id}', [ExamsController::class, 'confirmAttendanceRegistration'])->name('exams.register.attendance');
// Add these routes to your routes/web.php file (or wherever your admin routes are defined)
Route::get('admin/exams/visual_report', [ExamsController::class, 'generateVisualReport'])->name('admin.exams.visual_report');
Route::get('admin/exams/export_report_csv', [ExamsController::class, 'exportReportCSV'])->name('admin.exams.export_report_csv');



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

    // Separate routes for question-based and FCS results viewing
    Route::get('examiner/view_results/{candidate_id}/{station_id}', [CandidatesController::class, 'viewCandidateResults'])->name('examiner.view.results');
    Route::get('examiner/view_fcs_results/{candidate_id}', [CandidatesController::class, 'viewFcsResults'])->name('examiner.view.fcs.results');

    Route::get('examiner/results', [CandidatesController::class,'results']);

    // Separate resubmit routes for question-based and FCS
    Route::get('examiner/resubmit/{candidate_id}/{station_id}', [CandidatesController::class, 'resubmit'])->name('examiner.resubmit');
    Route::post('examiner/resubmit/{candidate_id}/{station_id}', [CandidatesController::class, 'updateEvaluation'])->name('candidateform.update');

    Route::post('examiner/examiner_form', [CandidatesController::class, 'storeEvaluation'])->name('examiner.add');
    Route::post('examiner/general_surgery', [CandidatesController::class, 'storegsEvaluation'])->name('gs.add');
    Route::get('examiner/profile_settings', [ExamsController::class, 'examinerProfile'])->name('examiner.profile');
    Route::post('examiner/password/update', [ExamsController::class, 'examinerChangePassword'])->name('examiner.password.update');
    Route::get('/get-candidates', [CandidatesController::class, 'getGsCandidatesByGroup']);
    Route::get('/get-mcs-candidates/{groupId}', [CandidatesController::class, 'getMcsCandidatesByGroup']);
    Route::get('/get-gs-candidates/{groupId}', [CandidatesController::class, 'getGsCandidatesByGroup']);
    Route::get('examiner/edit_info/{id}', [ExamsController::class, 'examinerEdit'])->name('examiner.edit');
    Route::post('examiner/edit_info/{id}', [ExamsController::class, 'examinerUpdate'])->name('examiner.selfUpdate');
    Route::get('examiner/badge', [ExamsController::class, 'examinerBadge'])->name('examiner.badge');

    // Exam type selection pages
    Route::get('examiner/cardiothoracic', [CandidatesController::class, 'cardiothoracicSelection'])->name('examiner.cardiothoracic');
    Route::get('examiner/urology', [CandidatesController::class, 'urologySelection'])->name('examiner.urology');
    Route::get('examiner/paediatric', [CandidatesController::class, 'paediatricSelection'])->name('examiner.paediatric');
    Route::get('examiner/orthopaedic', [CandidatesController::class, 'orthopaedicSelection'])->name('examiner.orthopaedic');
    Route::get('examiner/ent', [CandidatesController::class, 'entSelection'])->name('examiner.ent');
    Route::get('examiner/plastic_surgery', [CandidatesController::class, 'plasticSurgerySelection'])->name('examiner.plastic_surgery');
    Route::get('examiner/neurosurgery', [CandidatesController::class, 'neurosurgerySelection'])->name('examiner.neurosurgery');
    Route::get('examiner/paediatric_orthopaedics', [CandidatesController::class, 'paediatricOrthopaedicsSelection'])->name('examiner.paediatric_orthopaedics');

    // Exam forms - Clinical
    Route::get('examiner/cardiothoracic/clinical', [CandidatesController::class, 'cardiothoracicClinicalForm'])->name('examiner.cardiothoracic.clinical');
    Route::get('examiner/urology/clinical', [CandidatesController::class, 'urologyClinicalForm'])->name('examiner.urology.clinical');
    Route::get('examiner/paediatric/clinical', [CandidatesController::class, 'paediatricClinicalForm'])->name('examiner.paediatric.clinical');
    Route::get('examiner/ent/clinical', [CandidatesController::class, 'entClinicalForm'])->name('examiner.ent.clinical');
    Route::get('examiner/plastic_surgery/clinical', [CandidatesController::class, 'plasticSurgeryClinicalForm'])->name('examiner.plastic_surgery.clinical');
    Route::get('examiner/neurosurgery/clinical', [CandidatesController::class, 'neurosurgeryClinicalForm'])->name('examiner.neurosurgery.clinical');
    Route::get('examiner/orthopaedic/clinical', [CandidatesController::class, 'orthopaedicClinicalForm'])->name('examiner.orthopaedic.clinical');
    Route::get('examiner/paediatric_orthopaedics/clinical', [CandidatesController::class, 'paediatricOrthopaedicsClinicalForm'])->name('examiner.paediatric_orthopaedics.clinical');

    // Exam forms - Viva
    Route::get('examiner/cardiothoracic/viva', [CandidatesController::class, 'cardiothoracicVivaForm'])->name('examiner.cardiothoracic.viva');
    Route::get('examiner/urology/viva', [CandidatesController::class, 'urologyVivaForm'])->name('examiner.urology.viva');
    Route::get('examiner/paediatric/viva', [CandidatesController::class, 'paediatricVivaForm'])->name('examiner.paediatric.viva');
    Route::get('examiner/ent/viva', [CandidatesController::class, 'entVivaForm'])->name('examiner.ent.viva');
    Route::get('examiner/plastic_surgery/viva', [CandidatesController::class, 'plasticSurgeryVivaForm'])->name('examiner.plastic_surgery.viva');
    Route::get('examiner/orthopaedic/viva', [CandidatesController::class, 'orthopaedicVivaForm'])->name('examiner.orthopaedic.viva');
    Route::get('examiner/neurosurgery/viva', [CandidatesController::class, 'neurosurgeryVivaForm'])->name('examiner.neurosurgery.viva');
    Route::get('examiner/paediatric_orthopaedics/viva', [CandidatesController::class, 'paediatricOrthopaedicsVivaForm'])->name('examiner.paediatric_orthopaedics.viva');

    // FCS Resubmit routes
    Route::get('examiner/fcs-resubmit/{candidate_id}', [CandidatesController::class, 'showFcsResubmitSelection'])->name('examiner.fcs.resubmit.selection');
    Route::get('examiner/fcs-resubmit/{candidate_id}/{exam_format}', [CandidatesController::class, 'showFcsResubmitForm'])->name('examiner.fcs.resubmit.form');
    Route::post('examiner/fcs-resubmit/{candidate_id}', [CandidatesController::class, 'updateEvaluationFcs'])->name('examiner.update_evaluation.fcs');

    // Form submission routes
    Route::post('examiner/submit-exam', [CandidatesController::class, 'submitExamEvaluation'])->name('examiner.submit.exam');

    // Get candidates by group for specific exam
    Route::get('get-exam-candidates/{examType}/{groupId}', [CandidatesController::class, 'getExamCandidatesByGroup']);

});

Route::get('examiner/confirm-attendance/{examiner_id}', [ExamsController::class, 'showExaminerAttendanceConfirmation'])->name('examiner.confirm.attendance');
Route::post('examiner/confirm-attendance-registration/{examiner_id}', [ExamsController::class, 'confirmExaminerAttendanceRegistration'])->name('examiner.register.attendance');

