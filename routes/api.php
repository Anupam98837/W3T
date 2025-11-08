<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\MailerController;
use App\Http\Controllers\API\CourseModuleController;
use App\Http\Controllers\API\BatchController;
use App\Http\Controllers\API\BatchInstructorController;
use App\Http\Controllers\API\QuizzController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\StudyMaterialController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Auth Routes

Route::post('/auth/login',  [UserController::class, 'login']);
Route::post('/auth/logout', [UserController::class, 'logout'])
    ->middleware('checkRole');
Route::get('/auth/check',   [UserController::class, 'authenticateToken']);

// Users Routes 

Route::middleware(['checkRole:instructor,author,admin,super_admin'])->group(function () {
    Route::get('/users',      [UserController::class, 'index']);   
    Route::get('/users/all',  [UserController::class, 'all']);     
    Route::get('/users/{id}', [UserController::class, 'show']);    
});

Route::middleware(['checkRole:admin,super_admin'])->group(function () {
    Route::post('/users',                        [UserController::class, 'store']);      
    Route::match(['put','patch'], '/users/{id}', [UserController::class, 'update']);     
    Route::delete('/users/{id}',                 [UserController::class, 'destroy']);      
    Route::post('/users/{id}/restore',           [UserController::class, 'restore']);     
    Route::delete('/users/{id}/force',           [UserController::class, 'forceDelete']);   
    Route::patch('/users/{id}/password',         [UserController::class, 'updatePassword']);
    Route::post('/users/{id}/image',             [UserController::class, 'updateImage']);   
});


// Course Routes 

Route::middleware('checkRole:admin,super_admin')->group(function () {
    // Courses
    Route::get   ('/courses',              [CourseController::class, 'index']);
    Route::get   ('/courses/{course}',     [CourseController::class, 'show']);    // {id|uuid}
    Route::post  ('/courses',              [CourseController::class, 'store']);
    Route::put   ('/courses/{course}',     [CourseController::class, 'update']);
    Route::patch ('/courses/{course}',     [CourseController::class, 'update']);
    Route::delete('/courses/{course}',     [CourseController::class, 'destroy']);
    Route::get('/courses/{course}/view', [CourseController::class, 'viewCourse']);

    // Featured media
    Route::get   ('/courses/{course}/media',           [CourseController::class, 'mediaIndex']);
    Route::post  ('/courses/{course}/media',           [CourseController::class, 'mediaUpload']);   // multipart OR JSON {url}
    Route::post  ('/courses/{course}/media/reorder',   [CourseController::class, 'mediaReorder']);  // {ids:[...]} or {orders:{id:pos}}
    Route::delete('/courses/{course}/media/{media}',   [CourseController::class, 'mediaDestroy']);  // {id|uuid}
});


Route::middleware('checkRole:admin,super_admin')->group(function () {
    Route::get(   '/mailer',             [MailerController::class, 'index']);
    Route::post(  '/mailer',             [MailerController::class, 'store']);
    Route::get(   '/mailer/{id}',        [MailerController::class, 'show']);
    Route::put(   '/mailer/{id}',        [MailerController::class, 'update']);
    Route::patch( '/mailer/{id}',        [MailerController::class, 'update']);
    Route::put(   '/mailer/{id}/default',[MailerController::class, 'setDefault']);
    Route::delete('/mailer/{id}',        [MailerController::class, 'destroy']);
});

Route::middleware(['checkRole:admin,super_admin'])->group(function () {
    Route::get   ('/course-modules',                      [CourseModuleController::class, 'index']);
    Route::get   ('/course-modules/bin',                  [CourseModuleController::class, 'bin']);              // NEW
    Route::get   ('/course-modules/{idOrUuid}',           [CourseModuleController::class, 'show']);
    Route::post  ('/course-modules',                      [CourseModuleController::class, 'store']);
    Route::match(['put','patch'], '/course-modules/{idOrUuid}', [CourseModuleController::class, 'update']);

    // Archive toggles (NEW)
    Route::post  ('/course-modules/{idOrUuid}/archive',   [CourseModuleController::class, 'archive']);
    Route::post  ('/course-modules/{idOrUuid}/unarchive', [CourseModuleController::class, 'unarchive']);

    // Delete / Restore / Force delete
    Route::delete('/course-modules/{idOrUuid}',           [CourseModuleController::class, 'destroy']);         // soft delete to Bin
    Route::post  ('/course-modules/{idOrUuid}/restore',   [CourseModuleController::class, 'restore']);         // NEW
    Route::delete('/course-modules/{idOrUuid}/force',     [CourseModuleController::class, 'forceDestroy']);    // NEW

    // DnD ordering (existing)
    Route::post  ('/course-modules/reorder',              [CourseModuleController::class, 'reorder']);
});


