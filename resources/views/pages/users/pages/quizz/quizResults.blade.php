@extends('pages.users.layout.structure')

@section('title', 'Quiz Results')

@include('modules.quizz.quizResults')

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>
@endsection
