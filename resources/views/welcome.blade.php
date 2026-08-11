<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FoodFlow - Restoran boshqaruv tizimi</title>
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="manifest" href="/manifest.json">
        
        <!-- PWA / iOS Support -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="FoodFlow">
        <link rel="apple-touch-icon" href="/foodflow_logo.png">
        <meta name="theme-color" content="#ffffff">

        <!-- Google Fonts (Outfit) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 text-slate-900 font-sans antialiased selection:bg-slate-900 selection:text-white">
        <div id="app"></div>
    </body>
</html>
