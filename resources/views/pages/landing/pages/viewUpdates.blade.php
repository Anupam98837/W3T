{{-- resources/views/pages/users/admin/pages/course/viewCourse.blade.php --}}
@include('pages.landing.components.header')

{{-- Page Title --}}
<title>View Course</title>

{{-- Page Content --}}
@include('modules.landingPages.viewUpdates')

{{-- Page Scripts --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>

@include('pages.landing.components.footer')
