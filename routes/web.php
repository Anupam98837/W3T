<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VIEW\CodingQuestionController as ViewQuestionController;


// Route::get('/', function () {
//     return view('ui.structure');
// });

Route::get('/ui', function () {
    return view('ui.ui');
});
Route::get('/', function () {
    return view('pages.landing.pages.home');
});


// Login Routes 

Route::get('/login', function () {
    return view('pages.auth.login');
});

Route::get('/register', function () {
    return view('pages.auth.register');
});

Route::get('/courses/all', function () {
    return view('pages.landing.pages.allCourse');
});

Route::get('/categories/all', function () {
    return view('pages.landing.pages.allCategory');
});

Route::get('/updates/all', function () {
    return view('pages.landing.pages.viewUpdates');
});
Route::get('/terms&conditions', function () {
    return view('pages.landing.pages.termsAndCondition');
});
Route::get('/privacypolicy', function () {
    return view('pages.landing.pages.privacyPolicy');
});

Route::get('/refundpolicy', function () {
    return view('pages.landing.pages.refundPolicy');
});

Route::get('/about-us', function () {
    return view('pages.landing.pages.aboutUs');
});
Route::get('/contact-us', function () {
    return view('pages.landing.pages.contactUs');
});

Route::get('/exam/{quiz}', function (\Illuminate\Http\Request $r, $quiz) {
    // Pass the quiz key (uuid or id) to the view
    return view('modules.exam.exam', ['quizKey' => $quiz]);
})->name('exam.take');
// Super Admin Routes

Route::get('super_admin/dashboard', function () {
    return view('pages.users.super_admin.pages.common.dashboard');
})->name('dashboard');

Route::get('super_admin/users/manage', function () {
    return view('pages.users.super_admin.pages.users.manageUsers');
});

Route::get('/super_admin/courses/create', function () {
    return view('pages.users.super_admin.pages.course.createCourse');
});
Route::get('/super_admin/courses/manage', function () {
    return view('pages.users.super_admin.pages.course.manageCourses');
});

Route::get('/super_admin/batches/manage', function () {
    return view('pages.users.super_admin.pages.batches.manageBatches');
});

Route::get('super_admin/mailers/manage', function () {
    return view('pages.users.super_admin.pages.mailers.manageMailers');
})->name('mailers.manage');

Route::get('/super_admin/courses/{course}', fn ($course) =>
    view('pages.users.super_admin.pages.course.viewCourse', ['courseParam' => $course])
)->where('course', '^(?!create$|manage$|view$).+');


// Admin Routes 
Route::get('/admin/dashboard', function () {
    return view('pages.users.admin.pages.common.dashboard');
})->name('dashboard');

Route::get('/admin/users/manage', function () {
    return view('pages.users.admin.pages.users.manageUsers');
});

Route::get('/admin/courses/create', function () {
    return view('pages.users.admin.pages.course.createCourse');
});
Route::get('/admin/courses/manage', function () {
    return view('pages.users.admin.pages.course.manageCourses');
});

Route::get('/admin/batches/manage', function () {
    return view('pages.users.admin.pages.batches.manageBatches');
});

Route::get('/admin/mailers/manage', function () {
    return view('pages.users.admin.pages.mailers.manageMailers');
})->name('mailers.manage');

Route::get('/admin/coursesModule/manage', function () {
    return view('pages.users.admin.pages.course.manageCourseModule');
});

Route::get('/admin/quizz/create', function () {
    return view('pages.users.admin.pages.quizz.createQuizz');
});
Route::get('/admin/quizz/manage', function () {
    return view('pages.users.admin.pages.quizz.manageQuizz');
});

Route::get('/admin/quizz/questions/manage', function () {
    return view('pages.users.admin.pages.questions.manageQuestion');
});

Route::get('/admin/courses/{uuid}/view', function (string $uuid) {
    return view('modules.course.viewCourse.viewCourseLayout', [
        'courseParam' => $uuid,   // use this in the Blade JS
    ]);
})->whereUuid('uuid')->name('admin.courses.global');

Route::get('/courses/{course}', fn ($course) =>
    view('pages.landing.pages.viewCourse', ['courseParam' => $course])
)->where('course', '^(?!create$|manage$|view$).+')->name('pages.courses.admin');

Route::get('/admin/course/studyMaterial/manage', function () {
    return view('pages.users.admin.pages.studyMaterial.manageStudyMaterial');
});
Route::get('/admin/course/studyMaterial/create', function () {
    return view('pages.users.admin.pages.studyMaterial.createStudyMaterial');
});

Route::get('/admin/assignments/create', function () {
    return view('pages.users.admin.pages.assignments.createAssignment');
});
 
Route::get('/admin/assignments/manage', function () {
    return view('pages.users.admin.pages.assignments.manageAssignment');
});

