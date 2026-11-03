<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            html {
                background-color: #FBF5EE;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/apple-touch-icon.png">
        <link rel="manifest" href="/assets/images/site.webmanifest">
        <meta name="theme-color" content="#FBF5EE">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @php
            // Crawlers (Facebook, LinkedIn, Slack…) don't run JS, so the share tags must be in the server HTML.
            $isLanding = $page['component'] === 'Landing';
            $metaLocale = $isLanding ? ($page['props']['locale'] ?? app()->getLocale()) : app()->getLocale();
            $metaTitle = __('meta.title', [], $metaLocale);
            $metaDescription = __('meta.description', [], $metaLocale);
            $metaImage = url('/assets/images/og-image.png');
            $metaUrl = $isLanding ? ($page['props']['urls'][$metaLocale] ?? url()->current()) : url()->current();
        @endphp
        <x-inertia::head>
            <title>{{ $isLanding ? $metaTitle : config('app.name', 'Abrio') }}</title>
            <meta head-key="description" name="description" content="{{ $metaDescription }}">
            <meta head-key="og:site_name" property="og:site_name" content="{{ config('app.name', 'Abrio') }}">
            <meta head-key="og:type" property="og:type" content="website">
            <meta head-key="og:url" property="og:url" content="{{ $metaUrl }}">
            <meta head-key="og:title" property="og:title" content="{{ $isLanding ? $metaTitle : config('app.name', 'Abrio') }}">
            <meta head-key="og:description" property="og:description" content="{{ $metaDescription }}">
            <meta head-key="og:locale" property="og:locale" content="{{ __('meta.locale', [], $metaLocale) }}">
            <meta head-key="og:image" property="og:image" content="{{ $metaImage }}">
            <meta head-key="og:image:width" property="og:image:width" content="1200">
            <meta head-key="og:image:height" property="og:image:height" content="630">
            <meta head-key="og:image:alt" property="og:image:alt" content="{{ __('meta.image_alt', [], $metaLocale) }}">
            <meta head-key="twitter:card" name="twitter:card" content="summary_large_image">
            <meta head-key="twitter:title" name="twitter:title" content="{{ $isLanding ? $metaTitle : config('app.name', 'Abrio') }}">
            <meta head-key="twitter:description" name="twitter:description" content="{{ $metaDescription }}">
            <meta head-key="twitter:image" name="twitter:image" content="{{ $metaImage }}">
            @if ($isLanding)
                <link head-key="canonical" rel="canonical" href="{{ $metaUrl }}">
                <link head-key="alt-fr" rel="alternate" hreflang="fr" href="{{ $page['props']['urls']['fr'] ?? url('/') }}">
                <link head-key="alt-en" rel="alternate" hreflang="en" href="{{ $page['props']['urls']['en'] ?? url('/en') }}">
                <link head-key="alt-default" rel="alternate" hreflang="x-default" href="{{ $page['props']['urls']['fr'] ?? url('/') }}">
            @endif
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
