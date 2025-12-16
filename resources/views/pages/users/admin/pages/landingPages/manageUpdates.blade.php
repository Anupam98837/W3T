{{-- pages/users/super_admin/pages/mailers/manageMailers.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title','Updates')

@push('styles')
  {{-- If your layout already loads main.css, you can remove this line --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
@endpush

@section('content')
  @include('modules.landingPages.manageUpdates')
@endsection
