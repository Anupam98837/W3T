{{-- resources/views/dashboard.blade.php --}}

@extends('pages.users.admin.layout.structure')

@section('title', 'manage Blog')
@section('header', 'Dashboard')

@section('content')
@include('modules.blog.manageBlog')
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
