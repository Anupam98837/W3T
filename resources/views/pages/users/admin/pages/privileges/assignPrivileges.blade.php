{{-- pages/users/admin/pages/privileges/assignPrivileges --}}
@extends('pages.users.admin.layout.structure')

@section('title','Module')
@section('subtitle','Create, edit and manage module')

@push('styles')
  {{-- If your layout already loads main.css, you can remove this line --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
@endpush

@section('content')
  @include('modules.privileges.assignPrivileges')
@endsection
