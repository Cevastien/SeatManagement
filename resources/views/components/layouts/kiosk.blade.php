<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Guest Waitlist - Kiosk' }}</title>

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        
        <!-- Tailwind Config -->
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#1e293b',
                            'primary-dark': '#0f172a',
                            secondary: '#2c3e50',
                            accent: '#f39c12',
                            neutral: '#f5f7fa',
                            'neutral-dark': '#e3e8ef'
                        },
                        fontFamily: {
                            'inter': ['Inter', 'sans-serif']
                        }
                    }
                }
            }
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-inter antialiased">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
