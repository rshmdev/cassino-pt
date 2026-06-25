/** @type {import('tailwindcss').Config} */
// eslint-disable-next-line no-undef
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
        "./node_modules/flowbite/**/*.js",
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                // Cor primária controlada pelo painel admin (Custom Layout).
                // Lê os canais RGB injetados em :root via --ci-primary-rgb,
                // permitindo modificadores de opacidade (primary/10, primary/50...).
                primary: {
                    DEFAULT: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    50: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    100: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    200: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    300: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    400: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    500: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    600: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    700: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    800: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                    900: 'rgb(var(--ci-primary-rgb) / <alpha-value>)',
                },
                secondary: {
                    100: '#FCE7E7',
                    200: '#F9C1C1',
                    300: '#F59E9E',
                    400: '#F06565',
                    500: '#EF4444',
                    600: '#D73737',
                    700: '#B82727',
                    800: '#962121',
                    900: '#7C1616',
                },
                green: {
                    100: '#ECFDF5',
                    200: '#D1FAE5',
                    300: '#A7F3D0',
                    400: '#4ce325',
                    500: '#3BC117',
                    600: '#32a614',
                    700: '#206e0e',
                    800: '#184b0b',
                    900: '#133b0b',
                },
                gray: {
                    50: '#F9FAFB',
                    100: '#F3F4F6',
                    200: '#E5E7EB',
                    300: '#D1D5DB',
                    400: '#9CA3AF',
                    500: '#6B7280',
                    600: '#4B5563',
                    700: '#374151',
                    800: '#1F2937',
                    900: '#000000', // Pitch Black for deep contrast
                },
            },
        },
    },
    plugins: [
        require('flowbite/plugin')
    ],
}

