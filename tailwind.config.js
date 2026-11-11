/**
 * Tailwind CSS 4 compatibility configuration.
 * The semantic design tokens live in resources/css/app.css via @theme.
 */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './vendor/livewire/flux/resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                base: 'var(--bg-base)',
                'base-end': 'var(--bg-base-end)',
                surface: 'var(--bg-surface)',
                'surface-raised': 'var(--bg-surface-raised)',
                ink: 'var(--text-primary)',
                muted: 'var(--text-muted)',
                line: 'var(--border-subtle)',
                accent: 'var(--accent)',
                'accent-strong': 'var(--accent-strong)',
                warning: 'var(--warning)',
                danger: 'var(--danger)',
            },
        },
    },
};
