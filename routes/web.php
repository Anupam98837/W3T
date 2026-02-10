<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\VIEW\CodingQuestionController as ViewQuestionController;

/*
|--------------------------------------------------------------------------
| Public / Landing Routes
|--------------------------------------------------------------------------
*/

Route::get('/ui', fn () => view('ui.ui'));
Route::get('/', fn () => view('pages.landing.pages.home'));

Route::get('/login', fn () => view('pages.auth.login'));
Route::get('/register', fn () => view('pages.auth.register'));

Route::get('/forgot-password', fn () => view('pages.auth.forgotPassword'));
Route::get('/reset-password', fn () => view('pages.auth.resetPassword'));

Route::get('/courses/all', fn () => view('pages.landing.pages.allCourse'));
Route::get('/categories/all', fn () => view('pages.landing.pages.allCategory'));
Route::get('/updates/all', fn () => view('pages.landing.pages.viewUpdates'));

Route::get('/courses/{course}', fn ($course) =>
    view('pages.landing.pages.viewCourse', ['courseParam' => $course])
)->where('course', '^(?!create$|manage$|view$).+')->name('pages.courses.admin');

Route::get('/terms&conditions', fn () => view('pages.landing.pages.termsAndCondition'));
Route::get('/privacypolicy', fn () => view('pages.landing.pages.privacyPolicy'));
Route::get('/refundpolicy', fn () => view('pages.landing.pages.refundPolicy'));

Route::get('/about-us', fn () => view('pages.landing.pages.aboutUs'));
Route::get('/contact-us', fn () => view('pages.landing.pages.contactUs'));

Route::get('/blog/view/{slug}', fn ($slug) => view('pages.landing.pages.viewBlog'));

Route::get('/course-categories/manage', fn () => view('pages.users.pages.course.manageCategories'));

/*
|--------------------------------------------------------------------------
| Exams (public)
|--------------------------------------------------------------------------
*/
Route::get('/exam/{quiz}', fn (Request $r, $quiz) => view('modules.exam.exam', ['quizKey' => $quiz]))->name('exam.take');
Route::get('/test-exam/{quiz}', fn (Request $r, $quiz) => view('modules.exam.testExam', ['quizKey' => $quiz]))->name('exam.test');

/*
|--------------------------------------------------------------------------
| General (NO role wise) - All Admin/Panel Pages
|--------------------------------------------------------------------------
| Base path used: pages.users.pages....
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', fn () => view('pages.users.pages.common.dashboard'))->name('dashboard');
Route::get('/profile', fn () => view('pages.users.pages.common.profile'))->name('profile');

/* Users */
Route::get('/users/manage', fn () => view('pages.users.pages.users.manageUsers'));

/* Courses */
Route::get('/courses/create', fn () => view('pages.users.pages.course.createCourse'));
Route::get('/courses/manage', fn () => view('pages.users.pages.course.manageCourses'));
Route::get('/courses-module/manage', fn () => view('pages.users.pages.course.manageCourseModule'));
Route::get('/running-courses', fn () => view('pages.users.pages.course.courses')); // was /admin/courses

// Module view course layout (keep)
Route::get('/courses/{uuid}/view', function (string $uuid) {
    return view('modules.course.viewCourse.viewCourseLayout', [
        'courseParam' => $uuid,
    ]);
})->whereUuid('uuid')->name('courses.view');

// Public landing view course (keep)
Route::get('/course/{course}', fn ($course) =>
    view('pages.landing.pages.viewCourse', ['courseParam' => $course])
)->where('course', '^(?!create$|manage$|view$).+')->name('courses.public.view');

/* Batches */
Route::get('/batches/manage', fn () => view('pages.users.pages.batches.manageBatches'));

/* Mailers */
Route::get('/mailers/manage', fn () => view('pages.users.pages.mailers.manageMailers'))->name('mailers.manage');

/* Study Material */
Route::get('/study-material/manage', fn () => view('pages.users.pages.studyMaterial.manageStudyMaterial'));
Route::get('/study-material/create', fn () => view('pages.users.pages.studyMaterial.createStudyMaterial'));

/* Assignments */
Route::get('/assignments/create', fn () => view('pages.users.pages.assignments.createAssignment'));
Route::get('/assignments/manage', fn () => view('pages.users.pages.assignments.manageAssignment'));

/* Notices */
Route::get('/notice/manage', fn () => view('pages.users.pages.notices.manageNotice'));
Route::get('/notice/create', fn () => view('pages.users.pages.notices.createNotice'));

/* Quiz */
Route::get('/quizz/create', fn () => view('pages.users.pages.quizz.createQuizz'));
Route::get('/quizz/manage', fn () => view('pages.users.pages.quizz.manageQuizz'));
Route::get('/quizz/questions/manage', fn () => view('pages.users.pages.questions.manageQuestion'));

