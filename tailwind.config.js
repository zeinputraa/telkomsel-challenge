import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'SF Pro Display',
                    '-apple-system',
                    'BlinkMacSystemFont',
                    'Segoe UI',
                    'Roboto',
                    'Helvetica',
                    'Arial',
                    'sans-serif',
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            colors: {
                telkom: {
                    50:  '#fff1f1',
                    100: '#ffe0e0',
                    200: '#ffc5c5',
                    300: '#ff9d9d',
                    400: '#ff6464',
                    500: '#ff3333',
                    600: '#E10600',
                    700: '#B80000',
                    800: '#970000',
                    900: '#7d0000',
                    950: '#450000',
                },
                sidebar: {
                    DEFAULT: '#0F1117',
                    hover:   '#1A1D27',
                    active:  '#1E2130',
                    border:  '#1E2130',
                },
                surface: '#F5F6FA',
            },
            boxShadow: {
                card: '0 1px 3px 0 rgba(0,0,0,0.07), 0 1px 2px -1px rgba(0,0,0,0.07)',
                'card-hover': '0 4px 12px 0 rgba(0,0,0,0.10)',
            },
            borderRadius: {
                xl: '0.75rem',
                '2xl': '1rem',
            },
        },
    },

    plugins: [forms],
};
