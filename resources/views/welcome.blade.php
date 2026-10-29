<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OrbitEntry &bull; Visitor Management</title>
    <link rel="icon" type="image/png" href="{{ asset('images/orbitentry-favicon.png') }}">
    
    <!-- Clean Industry Standard Fonts: Inter & Roboto Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        code, kbd, samp, pre, .font-mono {
            font-family: 'Roboto Mono', monospace;
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-900 antialiased">
    <livewire:visitor-kiosk />

    @livewireScripts
</body>
</html>