/*
|--------------------------------------------------------------------------
| Dashboard Menu + Privileges (modules - keep same)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard-menu/manage', fn () => view('modules.dashboardMenu.manageDashboardMenu'));
Route::get('/dashboard-menu/create', fn () => view('modules.dashboardMenu.createDashboardMenu'));

Route::get('/page-privilege/manage', fn () => view('modules.privileges.managePagePrivileges'));
Route::get('/page-privilege/create', fn () => view('modules.privileges.createPagePrivileges'));

Route::get('/user-privileges/manage', function () {
    $userUuid = request('user_uuid');
    $userId   = request('user_id');

    return view('modules.privileges.assignPrivileges', [
        'userUuid' => $userUuid,
        'userId'   => $userId,
    ]);
})->name('modules.privileges.assign.user');

/*
|--------------------------------------------------------------------------
| Coding / Compiler
|--------------------------------------------------------------------------
*/
Route::get('/compiler/test', fn () => view('pages.users.pages.compiler.testCompiler'));
Route::get('/compiler/manage', fn () => view('pages.users.pages.compiler.testCompiler'));

Route::get('/coding-test', function (Request $request) {
    $batch    = $request->query('batch');
    $question = $request->query('question');
    $attempt  = $request->query('attempt');

    if (!$batch || !$question) abort(404, 'Missing required parameters');

    return view('modules.codingTest.codingTest', compact('batch','question','attempt'));
});

Route::get('/topic/manage', fn () => view('pages.users.pages.topic.manageTopic'));
Route::get('/topic/module/manage', fn () => view('pages.users.pages.topic.manageTopicModule'));

Route::get('/topics/{topic}/modules/{module}/questions', [ViewQuestionController::class, 'manage'])
    ->name('questions.manage');

/*
|--------------------------------------------------------------------------
| Landing Page Admin Settings (general)
|--------------------------------------------------------------------------
*/
Route::get('/updates/manage', fn () => view('pages.users.pages.landingPages.manageUpdates'));
Route::get('/contacts/manage', fn () => view('pages.users.pages.landingPages.manageContacts'));
Route::get('/hero-images/manage', fn () => view('pages.users.pages.landingPages.manageHeroImages'));
Route::get('/featured/courses/manage', fn () => view('pages.users.pages.landingPages.manageCourses'));

Route::get('/terms-and-conditions/manage', fn () => view('pages.users.pages.landingPages.manageTermsAndCondition'));
Route::get('/refund-policy/manage', fn () => view('pages.users.pages.landingPages.manageRefundPolicy'));
Route::get('/privacy-policy/manage', fn () => view('pages.users.pages.landingPages.managePrivacyPolicy'));
Route::get('/about-us/manage', fn () => view('pages.users.pages.landingPages.manageAboutUs'));
Route::get('/enquiry/manage', fn () => view('pages.users.pages.landingPages.manageEnquiry'));

/*
|--------------------------------------------------------------------------
| Blog (general)
|--------------------------------------------------------------------------
*/
Route::get('/blog/create', fn () => view('modules.blog.createBlog'));
Route::get('/blog/manage', fn () => view('pages.users.pages.blog.manageBlog'));

/*
|--------------------------------------------------------------------------
| My Courses / Results (general)
|--------------------------------------------------------------------------
*/
Route::get('/mycourses/{batch}/view', function ($batchUuid) {
    return view('modules.course.viewCourse.viewCourseLayout', ['batchUuid' => $batchUuid]);
})->name('mycourses.view');

Route::get('/exam/results/{resultId}/view', fn ($resultId) =>
    view('modules.course.viewCourse.viewCourseTabs.examResult', ['resultId' => $resultId])
);

Route::get('/coding/results/{resultUuid}/view', fn ($resultUuid) =>
    view('modules.course.viewCourse.viewCourseTabs.codingResult', ['resultUuid' => $resultUuid])
);

/*
|--------------------------------------------------------------------------
| Assignment documents viewer (general)
|--------------------------------------------------------------------------
*/
Route::get('/assignments/{assignment}/students/{student}/documents', function (string $assignment, string $student) {
    return view('modules.course.viewCourse.viewCourseTabs.assignmentSubmissionView', [
        'assignmentKey' => $assignment,
        'studentKey'    => $student,
    ]);
})
->whereUuid('assignment')
->whereUuid('student')
->name('assignments.student.documents');


/*
|--------------------------------------------------------------------------
| user activity log  viewer 
|--------------------------------------------------------------------------
*/

Route::get('/activity-logs', fn () => view('modules.userActivityLogs.userActivityLogsView'));

Route::get('/meta-tags/manage', function () {
    return view('pages.users.pages.metaTags.manageMetaTags');
});