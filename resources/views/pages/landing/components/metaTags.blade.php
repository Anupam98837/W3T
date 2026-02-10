@php
use Illuminate\Support\Facades\DB;

$pageLink = $pageLink ?? '/';

$currentPath = '/' . ltrim(request()->path() ?? '', '/');
if ($currentPath === '//') $currentPath = '/';

$full = url()->current();
$fullNoTrail = rtrim($full, '/');

$candidates = array_values(array_unique(array_filter([
  $pageLink,
  rtrim($pageLink, '/'),
  $currentPath,
  rtrim($currentPath, '/'),
  $full,
  $fullNoTrail,
  '/',
  'home',
  '/home',
])));

$rows = DB::table('meta_tags')
  ->whereIn('page_link', $candidates)
  ->orderByDesc('updated_at')
  ->limit(200)
  ->get();

$seen = [];
@endphp

@foreach($rows as $t)
  @php
    $type = strtolower(trim((string)($t->tag_type ?? 'standard')));
    $attr = trim((string)($t->tag_attribute ?? ''));
    $val  = trim((string)($t->tag_attribute_value ?? ''));

    if ($attr === '') continue;

    // normalize type
    if ($type === 'og' || $type === 'open_graph' || $type === 'opengraph') $type = 'opengraph';
    if ($type === 'http' || $type === 'http_equiv' || $type === 'http-equiv') $type = 'http';
    if ($type === 'name') $type = (str_starts_with(strtolower($attr), 'twitter:') ? 'twitter' : 'standard');
    if (str_starts_with(strtolower($attr), 'og:')) $type = 'opengraph';
    if (str_starts_with(strtolower($attr), 'twitter:')) $type = 'twitter';

    $key = $type . '::' . strtolower($attr);
    if (isset($seen[$key])) continue;
    $seen[$key] = 1;
  @endphp

  @if($type === 'charset')
    {{-- You already have <meta charset="UTF-8"> in home.blade.php, so skip --}}
  @elseif($type === 'opengraph')
    <meta property="{{ e($attr) }}" content="{{ e($val) }}">
  @elseif($type === 'http')
    <meta http-equiv="{{ e($attr) }}" content="{{ e($val) }}">
  @else
    <meta name="{{ e($attr) }}" content="{{ e($val) }}">
  @endif
@endforeach
