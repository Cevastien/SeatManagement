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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
            },
            colors: {
                primary: {
                    DEFAULT: '#b5e4ff',
                    50: '#f0faff',
                    100: '#b5e4ff',
                    200: '#a8dcfa',
                    300: '#94d0f2',
                    400: '#6ab9e6',
                    500: '#3f9fd8',
                    600: '#0082cd',
                    700: '#006eb1',
                    800: '#005a95',
                    900: '#004a7a',
                    950: '#003a61',
                },
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
