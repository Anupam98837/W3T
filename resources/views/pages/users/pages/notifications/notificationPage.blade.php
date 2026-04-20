{{-- pages/users/admin/pages/privileges/assignPrivileges --}}
@extends('pages.users.layout.structure')

@section('title','Notifications')

@push('styles')
  {{-- If your layout already loads main.css, you can remove this line --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
@endpush

@section('content')
  @include('modules.notifications.notificationPage')
@endsection