Route::middleware('checkRole:admin,super_admin')->group(function () {
    // Batches
    Route::get   ('/batches',                    [BatchController::class, 'index']);
    Route::get   ('/batches/{idOrUuid}',         [BatchController::class, 'show']);
    Route::post  ('/batches',                    [BatchController::class, 'store']);
    Route::match(['put','patch'], '/batches/{idOrUuid}', [BatchController::class, 'update']);
    Route::delete('/batches/{idOrUuid}',         [BatchController::class, 'destroy']);
    Route::post  ('/batches/{idOrUuid}/restore', [BatchController::class, 'restore']);
    Route::patch ('/batches/{idOrUuid}/archive', [BatchController::class, 'archive']);

    // Existing students (for the toggle modal)
    Route::get   ('/batches/{idOrUuid}/students',          [BatchController::class, 'studentsIndex']);
    Route::post  ('/batches/{idOrUuid}/students/toggle',   [BatchController::class, 'studentsToggle']);

    //Instructor Routes 
    Route::get   ('/batches/{batch}/instructors',            [BatchController::class,'instructorsIndex']);
    Route::post  ('/batches/{batch}/instructors/toggle',     [BatchController::class,'instructorsToggle']);
    Route::patch ('/batches/{batch}/instructors/update',     [BatchController::class,'instructorsUpdate']);

    // CSV upload
    Route::post  ('/batches/{idOrUuid}/students/upload-csv', [BatchController::class, 'studentsUploadCsv']);
});


// Quiz & Question Routes 

Route::middleware('checkRole:admin,super_admin')
    ->prefix('quizz')->name('quizz.')
    ->group(function () {
 
    // ===== Quizzes list/create =====
    Route::get('/',   [QuizzController::class, 'index'])->name('index');
    Route::post('/',  [QuizzController::class, 'store'])->name('store');
 
    // ===== Questions (place BEFORE any /{key} routes) =====
    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/',            [QuestionController::class, 'index'])->name('index');   // GET /api/quizz/questions?quiz=...
        Route::post('/',           [QuestionController::class, 'store'])->name('store');
        Route::get('/{key}',       [QuestionController::class, 'show'])->name('show');
        Route::match(['put','patch'],'/{key}', [QuestionController::class, 'update'])->name('update');
        Route::delete('/{key}',    [QuestionController::class, 'destroy'])->name('destroy');
    });
 
    // ===== Status & lifecycle =====
    Route::patch ('/{key}/status',  [QuizzController::class, 'updateStatus'])->name('status');
    Route::patch ('/{key}/restore', [QuizzController::class, 'restore'])->name('restore');
    Route::delete('/{key}',         [QuizzController::class, 'destroy'])->name('destroy');
    Route::delete('/{key}/force',   [QuizzController::class, 'forceDelete'])->name('force');
 
    // ===== Optional notes =====
    Route::get ('/{key}/notes',     [QuizzController::class, 'listNotes'])->name('notes.list');
    Route::post ('/{key}/notes',    [QuizzController::class, 'addNote'])->name('notes.add');
 
    // ===== Show/Update generic (MUST be last) =====
    Route::get ('/{key}',           [QuizzController::class, 'show'])->name('show');
    Route::match(['put','patch'],'/{key}', [QuizzController::class, 'update'])->name('update');
});


// Assignments (admin, super_admin, instructor)
Route::middleware('checkRole:admin,super_admin,instructor')->group(function () {
    // Generic assignment endpoints
    Route::get   ('/assignments',                   [AssignmentController::class, 'index']);
    Route::get   ('/assignments/{assignment}',      [AssignmentController::class, 'show']);     // {id|uuid|slug}
    Route::post  ('/assignments',                   [AssignmentController::class, 'store']);
    Route::match(['put','patch'], '/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::delete('/assignments/{assignment}',      [AssignmentController::class, 'destroy']);

    // Optional: assignments under a course (list/create) — keeps parity with courses routes
    Route::get   ('/courses/{course}/assignments',          [AssignmentController::class, 'index']);
    Route::post  ('/courses/{course}/assignments',          [AssignmentController::class, 'store']);
});

// Study Material Routes 

Route::middleware('checkRole:admin,super_admin')->group(function () {
    Route::get   ('/study-materials',                 [StudyMaterialController::class, 'index']);
    Route::post  ('/study-materials',                 [StudyMaterialController::class, 'store']);
    Route::patch ('/study-materials/{id}',            [StudyMaterialController::class, 'update']);
    Route::delete('/study-materials/{id}',            [StudyMaterialController::class, 'destroy']);

    // View endpoints
    Route::get   ('/study-materials/show/{uuid}',     [StudyMaterialController::class, 'showByUuid']);
    Route::get   ('/study-materials/stream/{uuid}/{fileId}', [StudyMaterialController::class, 'streamInline']);
});
