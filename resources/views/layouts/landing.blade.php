<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 1. BASIC SEO --}}
    <title>{{ $title ?? 'Website Roov - Jual Beli Properti' }}</title>
    <meta name="description" content="{{ $description ?? 'Temukan properti impian Anda di Surabaya dan sekitarnya. Rumah, Apartemen, dan Tanah terbaik.' }}">
    <meta name="keywords" content="properti surabaya, jual rumah, cari rumah, roov property, agen properti">
    <meta name="author" content="Roov Property">

    {{-- 2. SOCIAL MEDIA / WHATSAPP PREVIEW (Open Graph) --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? 'Website Roov Property' }}">
    <meta property="og:description" content="{{ $description ?? 'Portal properti terpercaya di Surabaya.' }}">
    <meta property="og:image" content="{{ $image ?? asset('images/default-og.jpg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Slot untuk Script Tambahan (JSON-LD) --}}
    {{ $head ?? '' }}
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50 flex flex-col min-h-screen">
    
    <main class="flex-grow">
        {{ $slot }}
    </main>

</body>
</html>