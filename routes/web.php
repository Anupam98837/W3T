<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('ui.structure');
});

Route::get('/ui', function () {
    return view('ui.ui');
});
Route::get('/login', function () {
    return view('ui.login');
});


// Login Routes 

Route::get('auth/login', function () {
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
