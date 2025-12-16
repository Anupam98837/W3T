{{-- resources/views/dashboard.blade.php --}}

@extends('pages.users.admin.layout.structure')

@section('title', 'Manage Coding Questions')
@section('header', 'Dashboard')

@section('content')
@include('modules.codingQuestions.manageCodingQuestions')
@endsection

@section('scripts')
<script>
  // On DOM ready, verify token; if missing, redirect home
  document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>
@endsection
