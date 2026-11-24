@php
    $routeName = request()->routeIs('*.map') ? 'map' : 'landing';
    $ogImage = file_exists(public_path('images/og.jpg')) ? url('images/og.jpg') : (file_exists(public_path('images/hero.webp')) ? url('images/hero.webp') : null);
    $pageTitle = $routeName === 'map' ? __('atlas.map_title') : __('atlas.title');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ __('atlas.description') }}">
<link rel="canonical" href="{{ route($lang.'.'.$routeName) }}">
<link rel="alternate" hreflang="ar" href="{{ route('ar.'.$routeName) }}">
<link rel="alternate" hreflang="en" href="{{ route('en.'.$routeName) }}">
<link rel="alternate" hreflang="x-default" href="{{ route('ar.'.$routeName) }}">
<meta property="og:site_name" content="{{ __('atlas.name') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ __('atlas.description') }}">
<meta property="og:url" content="{{ route($lang.'.'.$routeName) }}">
<meta property="og:locale" content="{{ $lang === 'ar' ? 'ar_AR' : 'en_US' }}">
@if ($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $ogImage }}">
@endif
<meta name="theme-color" content="#0b0a0f">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
