<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <meta name="description" content="Welcome to the official portal of Ideal Professional Institute (IPI). Access our Notice Board, Student Results, and explore Campus Life gallery.">
        <meta name="keywords" content="Ideal Professional Institute, IPI, Notice Board, Result Portal, Campus Life, IPI Notices, Education Portal">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::first()?->favicon_path ? asset('storage/' . \App\Models\SiteSetting::first()->favicon_path) : asset('favicon.ico') }}">

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
