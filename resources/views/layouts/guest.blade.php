<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            body { 
                font-family: 'Inter', sans-serif; 
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            }
            .login-container {
                background: #f1f5f9;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                border: 2px solid #e2e8f0;
            }
            .brand-button {
                background: #111827;
                color: white;
                border: 2px solid #111827;
                transition: all 0.2s ease;
            }
            .brand-button:hover {
                background: #374151;
                border-color: #374151;
                transform: translateY(-1px);
            }
            .form-input {
                border: 2px solid #e5e7eb;
                transition: all 0.2s ease;
            }
            .form-input:focus {
                border-color: #111827;
                outline: none;
                box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.1);
            }
        </style>
    </head>
    <body class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="w-full max-w-md">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-gray-800 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">SEAT MANAGEMENT</h1>
                        <p class="text-sm text-gray-600">SYSTEM</p>
                    </div>
                </div>
                <div class="text-lg text-gray-700 font-semibold">Admin Login</div>
            </div>

            <!-- Login Form -->
            <div class="login-container px-8 py-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
