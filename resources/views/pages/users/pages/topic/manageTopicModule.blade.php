{{-- resources/views/dashboard.blade.php --}}

@extends('pages.users.layout.structure')

@section('title', 'Topic')
@section('header', 'Dashboard')

@section('content')
@include('modules.topic.manageTopicModule')
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
