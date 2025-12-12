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
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\AssignmentController;
use App\Http\Controllers\API\AssignmentSubmissionController;
use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\API\NoticeController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\PrivilegeController;
use App\Http\Controllers\API\UserPrivilegeController;
use App\Http\Controllers\API\BatchMessageController;
use App\Http\Controllers\API\TopicController;
use App\Http\Controllers\API\CodingModuleController;
use App\Http\Controllers\API\CodingQuestionController;
use App\Http\Controllers\API\JudgeController;
use App\Http\Controllers\API\LandingPageController;
use App\Http\Controllers\API\CourseCategoryController;
use App\Http\Controllers\API\DashboardController;

// Auth Routes

Route::post('/auth/login',  [UserController::class, 'login']);
Route::post('/auth/logout', [UserController::class, 'logout'])
    ->middleware('checkRole');
Route::get('/auth/check',   [UserController::class, 'authenticateToken']);
Route::get('/auth/my-role', [UserController::class, 'getMyRole']);
Route::post('/auth/register', [UserController::class, 'register']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



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




Route::middleware('checkRole:admin,super_admin,student,instructor')->group(function () {
    // Courses
    Route::get('/courses/cards', [CourseController::class, 'listCourseBatchCards']);
    Route::post  ('/courses',              [CourseController::class, 'store']);
    Route::put   ('/courses/{course}',     [CourseController::class, 'update']);
    Route::patch ('/courses/{course}',     [CourseController::class, 'update']);

    Route::delete('/courses/{course}',     [CourseController::class, 'destroy']);
    Route::get('/courses/by-batch/{batch}/view', [CourseController::class, 'viewCourseByBatch']);
       Route::get ('/batches/{batch}/messages',  [BatchMessageController::class, 'index']);
Route::post('/batches/{batch}/messages',  [BatchMessageController::class, 'store']);

    // Featured media
    Route::get   ('/courses/{course}/media',           [CourseController::class, 'mediaIndex']);
    Route::post  ('/courses/{course}/media',           [CourseController::class, 'mediaUpload']);   // multipart OR JSON {url}
    Route::post  ('/courses/{course}/media/reorder',   [CourseController::class, 'mediaReorder']);  // {ids:[...]} or {orders:{id:pos}}
    Route::delete('/courses/{course}/media/{media}',   [CourseController::class, 'mediaDestroy']);  // {id|uuid}
    Route::delete('/courses/{course}/media/{media}/force',   [CourseController::class, 'mediaHardDestroy']);  // {id|uuid}

    // list deleted
    Route::get('/courses/deleted', [CourseController::class, 'indexDeleted']);
    
    // restore soft-deleted course
    Route::post('/courses/{course}/restore', [CourseController::class, 'restore']);
    Route::patch('/courses/{course}/restore', [CourseController::class, 'restore']); // allow PATCH

    // permanently delete
    Route::delete('/courses/{course}/force', [CourseController::class, 'forceDestroy']);

});
// Course Routes 
    Route::get   ('/courses',              [CourseController::class, 'index']);
    Route::get('/courses/{course}/view', [CourseController::class, 'viewCourse']);
    Route::get   ('/courses/{course}',     [CourseController::class, 'show']);    // {id|uuid}

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

Route::middleware('checkRole:admin,super_admin,instructor, student')->group(function () {

    // Batches
    Route::get   ('/batches',                    [BatchController::class, 'index']);
    Route::get   ('/batches/{idOrUuid}',         [BatchController::class, 'show']);
    Route::post  ('/batches',                    [BatchController::class, 'store']);
    Route::match(['put','patch'], '/batches/{idOrUuid}', [BatchController::class, 'update']);
    Route::delete('/batches/{idOrUuid}',         [BatchController::class, 'destroy']);
    Route::post  ('/batches/{idOrUuid}/restore', [BatchController::class, 'restore']);
    Route::patch ('/batches/{idOrUuid}/archive', [BatchController::class, 'archive']);


    /* ---------------------------
     *   STUDENT ROUTES
     * --------------------------- */
    Route::get   ('/batches/{idOrUuid}/students',          [BatchController::class, 'studentsIndex']);
    Route::post  ('/batches/{idOrUuid}/students/toggle',   [BatchController::class, 'studentsToggle']);
    Route::post  ('/batches/{idOrUuid}/students/upload-csv', [BatchController::class, 'studentsUploadCsv']);


    /* ---------------------------
     *   INSTRUCTOR ROUTES
     * --------------------------- */
    Route::get   ('/batches/{batch}/instructors',          [BatchController::class,'instructorsIndex']);
    Route::post  ('/batches/{batch}/instructors/toggle',   [BatchController::class,'instructorsToggle']);
    Route::patch ('/batches/{batch}/instructors/update',   [BatchController::class,'instructorsUpdate']);


    /* ---------------------------
     *   QUIZ ROUTES (NEW)
     * --------------------------- */

    // List all quizzes + search + filter (assigned/unassigned)
    Route::get   ('/batches/{idOrUuid}/quizzes',           [BatchController::class, 'quizzIndex']);

    // Assign / Unassign a quiz to batch
    Route::post  ('/batches/{idOrUuid}/quizzes/toggle',    [BatchController::class, 'quizzToggle']);

    // Update quiz link info (display_order, status, publish_to_students)
    Route::patch ('/batches/{idOrUuid}/quizzes/update',    [BatchController::class, 'quizzUpdate']);
    Route::get('/batch/{batchKey}/quizzes', [QuizzController::class,'viewQuizzesByBatch']);
// Route::get('/quizz/by-course/{course}', [QuizzController::class,'viewByCourse']);
// Route::get('/quizz/by-module/{module}', [QuizzController::class,'viewByCourseModule']);

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
    Route::get('/deleted', [QuizzController::class, 'deletedIndex']);

    // ===== Optional notes =====
    Route::get ('/{key}/notes',     [QuizzController::class, 'listNotes'])->name('notes.list');
    Route::post ('/{key}/notes',    [QuizzController::class, 'addNote'])->name('notes.add');
 
    // ===== Show/Update generic (MUST be last) =====
    Route::get ('/{key}',           [QuizzController::class, 'show'])->name('show');
    Route::match(['put','patch'],'/{key}', [QuizzController::class, 'update'])->name('update');
});
//students UUID
Route::middleware('checkRole:admin,super_admin,instructor,student')->group(function () {
    Route::get('/student/uuid', [AssignmentSubmissionController::class, 'getStudentUuid']);
});


// Assignments (admin, super_admin, instructor)
Route::middleware('checkRole:admin,super_admin,instructor, student')->group(function () {
    // Generic assignment endpoints
    Route::get   ('/assignments',                   [AssignmentController::class, 'index']);
    Route::get   ('/assignments/{assignment}',      [AssignmentController::class, 'show']);     // {id|uuid|slug}
    Route::post  ('/assignments',                   [AssignmentController::class, 'store']);
    Route::match(['put','patch'], '/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::delete('/assignments/{assignment}',      [AssignmentController::class, 'destroy']);
    Route::get   ('/assignments/bin',                         [AssignmentController::class, 'indexDeleted']);
    // Optional: assignments under a course (list/create) — keeps parity with courses routes
    Route::get   ('/courses/{course}/assignments',          [AssignmentController::class, 'index']);
    Route::post  ('/courses/{course}/assignments',          [AssignmentController::class, 'store']);
    
// New: hard delete / bin / restore
Route::delete('/assignments/{assignment}/force',          [AssignmentController::class, 'forceDelete']); // hard delete
 Route::post  ('/assignments/{assignment}/restore',        [AssignmentController::class, 'restore']);     // restore soft-deleted

// Batch-scoped assignment endpoints
Route::get   ('/batches/{batchKey}/assignments',          [AssignmentController::class, 'viewAssignmentByBatch']);
Route::post  ('/assignments/batch/{batchKey}',          [AssignmentController::class, 'storeByBatch']);
Route::get   ('/batches/{batchKey}/assignments/bin',      [AssignmentController::class, 'binByBatch']);
});

Route::middleware('checkRole:admin,super_admin,instructor,student')->prefix('assignments')->group(function () {
    Route::post('{assignmentId}/submit', [AssignmentSubmissionController::class,'uploadByAssignment'])->name('assignments.submit')->where('assignmentId','[0-9]+');
    Route::post('submit', [AssignmentSubmissionController::class,'upload'])->name('assignments.submit.generic');
    Route::get('{assignmentId}/submit-info', [AssignmentSubmissionController::class,'assignmentInfo'])->name('assignments.submit.info')->where('assignmentId','[0-9]+');
    Route::get('my-submissions/{assignmentKey}', [AssignmentSubmissionController::class,'mySubmissions'])->name('assignments.submissions.my')->where('assignmentKey','[A-Za-z0-9\-_]+');
    Route::get('my-submission/{submissionKey}', [AssignmentSubmissionController::class,'mySubmissionDetail'])->name('assignments.submission.my_detail')->where('submissionKey','[A-Za-z0-9\-_]+');
    Route::get('submission/uuid/{uuid}', [AssignmentSubmissionController::class,'show'])->name('assignments.submission.show')->whereUuid('uuid');
    Route::delete('submission/key/{submissionKey}', [AssignmentSubmissionController::class,'softDeleteSubmission'])->name('assignments.submission.soft_delete')->where('submissionKey','[A-Za-z0-9\-_]+');
    Route::post('submission/key/{submissionKey}/restore', [AssignmentSubmissionController::class,'restoreSubmission'])->name('assignments.submission.restore')->where('submissionKey','[A-Za-z0-9\-_]+');
    Route::delete('submission/key/{submissionKey}/force', [AssignmentSubmissionController::class,'forceDeleteSubmission'])->name('assignments.submission.force_delete')->where('submissionKey','[A-Za-z0-9\-_]+');
    Route::get('/{assignmentKey}/student/marks', [AssignmentSubmissionController::class, 'getMyAssignmentMarks']);

});
// Instructor-only routes
Route::middleware('checkRole:admin,super_admin,instructor,student')
    ->prefix('assignments')
    ->group(function () {

        // Get all submissions for a specific assignment
        Route::get('{assignmentKey}/submissions', [AssignmentSubmissionController::class, 'assignmentSubmissions'])
            ->name('assignments.submissions.all')
            ->where('assignmentKey', '[A-Za-z0-9\-_]+');

        // Get student submission status
        Route::get('{assignmentKey}/student-status', [AssignmentSubmissionController::class, 'studentSubmissionStatus'])
            ->name('assignments.submissions.status')
            ->where('assignmentKey', '[A-Za-z0-9\-_]+');

        // Get submission statistics
        Route::get('{assignmentKey}/submission-stats', [AssignmentSubmissionController::class, 'submissionStats'])
            ->name('assignments.submissions.stats')
            ->where('assignmentKey', '[A-Za-z0-9\-_]+');

        // CSV Export Routes
        Route::get('{assignmentKey}/export/submitted', [AssignmentSubmissionController::class, 'exportSubmittedStudentsCSV']);
        Route::get('{assignmentKey}/export/unsubmitted', [AssignmentSubmissionController::class, 'exportUnsubmittedStudentsCSV']);
        Route::get('{assignmentKey}/export/all', [AssignmentSubmissionController::class, 'exportAllStudentsStatusCSV']);

        // Grading routes
        Route::post('submissions/{submission}/grade', [AssignmentSubmissionController::class, 'gradeSubmission']);
        Route::get('submissions/{submission}/marks', [AssignmentSubmissionController::class, 'getSubmissionMarks']);
        Route::get('{assignmentKey}/marks', [AssignmentSubmissionController::class, 'getAssignmentMarks']);

        // Bulk grade
        Route::post('submissions/bulk-grade', [AssignmentSubmissionController::class, 'bulkGradeSubmissions']);

        // Document viewing routes
        Route::get('{assignment}/submissions-documents', [AssignmentSubmissionController::class, 'getAssignmentSubmissionsWithDocuments']);
        Route::get('{assignment}/students/{student}/documents', [AssignmentSubmissionController::class, 'getStudentAssignmentDocuments']);
        Route::get('submissions/{submission}/download-documents', [AssignmentSubmissionController::class, 'downloadSubmissionDocuments']);
    });

// Study Material Routes 
Route::middleware('checkRole:admin,super_admin,instructor,student')->group(function () {
    Route::get   ('/study-materials',                 [StudyMaterialController::class, 'index']);
    Route::get('/study-materials/batch/{batchKey}', [StudyMaterialController::class, 'viewStudyMaterialByBatch']);
    Route::post('/study-materials/batch/{batchKey}', [StudyMaterialController::class, 'storeByBatch']);

    // Route::get('/study-materials/page',[StudyMaterialController::class, 'indexByQuery']);
    Route::post  ('/study-materials',                 [StudyMaterialController::class, 'store']);
    Route::patch ('/study-materials/{id}',            [StudyMaterialController::class, 'update']);
    Route::delete('/study-materials/{id}',            [StudyMaterialController::class, 'destroy']);
    Route::post('/study-materials/{id}/restore', [StudyMaterialController::class, 'restore']);
    Route::delete('/study-materials/{id}/force', [StudyMaterialController::class, 'forceDelete']);
    Route::get('/study-materials/deleted', [StudyMaterialController::class, 'indexDeleted']);
    Route::get('/study-materials/bin/batch/{batchKey}', [StudyMaterialController::class, 'binByBatch']);
       // View endpoints
    Route::get   ('/study-materials/show/{uuid}',     [StudyMaterialController::class, 'showByUuid']);
    Route::get   ('/study-materials/stream/{uuid}/{fileId}', [StudyMaterialController::class, 'streamInline']);
});


// All Media Routes 
Route::middleware(['checkRole:admin,super_admin,instructor,author'])->group(function () {
    Route::get('/media',  [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::delete('/media/{idOrUuid}', [MediaController::class, 'destroy']);
});


// Notice Routes
Route::middleware('checkRole:admin,super_admin,instructor,student')->group(function () {
    Route::get('/notices', [NoticeController::class, 'index']);
    Route::get('/notices/batch/{batchKey}', [NoticeController::class, 'viewByBatch']);
    Route::post('/notices/batch/{batchKey}', [NoticeController::class, 'storeByBatch']);
   
    Route::post('/notices', [NoticeController::class, 'store']);
    Route::post('/notices/{id}/archive', [NoticeController::class, 'archive']);
Route::post('/notices/{id}/unarchive', [NoticeController::class, 'unarchive']);

    Route::patch('/notices/{id}', [NoticeController::class, 'update']);
    Route::delete('/notices/{id}', [NoticeController::class, 'destroy']);

    Route::post('/notices/{id}/restore', [NoticeController::class, 'restore']);
    Route::delete('/notices/{id}/force', [NoticeController::class, 'forceDelete']);
    Route::get('/notices/deleted', [NoticeController::class, 'indexDeleted']);
    Route::get('/notices/bin/batch/{batchKey}', [NoticeController::class, 'binByBatch']);
   
    // View endpoints
    Route::get('/notices/show/{uuid}', [NoticeController::class, 'showByUuid']);
    Route::get('/notices/stream/{uuid}/{fileId}', [NoticeController::class, 'streamInline']);
});

// Exam Routes

Route::middleware(['checkRole:student,admin'])->prefix('exam')->group(function () {
    Route::post('/start',                           [ExamController::class, 'start']);
    Route::get ('/attempts/{attempt}/questions',    [ExamController::class, 'questions']);
    Route::post('/attempts/{attempt}/answer',       [ExamController::class, 'saveAnswer']);
    Route::post('/attempts/{attempt}/submit',       [ExamController::class, 'submit']);
    Route::get ('/attempts/{attempt}/status',       [ExamController::class, 'status']);
    Route::post('/attempts/{attempt}/focus', [ExamController::class, 'focus']);
    Route::get('/quizzes/{quizKey}/my-attempts', [ExamController::class, 'myAttemptsForQuiz']);
    Route::get('/results/{resultId}', [ExamController::class, 'resultDetail']);
    Route::get('/results/{resultId}/export', [ExamController::class, 'export']);

});

// printable answer sheet (usually admin/instructor; expose as needed)
Route::middleware(['checkRole:admin,instructor,super_admin'])->get(
    '/exam/results/{id}/answer-sheet',
    [ExamController::class, 'answerSheet']
);

Route::middleware('checkRole:admin,super_admin,instructor')->group(function () {
    // -----------------------
    // Modules (list / create)
    // -----------------------
    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('modules/archived', [ModuleController::class, 'archived'])->name('modules.archived');
    Route::get('modules/bin', [ModuleController::class, 'bin'])->name('modules.bin');
    Route::post('modules', [ModuleController::class, 'store'])->name('modules.store');

    // -----------------------
    // Module actions (specific first)
    // -----------------------
    // all-with-privileges
    Route::get('modules/all-with-privileges', [ModuleController::class, 'allWithPrivileges'])
        ->name('modules.allWithPrivileges');

    // restore / archive / unarchive / force delete (specific routes before parameter route)
    Route::post('modules/{id}/restore', [ModuleController::class, 'restore'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.restore');

    Route::post('modules/{id}/archive', [ModuleController::class, 'archive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.archive');

    Route::post('modules/{id}/unarchive', [ModuleController::class, 'unarchive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.unarchive');

    Route::delete('modules/{id}/force', [ModuleController::class, 'forceDelete'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.forceDelete');

    // reorder (collection action)
    Route::post('modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');

    // single-resource show/update/destroy (allow numeric id or UUID)
    Route::get('modules/{id}', [ModuleController::class, 'show'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.show');

    Route::match(['put', 'patch'], 'modules/{id}', [ModuleController::class, 'update'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.update');

    Route::delete('modules/{id}', [ModuleController::class, 'destroy'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.destroy');

    // -----------------------
    // Privileges (collection first, then specific actions, then single-resource)
    // -----------------------
    Route::get('privileges', [PrivilegeController::class, 'index'])->name('privileges.index'); // optional module_id filter
    Route::get('privileges/archived', [PrivilegeController::class, 'archived'])->name('privileges.archived');
    Route::get('privileges/bin', [PrivilegeController::class, 'bin'])->name('privileges.bin');

    Route::post('privileges', [PrivilegeController::class, 'store'])->name('privileges.store');

    // actions on particular privilege (specific before the param-based show)
    Route::delete('privileges/{id}/force', [PrivilegeController::class, 'forceDelete'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.forceDelete');

    Route::post('privileges/{id}/restore', [PrivilegeController::class, 'restore'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.restore');

    Route::post('privileges/{id}/archive', [PrivilegeController::class, 'archive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.archive');

    Route::post('privileges/{id}/unarchive', [PrivilegeController::class, 'unarchive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.unarchive');

    Route::post('privileges/reorder', [PrivilegeController::class, 'reorder'])
        ->name('privileges.reorder'); // expects { ids: [...] }

    // single-resource show/update/destroy — allow id or uuid
    Route::get('privileges/{id}', [PrivilegeController::class, 'show'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.show');

    Route::match(['put', 'patch'], 'privileges/{id}', [PrivilegeController::class, 'update'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.update');

    Route::delete('privileges/{id}', [PrivilegeController::class, 'destroy'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.destroy');

    // module-specific privileges
    Route::get('modules/{id}/privileges', [PrivilegeController::class, 'forModule'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.privileges');

    // -----------------------
    // User privilege endpoints
    // -----------------------
    // prefer explicit names and avoid ambiguous "delete" path for semantic clarity
    Route::post('user-privileges/sync',   [UserPrivilegeController::class, 'sync'])->name('user-privileges.sync');
    Route::post('user-privileges/assign', [UserPrivilegeController::class, 'assign'])->name('user-privileges.assign');
    Route::post('user-privileges/unassign', [UserPrivilegeController::class, 'unassign'])->name('user-privileges.unassign');
    Route::post('user-privileges/delete', [UserPrivilegeController::class, 'destroy'])->name('user-privileges.destroy'); // revoke mapping (soft-delete)
    Route::get('user-privileges/list',    [UserPrivilegeController::class, 'list'])->name('user-privileges.list');

    // user lookup routes
    // show by numeric id or uuid (keep constraint to avoid accidental greedy matches)
    Route::get('user/{idOrUuid}', [UserPrivilegeController::class, 'show'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('user.show');

    // explicit by-uuid route if you need a looser pattern or different handling
    Route::get('user/by-uuid/{uuid}', [UserPrivilegeController::class, 'byUuid'])
        ->where('uuid', '[0-9a-fA-F\-]{36}')
        ->name('user.byUuid');
});
// Topic
Route::prefix('coding_topics')->group(function () {
    Route::get('/',              [TopicController::class, 'index'])->name('topics.index');
    Route::get('{idOrSlug}',     [TopicController::class, 'show'])->name('topics.show');
    Route::post('/',             [TopicController::class, 'store'])->name('topics.store');
    Route::match(['put','patch'], '{id}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('{id}',        [TopicController::class, 'destroy'])->name('topics.destroy');
    Route::post('{id}/restore',  [TopicController::class, 'restore'])->name('topics.restore');
    Route::patch('{id}/toggle-status', [TopicController::class, 'toggleStatus'])->name('topics.toggle');
    Route::post('reorder',       [TopicController::class, 'reorder'])->name('topics.reorder');
    Route::post('{id}/image',    [TopicController::class, 'changeImage'])->name('topics.image.change');
    Route::delete('{id}/image',  [TopicController::class, 'removeImage'])->name('topics.image.remove');
});

// Coding Modules
Route::prefix('coding_modules')->group(function () {
    Route::get('/',               [CodingModuleController::class, 'index'])->name('modules.index');
    Route::get('{idOrSlug}',      [CodingModuleController::class, 'show'])->name('modules.show');
    Route::post('/',              [CodingModuleController::class, 'store'])->name('modules.store');
    Route::match(['put','patch'], '{id}', [CodingModuleController::class, 'update'])->name('modules.update');
    Route::delete('{id}',         [CodingModuleController::class, 'destroy'])->name('modules.destroy');
    Route::post('{id}/restore',   [CodingModuleController::class, 'restore'])->name('modules.restore');
    Route::patch('{id}/toggle-status', [CodingModuleController::class, 'toggleStatus'])->name('modules.toggle');
    Route::post('reorder',        [CodingModuleController::class, 'reorder'])->name('modules.reorder');
});
Route::post('/judge/execute', [JudgeController::class, 'submit']);

// Coding Questions
Route::prefix('coding_questions')->group(function () {
    Route::get('/',                        [CodingQuestionController::class, 'index'])->name('questions.index');

    // put static/action routes before the catch-all {idOrSlug}
    Route::post('reorder',                 [CodingQuestionController::class, 'reorder'])->name('questions.reorder');

    Route::post('/',                       [CodingQuestionController::class, 'store'])->name('questions.store');
    Route::match(['put','patch'], '{id}',  [CodingQuestionController::class, 'update'])->name('questions.update');
    Route::delete('{id}',                  [CodingQuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('{id}/restore',            [CodingQuestionController::class, 'restore'])->name('questions.restore');
    Route::patch('{id}/toggle-status',     [CodingQuestionController::class, 'toggleStatus'])->name('questions.toggle');

    // keep this LAST so it doesn’t swallow other routes
    Route::get('{identifier}',             [CodingQuestionController::class, 'show'])->name('questions.show');
});

// Admin: manage "Updates" strip (you can wrap with auth middleware)
Route::prefix('landing')->group(function () {
    Route::get('updates', [LandingPageController::class, 'updatesIndex'])->name('landing.updates.index');
    Route::post('updates', [LandingPageController::class, 'updatesStore'])->name('landing.updates.store');
    Route::put('updates/{id}', [LandingPageController::class, 'updatesUpdate'])->name('landing.updates.update');
    Route::delete('updates/{id}', [LandingPageController::class, 'updatesDestroy'])->name('landing.updates.destroy');
     // Updates
    Route::post('/updates/reorder', [LandingPageController::class, 'updates_reorder']);

    // Hero images
    Route::post('/hero/reorder', [LandingPageController::class, 'hero_reorder']);

    // Categories
    Route::post('/categories/reorder', [LandingPageController::class, 'categories_reorder']);

    // Featured courses
   
    Route::post('/featured-courses/reorder', [LandingPageController::class, 'featuredCourses_reorder']);
});
Route::post('/landing/contacts/reorder', [LandingPageController::class, 'contact_reorder'])
    ->name('landing.contact.reorder');

Route::get   ('landing/contacts',        [LandingPageController::class, 'contact_index'])->name('landing.contact.index');
Route::get('landing/contact', [LandingPageController::class, 'contactsDisplay']);
Route::post  ('landing/contact',        [LandingPageController::class, 'contact_store'])->name('landing.contact.store');
Route::put   ('landing/contact/{id}',   [LandingPageController::class, 'contact_update'])->name('landing.contact.update');
Route::patch ('landing/contact/{id}',   [LandingPageController::class, 'contact_update']);
Route::delete('landing/contact/{id}',   [LandingPageController::class, 'contact_destroy'])->name('landing.contact.destroy');

Route::get   ('landing/hero-images',        [LandingPageController::class, 'hero_index'])->name('landing.hero.index');
Route::post  ('landing/hero-images',        [LandingPageController::class, 'hero_store'])->name('landing.hero.store');
Route::put   ('landing/hero-images/{id}',   [LandingPageController::class, 'hero_update'])->name('landing.hero.update');
Route::patch ('landing/hero-images/{id}',   [LandingPageController::class, 'hero_update']);
Route::delete('landing/hero-images/{id}',   [LandingPageController::class, 'hero_destroy'])->name('landing.hero.destroy');

// OPTIONAL: public display API for landing page
Route::get('landing/hero-images/display', [LandingPageController::class, 'hero_display'])
    ->name('landing.hero.display');
// Upload from device
Route::post('uploads/hero-image', [LandingPageController::class, 'upload']);

// Image library for modal ("From Library" button)
Route::get('media/images', [LandingPageController::class, 'library']);

Route::get   ('landing/categories',        [CourseCategoryController::class, 'categories_index'])->name('landing.categories.index');
Route::post  ('landing/categories',        [CourseCategoryController::class, 'categories_store'])->name('landing.categories.store');
Route::put   ('landing/categories/{id}',   [CourseCategoryController::class, 'categories_update'])->name('landing.categories.update');
Route::patch ('landing/categories/{id}',   [CourseCategoryController::class, 'categories_update']);
Route::delete('landing/categories/{id}',   [CourseCategoryController::class, 'categories_destroy'])->name('landing.categories.destroy');

// public display for landing page
Route::get('landing/categories/display', [CourseCategoryController::class, 'categories_display'])
    ->name('landing.categories.display');
 

Route::get   ('landing/featured-courses',        [LandingPageController::class, 'featuredCourses_index']);
Route::get('landing/featured-courses/display',   [LandingPageController::class, 'featuredCourses_display']);
Route::patch('/courses/{course}/featured', [LandingPageController::class, 'toggleFeatured']);

//Students Enroll & verify 
Route::post('batches/{idOrUuid}/students/enroll', [BatchController::class, 'enrollStudent']);
Route::post('batches/{idOrUuid}/students/{userId}/verify', [BatchController::class, 'verifyStudent']);

 Route::get('/batches/{batch}/enrollment/status', [BatchController::class, 'checkBatchEnrollment']);
 Route::get('/batches/{idOrUuid}/students/not-verified', [BatchController::class, 'getNotVerifiedStudents']
);
Route::middleware(['checkRole:superadmin,admin,instructor,student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'summary']);                // role-aware single payload
    Route::get('/dashboard/widgets/{slug}', [DashboardController::class, 'widget']);  // optional lazy widgets
});