@if(seo('title'))
    <title>@seo('title')</title>
    <meta property="og:title" content="@seo('title')" />
@endif

@if(seo('description')) 
    <meta property="og:description" content="@seo('description')" />
    <meta name="description" content="@seo('description')" />
@endif

<meta property="og:type" content="website" />

@if(seo('site')) <meta property="og:site_name" content="@seo('site')"> @endif

@if(seo('image')) <meta property="og:image" content="@seo('image')" /> @endif

@if(seo('url'))
    <meta property="og:url" content="@seo('url')" />
    <link rel="canonical" href="@seo('url')" />
@endif

@foreach(seo()->tags() as $tag)
    {!! $tag !!}
@endforeach

@foreach(seo()->extensions() as $extension)
    <x-dynamic-component :component="$extension" />
@endforeach
