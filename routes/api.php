<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\MailerController;
use App\Http\Controllers\API\CourseModuleController;
use App\Http\Controllers\API\BatchController;
use App\Http\Controllers\API\BatchInstructorController;


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
    Route::get   ('/course-modules',                 [CourseModuleController::class, 'index']);
    Route::get   ('/course-modules/{idOrUuid}',      [CourseModuleController::class, 'show']);
    Route::post  ('/course-modules',                 [CourseModuleController::class, 'store']);
    Route::match(['put','patch'], '/course-modules/{idOrUuid}', [CourseModuleController::class, 'update']);
    Route::delete('/course-modules/{idOrUuid}',      [CourseModuleController::class, 'destroy']);

    // Optional but handy for drag-and-drop ordering in your UI:
    Route::post  ('/course-modules/reorder',         [CourseModuleController::class, 'reorder']);
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

    // CSV upload
    Route::post  ('/batches/{idOrUuid}/students/upload-csv', [BatchController::class, 'studentsUploadCsv']);
});

Route::prefix('batch-instructors')->group(function () {
    // Read endpoints (allow admin + instructor views)
    Route::middleware('check.role:admin,super_admin,instructor')->group(function () {
        Route::get('/', [BatchInstructorController::class, 'index']);
        Route::get('/instructors-of-batch', [BatchInstructorController::class, 'instructorsOfBatch']);
        Route::get('/batches-for-user', [BatchInstructorController::class, 'batchesForUser']);
    });

    // Write endpoints (restrict to admins)
    Route::middleware('check.role:admin,super_admin')->group(function () {
        Route::post('/toggle', [BatchInstructorController::class, 'toggle']);
        Route::post('/bulk-sync-for-user', [BatchInstructorController::class, 'bulkSyncForUser']);
        Route::post('/restore', [BatchInstructorController::class, 'restore']);
    });
});