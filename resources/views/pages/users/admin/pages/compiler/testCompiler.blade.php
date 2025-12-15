{{-- resources/views/dashboard.blade.php --}}

@extends('pages.users.admin.layout.structure')

@section('title', 'Topic')
@section('header', 'Dashboard')

@section('content')
@include('modules.compiler.testCompiler')
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
