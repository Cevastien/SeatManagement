<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="kiosk-mode" content="true">
    
    <title>{{ config('app.name', 'Restaurant Kiosk') }}</title>
    
    <!-- Prevent zooming and scrolling on touch devices -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/kiosk.css', 'resources/js/app.js'])
    
    <style>
        /* Kiosk-specific styles */
        html, body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            margin: 0;
            padding: 0;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Prevent context menu on long press */
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow text selection in input fields */
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Full screen kiosk mode */
        .kiosk-container {
            height: 100vh;
            width: 100vw;
            position: fixed;
            top: 0;
            left: 0;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Large touch targets */
        .kiosk-button {
            min-height: 80px;
            min-width: 200px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .kiosk-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .kiosk-button:active {
            transform: translateY(0);
        }
        
        /* Typography scale */
        .kiosk-heading {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
        }
        
        .kiosk-subheading {
            font-size: 24px;
            font-weight: 500;
            line-height: 1.3;
        }
        
        .kiosk-body {
            font-size: 18px;
            font-weight: 400;
            line-height: 1.5;
        }
        
        .kiosk-small {
            font-size: 14px;
            font-weight: 400;
        }
        
        /* Card styling */
        .kiosk-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        /* Kiosk theme background */
        .kiosk-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3730a3 100%);
        }
        
        /* 900x600 kiosk optimization */
        @media (max-width: 900px) and (max-height: 600px) {
            .kiosk-heading {
                font-size: 36px;
            }
            
            .kiosk-subheading {
                font-size: 18px;
            }
            
            .kiosk-body {
                font-size: 16px;
            }
            
            .kiosk-button {
                min-height: 60px;
                min-width: 150px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body class="font-inter antialiased kiosk-bg">
    <div class="kiosk-container">
        {{ $slot }}
    </div>
    
    <!-- Kiosk-specific JavaScript -->
    <script>
        // Prevent right-click context menu
        document.addEventListener('contextmenu', e => e.preventDefault());
        
        // Prevent zoom on double-tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Auto-refresh every 5 minutes to prevent session timeout
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                window.location.reload();
            }
        }, 300000);
        
        // Prevent navigation away from kiosk
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>

