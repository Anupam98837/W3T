{{-- resources/views/dashboard.blade.php --}}

@extends('pages.users.layout.structure')

@section('title', 'Test compiler')
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
