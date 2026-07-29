import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Cairo', ...defaultTheme.fontFamily.sans],
            },

            /*
            |--------------------------------------------------------------------------
            | Theme tokens → CSS variables
            |--------------------------------------------------------------------------
            | Never hardcode brand colors in components. Override variables per clinic.
            */
            colors: {
                primary: {
                    DEFAULT: 'var(--color-primary)',
                    soft: 'var(--color-primary-soft)',
                    muted: 'var(--color-primary-muted)',
                    hover: 'var(--color-primary-hover)',
                    foreground: 'var(--color-primary-foreground)',
                },
                secondary: {
                    DEFAULT: 'var(--color-secondary)',
                    soft: 'var(--color-secondary-soft)',
                    hover: 'var(--color-secondary-hover)',
                    foreground: 'var(--color-secondary-foreground)',
                },
                accent: {
                    DEFAULT: 'var(--color-accent)',
                    soft: 'var(--color-accent-soft)',
                    foreground: 'var(--color-accent-foreground)',
                },
                sidebar: {
                    DEFAULT: 'var(--color-sidebar)',
                    foreground: 'var(--color-sidebar-foreground)',
                    muted: 'var(--color-sidebar-muted)',
                    border: 'var(--color-sidebar-border)',
                },
                navbar: {
                    DEFAULT: 'var(--color-navbar)',
                    foreground: 'var(--color-navbar-foreground)',
                    border: 'var(--color-navbar-border)',
                },
                success: {
                    DEFAULT: 'var(--color-success)',
                    soft: 'var(--color-success-soft)',
                    foreground: 'var(--color-success-foreground)',
                },
                warning: {
                    DEFAULT: 'var(--color-warning)',
                    soft: 'var(--color-warning-soft)',
                    foreground: 'var(--color-warning-foreground)',
                },
                danger: {
                    DEFAULT: 'var(--color-danger)',
                    soft: 'var(--color-danger-soft)',
                    foreground: 'var(--color-danger-foreground)',
                },
                background: 'var(--color-background)',
                surface: {
                    DEFAULT: 'var(--color-surface)',
                    muted: 'var(--color-surface-muted)',
                    subtle: 'var(--color-surface-subtle)',
                },
                border: 'var(--color-border)',
                foreground: {
                    DEFAULT: 'var(--color-text)',
                    muted: 'var(--color-text-muted)',
                    subtle: 'var(--color-text-subtle)',
                },
                overlay: 'var(--color-overlay)',
            },

            boxShadow: {
                soft: '0 1px 2px 0 rgb(31 26 23 / 0.03), 0 1px 3px 0 rgb(31 26 23 / 0.05)',
                'soft-md': '0 4px 12px -2px rgb(31 26 23 / 0.06), 0 2px 6px -2px rgb(31 26 23 / 0.04)',
                'soft-lg': '0 12px 24px -6px rgb(31 26 23 / 0.08), 0 4px 10px -4px rgb(31 26 23 / 0.04)',
            },

            borderRadius: {
                ds: 'var(--radius)',
                'ds-lg': 'var(--radius-lg)',
            },
        },
    },

    plugins: [forms],
};
