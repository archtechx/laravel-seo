<title>@seo('title')</title>
<meta property="og:type" content="website" />
@if(seo('site')) <meta property="og:site_name" content="@seo('site')"> @endif
@if(seo('title')) <meta property="og:title" content="@seo('title')" /> @endif
@if(seo('description')) <meta property="og:description" content="@seo('description')" /> @endif
@if(seo('image')) <meta property="og:image" content="@seo('image')" /> @endif

@foreach(seo()->tags() as $tag)
    {!! $tag !!}
@endforeach

@foreach(seo()->extensions() as $extension)
    <x-dynamic-component :component="$extension" />
@endforeach
