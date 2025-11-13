{{-- resources/views/pages/users/student/pages/courses/courses.blade.php --}}
@extends('pages.users.student.layout.structure')

@section('title', 'Available Courses')
@section('subtitle', 'Browse and enroll in available courses')

@push('styles')
  {{-- If your layout already loads main.css, you can remove this line --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
@endpush

@section('content')
  @include('modules.course.viewCourse.courses')
@endsection