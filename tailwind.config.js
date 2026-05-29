/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/views/**/*.php',
    './js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Tajawal', 'sans-serif'],
        mono: ['Tajawal', 'monospace'],
      },
      colors: {
        brand: {
          50: 'var(--brand-50, #1a050b)',
          100: 'var(--brand-100, #2d0814)',
          200: 'var(--brand-200, #4a0d20)',
          300: 'var(--brand-300, #7a1536)',
          400: 'var(--brand-400, #b31f50)',
          500: 'var(--brand-500, #ff2a85)',
          600: 'var(--brand-600, #ff5a9f)',
          700: 'var(--brand-700, #ff85ba)',
          800: 'var(--brand-800, #ffaed4)',
          900: 'var(--brand-900, #ffd6e8)',
        },
        accent: {
          50: 'var(--accent-50, #1a0510)',
          100: 'var(--accent-100, #2d0820)',
          200: 'var(--accent-200, #4a0d35)',
          300: 'var(--accent-300, #721550)',
          400: 'var(--accent-400, #a02070)',
          500: 'var(--accent-500, #d63384)',
          600: 'var(--accent-600, #e0559e)',
          700: 'var(--accent-700, #ea7ab5)',
          800: 'var(--accent-800, #f2a2cc)',
          900: 'var(--accent-900, #f9cde3)',
        },
        surface: 'var(--surface, #050505)',
        'surface-alt': 'var(--surface-alt, #0a0a0a)',
        ink: 'var(--ink, #ffffff)',
        'ink-muted': 'var(--ink-muted, #999999)',
        'ink-dim': 'var(--ink-dim, #666666)',
        glass: {
          bg: 'var(--glass-bg, rgba(20, 20, 20, 0.4))',
          border: 'var(--glass-border, rgba(255, 255, 255, 0.05))',
        },
      },
      backgroundImage: {
        'gradient-primary': 'var(--gradient-primary)',
        'gradient-hero': 'var(--gradient-hero)',
      },
      boxShadow: {
        'neon': 'var(--neon-glow)',
        'neon-strong': 'var(--neon-glow-strong)',
        'neon-text': 'var(--neon-text-shadow)',
        'accent-neon': 'var(--accent-glow)',
        'accent-neon-strong': 'var(--accent-glow-strong)',
        'glass': '0 4px 30px rgba(0, 0, 0, 0.5)',
      },
      animation: {
        'scan': 'scan 3s ease-in-out infinite alternate',
        'shine': 'shine 5s linear infinite',
        'float-slow': 'floatSlow 8s ease-in-out infinite',
        'float-fast': 'floatFast 4s ease-in-out infinite',
        'marquee-rtl': 'marqueeRtl 25s linear infinite',
        'marquee-ltr': 'marqueeLtr 25s linear infinite',
        'pulse-neon': 'pulseNeon 2s ease-in-out infinite',
        'page-in': 'pageIn 0.35s ease-out',
        'fade-up': 'fadeUp 0.6s ease-out',
        'ping-neon': 'pingNeon 1.5s cubic-bezier(0, 0, 0.2, 1) infinite',
        'rotate-slow': 'rotateSlow 20s linear infinite',
      },
      keyframes: {
        scan: {
          '0%': { top: '5%', opacity: '0' },
          '10%': { opacity: '1' },
          '90%': { opacity: '1' },
          '100%': { top: '95%', opacity: '0' },
        },
        shine: {
          to: { backgroundPosition: '200% center' },
        },
        floatSlow: {
          '0%, 100%': { transform: 'translate(0, 0)' },
          '50%': { transform: 'translate(-10px, -20px)' },
        },
        floatFast: {
          '0%, 100%': { transform: 'translate(0, 0)' },
          '50%': { transform: 'translate(15px, 15px)' },
        },
        marqueeRtl: {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(50%)' },
        },
        marqueeLtr: {
          '0%': { transform: 'translateX(-50%)' },
          '100%': { transform: 'translateX(0)' },
        },
        pulseNeon: {
          '0%, 100%': { boxShadow: 'var(--neon-glow)' },
          '50%': { boxShadow: 'var(--neon-glow-strong)' },
        },
        pageIn: {
          from: { opacity: '0', transform: 'translateY(8px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        fadeUp: {
          from: { opacity: '0', transform: 'translateY(20px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        pingNeon: {
          '75%, 100%': { transform: 'scale(2)', opacity: '0' },
        },
        rotateSlow: {
          to: { transform: 'rotate(360deg)' },
        },
      },
    },
  },
  plugins: [],
}
