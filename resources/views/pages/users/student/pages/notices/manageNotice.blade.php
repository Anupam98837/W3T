{{-- resources/views/pages/users/admin/pages/users/manageUsers.blade.php --}}
@extends('pages.users.student.layout.structure')

@section('title', 'Notice')
@section('header', 'Manage Notice')

@section('content')
  @include('modules.notices.manageNotice')
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
