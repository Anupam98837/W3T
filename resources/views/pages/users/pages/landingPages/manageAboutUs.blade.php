{{-- pages/users/super_admin/pages/mailers/manageMailers.blade.php --}}
@extends('pages.users.layout.structure')

@section('title','About Us')

@push('styles')
  {{-- If your layout already loads main.css, you can remove this line --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
@endpush

@section('content')
  @include('modules.landingPages.manageAboutUs')
@endsection
