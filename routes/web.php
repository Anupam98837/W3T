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

Route::get('/admin/courses/{course}', fn ($course) =>
    view('pages.users.admin.pages.course.viewCourse', ['courseParam' => $course])
)->where('course', '^(?!create$|manage$|view$).+');

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
    return view('pages.users.admin.pages.assignments.manageAssignments');
});

