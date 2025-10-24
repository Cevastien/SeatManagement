import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './public/**/*.html',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
                heading: ['Inter', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
            },
            colors: {
                // Brand Colors
                primary: {
                    DEFAULT: '#09121E',
                    light: '#1a2332',
                    dark: '#000814',
                    50: '#f5f7fa',
                    100: '#ebeef3',
                    200: '#d3dae5',
                    300: '#adb9cd',
                    400: '#8093b0',
                    500: '#5f7496',
                    600: '#4a5b7d',
                    700: '#3c4a65',
                    800: '#343f55',
                    900: '#2f3749',
                    950: '#09121E',
                },
                // Accent Colors
                accent: {
                    DEFAULT: '#EEEDE7',
                    cream: '#EEEDE7',
                    yellow: '#FDB813',
                    orange: '#F59E0B',
                },
                // Status Colors (replacing red)
                status: {
                    success: '#10B981',
                    warning: '#F59E0B',
                    danger: '#DC2626',
                    info: '#3B82F6',
                },
                // UI Colors
                kiosk: {
                    dark: '#111827',
                    light: '#E6E6E6',
                }
            },
            fontSize: {
                'kiosk-heading': ['48px', { lineHeight: '1.1', fontWeight: '800' }],
                'kiosk-subheading': ['24px', { lineHeight: '1.3', fontWeight: '500' }],
                'kiosk-body': ['18px', { lineHeight: '1.5', fontWeight: '400' }],
                'kiosk-small': ['14px', { lineHeight: '1.4', fontWeight: '400' }],
            },
            minHeight: {
                'kiosk-button': '80px',
            },
            minWidth: {
                'kiosk-button': '200px',
            },
            animation: {
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
        },
    },

    plugins: [forms],
};
