<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:url" content="https://hyperbolus.net">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Hyperbolus">
    <meta property="og:title" content="{{ $page['props']['__meta_title'] ?? 'Hyperbolus' }}">
    <meta property="og:description" content="{{ $page['props']['__meta_description'] ?? 'Your source for everything Geometry Dash' }}">

    <link rel="icon" type="image/svg+xml" href="/icon.svg"/>
    <link rel="manifest" href="/site.webmanifest">
    <!-- TODO: -->
    <!-- <link rel="canonical" href="https://example.com/dresses/green-dresses" /> -->

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Scripts -->
    @routes
    @vite('resources/js/app.js')
    @inertiaHead
</head>
<body id="body" class="font-sans antialiased dark bg-ui-950">
@inertia
</body>
</html>
