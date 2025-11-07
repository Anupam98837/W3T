{{-- resources/views/pages/users/admin/pages/course/viewCourse.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'View Course')

@section('content')
  @include('modules.course.viewCourse')
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>
@endsection