Route::get('/admin/courses/{uuid}/view', function (string $uuid) {
    return view('modules.course.viewCourse.viewCourseLayout', [
        'courseParam' => $uuid,  
    ]);
})->whereUuid('uuid')->name('admin.courses.view');

Route::get('/admin/courses', function () {
    return view('pages.users.admin.pages.course.courses');
});

Route::get('/admin/notice/manage', function () {
    return view('pages.users.admin.pages.notices.manageNotice');
});
Route::get('/admin/notice/create', function () {
    return view('pages.users.admin.pages.notices.createNotice');
});

Route::get('/admin/module/manage', function () {
    return view('modules.module.manageModule');
});

Route::get('/admin/privilege/manage', function () {
    return view('modules.privileges.managePrivileges');
});

  // Accept either numeric ID OR UUID via query params
Route::get('/user-privileges/manage', function () {
    $userUuid = request('user_uuid');
    $userId   = request('user_id'); // fallback
    
    return view('pages.users.admin.pages.privileges.assignPrivileges', [
        'userUuid' => $userUuid,
        'userId'   => $userId,
    ]);
})->name('modules.privileges.assign.user');

//Coding Routes
Route::get('/test', function () {
    return view('pages.users.admin.pages.compiler.testCompiler');});

    Route::get('/coding-test/{uuid}', function () {
        return view('modules.codingTest.codingTest');});
    
    
Route::get('/admin/topic/manage', function () {
    return view('pages.users.admin.pages.topic.manageTopic');
});
Route::get('/admin/topic/module/manage', function () {
    return view('pages.users.admin.pages.topic.manageTopicModule');
});
Route::get('/admin/compiler/manage', function () {
    return view('pages.users.admin.pages.compiler.testCompiler');
});

Route::prefix('admin') // add your middlewares if needed
    ->group(function () {
        Route::get('topics/{topic}/modules/{module}/questions',
            [ViewQuestionController::class, 'manage']
        )->name('admin.questions.manage');
    });
// Landing Page dynamic Routes
 Route::get('/admin/landing-page/updates/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageUpdates');
});

Route::get('/admin/landing-page/contacts/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageContacts');
});

Route::get('/admin/landing-page/hero-images/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageHeroImages');
});

Route::get('/admin/landing-page/categories/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageCategories');
});

Route::get('/admin/featured/courses/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageCourses');
});

Route::get('/admin/landing-page/terms-and-conditions/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageTermsAndCondition');
});

Route::get('/admin/landing-page/refund-policy/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageRefundPolicy');
});

Route::get('/admin/landing-page/privacy-policy/manage', function () {
    return view('pages.users.admin.pages.landingPages.managePrivacyPolicy');
});

Route::get('/admin/landing-page/about-us/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageAboutUs');
});
Route::get('/admin/landing-page/enquiry/manage', function () {
    return view('pages.users.admin.pages.landingPages.manageEnquiry');
});

// Student Routes
Route::get('/student/dashboard', function () {
    return view('pages.users.student.pages.common.dashboard');
})->name('student.dashboard');

Route::get('/student/courses', function () {
    return view('pages.users.student.pages.course.courses');
});

Route::get('/mycourses/{batch}/view', function($batchUuid) {
    return view('modules.course.viewCourse.viewCourseLayout', ['batchUuid' => $batchUuid]);
})->name('student.course.view');

Route::get('/exam/results/{resultId}/view', function ($resultId) {
    return view('modules.course.viewCourse.viewCourseTabs.examResult', ['resultId' => $resultId]);
});


// Instructor Routes
Route::get('/instructor/dashboard', function () {
    return view('pages.users.instructor.pages.common.dashboard');
})->name('instructor.dashboard');

Route::get('/instructor/courses', function () {
    return view('pages.users.instructor.pages.course.courses');
});

// Route::get('/admin/viewdocuments', function () {
//     return view('modules.course.viewCourse.viewCourseTabs.assignmentSubmissionView');
// })->name('admin.viewdocuments');

Route::get('/assignments/{assignment}/students/{student}/documents', function (string $assignment, string $student) {
    return view('modules.course.viewCourse.viewCourseTabs.assignmentSubmissionView', [
        'assignmentKey' => $assignment,
        'studentKey' => $student,
    ]);
})
->whereUuid('assignment')
->whereUuid('student')
->name('admin.assignments.student.documents');


Route::get('/admin/notice/manage', function () {
    return view('pages.users.admin.pages.notices.manageNotice');
});
Route::get('/admin/notice/create', function () {
    return view('pages.users.admin.pages.notices.createNotice');
});




Route::get('/admin/profile', function () {
    return view('pages.users.admin.pages.common.profile');
});

Route::get('/student/profile', function () {
    return view('pages.users.student.pages.common.profile');
});

Route::get('/instructor/profile', function () {
    return view('pages.users.instructor.pages.common.profile');
});

Route::get('/super-admin/profile', function () {
    return view('pages.users.super_admin.pages.common.profile');
});