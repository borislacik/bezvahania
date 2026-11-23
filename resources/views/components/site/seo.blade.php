@props (['seo'])

<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}" />
<link rel="canonical" href="{{ $seo->canonicalUrl() }}" />
@if ($seo->noIndex || ! app()->isProduction())
    <meta name="robots" content="noindex, nofollow" />
@endif

<meta property="og:type" content="{{ $seo->type }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:locale" content="sk_SK" />
<meta property="og:title" content="{{ $seo->fullTitle() }}" />
<meta property="og:description" content="{{ $seo->description }}" />
<meta property="og:url" content="{{ $seo->canonicalUrl() }}" />
<meta property="og:image" content="{{ $seo->imageUrl() }}" />
<meta name="twitter:card" content="summary_large_image" />

@foreach ($seo->jsonLd as $jsonLd)
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endforeach
