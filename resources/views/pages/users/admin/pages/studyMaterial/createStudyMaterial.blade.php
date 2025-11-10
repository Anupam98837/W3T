{{-- resources/views/pages/users/admin/pages/users/manageUsers.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'Users')
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
