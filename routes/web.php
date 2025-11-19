<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('ui.structure');
// });

Route::get('/ui', function () {
    return view('ui.ui');
});
Route::get('/testing', function () {
    return view('modules.testing');
});


// Login Routes 

Route::get('/', function () {
    return view('pages.auth.login');
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

Route::get('/admin/courses/{course}', fn ($course) =>
    view('pages.users.admin.pages.course.viewCourse', ['courseParam' => $course])
)->where('course', '^(?!create$|manage$|view$).+')->name('admin.courses.admin');

Route::get('/admin/course/studyMaterial/manage', function () {
    return view('pages.users.admin.pages.studyMaterial.manageStudyMaterial');
});
Route::get('/admin/course/studyMaterial/create', function () {
    return view('pages.users.admin.pages.studyMaterial.createStudyMaterial');
});

Route::get('/admin/assignments/create', function () {
    return view('pages.users.admin.pages.assignments.createAssignment');
});
 
// Route::get('/admin/assignments/manage', function () {
//     return view('pages.users.admin.pages.assignments.manageAssignments');
// });

Route::get('/admin/courses/{uuid}/view', function (string $uuid) {
    return view('modules.course.viewCourse.viewCourseLayout', [
        'courseParam' => $uuid,   // <-- pass to Blade; JS will use this directly
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


// Student Routes
Route::get('/student/dashboard', function () {
    return view('pages.users.student.pages.common.dashboard');
})->name('student.dashboard');

Route::get('/student/courses', function () {
    return view('pages.users.student.pages.course.courses');
});

Route::get('/courses/{batch}/view', function($batchUuid) {
    return view('modules.course.viewCourse.viewCourseLayout', ['batchUuid' => $batchUuid]);
})->name('student.course.view');


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
