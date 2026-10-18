@php
    // public/build is gitignored, so a deploy that pulls the code without
    // running `npm run build` has no manifest. Saying so beats a stack trace.
    $assetsBuilt = is_file(public_path('hot')) || is_file(public_path('build/manifest.json'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>INTI Gym Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">

    @if ($assetsBuilt)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @inertiaHead
</head>
<body class="antialiased">
    @if ($assetsBuilt)
        @inertia
    @else
        <div style="max-width: 34rem; margin: 4rem auto; padding: 0 1.5rem; font-family: ui-sans-serif, system-ui, sans-serif; color: #e7e7ea;">
            <h1 style="font-size: 1.125rem; font-weight: 600;">The admin assets have not been built.</h1>
            <p style="margin-top: .75rem; font-size: .875rem; color: #a1a1aa; line-height: 1.6;">
                Run <code style="background:#17171a; border:1px solid #26262b; padding:.1rem .35rem;">npm install &amp;&amp; npm run build</code>
                on the server, then reload this page. <code style="background:#17171a; border:1px solid #26262b; padding:.1rem .35rem;">composer setup</code>
                does both in one step.
            </p>
        </div>
    @endif
</body>
</html>
