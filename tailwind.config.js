/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts}',
    ],
    theme: {
        extend: {
            colors: {
                'debugbar': {
                    'bg': '#1a202c',
                    'panel': '#2d3748',
                    'border': '#4a5568',
                    'text': '#e2e8f0',
                    'primary': '#3182ce',
                    'success': '#38a169',
                    'warning': '#d69e2e',
                    'error': '#e53e3e',
                    'muted': '#718096',
                }
            },
            fontFamily: {
                'mono': [
                    'ui-monospace',
                    'SFMono-Regular',
                    '"SF Mono"',
                    'Consolas',
                    '"Liberation Mono"',
                    'Menlo',
                    'monospace'
                ],
            },
            fontSize: {
                'xs': ['0.75rem', { lineHeight: '1rem' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem' }],
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
            },
            zIndex: {
                '60': '60',
                '70': '70',
                '80': '80',
                '90': '90',
                '100': '100',
            },
            animation: {
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'bounce-subtle': 'bounce 1s ease-in-out 2',
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.3s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(100%)' },
                    '100%': { transform: 'translateY(0)' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-100%)' },
                    '100%': { transform: 'translateY(0)' },
                },
            },
            backdropBlur: {
                'xs': '2px',
            },
            screens: {
                'xs': '475px',
            },
            maxHeight: {
                '18': '4.5rem',
                '32': '8rem',
                '40': '10rem',
                '48': '12rem',
                '56': '14rem',
                '64': '16rem',
                '72': '18rem',
                '80': '20rem',
                '88': '22rem',
                '96': '24rem',
            },
            minHeight: {
                '8': '2rem',
                '12': '3rem',
                '16': '4rem',
                '20': '5rem',
            },
            boxShadow: {
                'debugbar': '0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06)',
                'inner-lg': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.1)',
            },
            borderRadius: {
                'sm': '0.125rem',
                'DEFAULT': '0.25rem',
                'md': '0.375rem',
                'lg': '0.5rem',
                'xl': '0.75rem',
                '2xl': '1rem',
            },
            transitionProperty: {
                'height': 'height',
                'spacing': 'margin, padding',
                'colors': 'color, background-color, border-color, text-decoration-color, fill, stroke',
            },
            transitionDuration: {
                '250': '250ms',
                '350': '350ms',
                '400': '400ms',
                '450': '450ms',
                '600': '600ms',
                '800': '800ms',
                '900': '900ms',
            },
            transitionTimingFunction: {
                'bounce': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
        function({ addUtilities, theme }) {
            const newUtilities = {
                '.scrollbar-thin': {
                    scrollbarWidth: 'thin',
                    '&::-webkit-scrollbar': {
                        width: '6px',
                        height: '6px',
                    },
                    '&::-webkit-scrollbar-track': {
                        backgroundColor: theme('colors.gray.800'),
                    },
                    '&::-webkit-scrollbar-thumb': {
                        backgroundColor: theme('colors.gray.600'),
                        borderRadius: theme('borderRadius.DEFAULT'),
                    },
                    '&::-webkit-scrollbar-thumb:hover': {
                        backgroundColor: theme('colors.gray.500'),
                    },
                },
                '.scrollbar-none': {
                    scrollbarWidth: 'none',
                    '&::-webkit-scrollbar': {
                        display: 'none',
                    },
                },
            };
            addUtilities(newUtilities);
        },
    ],
    darkMode: 'class',
    corePlugins: {
        // Désactiver les preflight pour éviter les conflits
        preflight: false,
    },
};
