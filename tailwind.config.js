/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './app/Views/**/*.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                accent: {
                    200: '#a5f3fc',
                    500: '#06b6d4',
                },
            },
            boxShadow: {
                soft: '0 4px 16px -4px rgb(0 0 0 / 0.10)',
                'soft-lg': '0 12px 32px -8px rgb(0 0 0 / 0.14)',
            },
        },
    },
    plugins: [],
};
