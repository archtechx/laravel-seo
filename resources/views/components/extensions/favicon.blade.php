@if ($favicon = seo('favicon'))
    <link rel="icon" href="{{ $favicon }}">
@else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
@endif
