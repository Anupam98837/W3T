{{-- resources/views/pages/users/admin/pages/users/manageUsers.blade.php --}}
@extends('pages.users.instructor.layout.structure')

@section('title', 'Study Material')
@section('header', 'Create Study Material')

@section('content')
  @include('modules.studyMaterial.createStudyMaterial')
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>
@endsection
