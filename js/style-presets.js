/**
 * SkinAnalyzer Style Presets
 * Maps 5 visual styles to existing CSS themes + layouts + fonts
 */
const STYLE_PRESETS = {
    medicore: {
        label: 'MediCore',
        labelAr: 'الطبي الأنيق',
        layout: 'editorial',
        color: 'ocean',
        font: 'Cairo',
        description: 'Professional, clean, clinical',
        descriptionAr: 'احترافي، نظيف، سريري',
    },
    obsidian: {
        label: 'Obsidian',
        labelAr: 'المعدني الداكن',
        layout: 'cyber-lab',
        color: 'midnight',
        font: 'El Messiri',
        description: 'Premium, tech-forward, dark',
        descriptionAr: 'فاخر، تقني، داكن',
    },
    pristine: {
        label: 'Pristine',
        labelAr: 'النقي الطبيعي',
        layout: 'organic-spa',
        color: 'natural',
        font: 'Tajawal',
        description: 'Organic, calming, natural',
        descriptionAr: 'عضوي، مريح، طبيعي',
    },
    neonderm: {
        label: 'NeonDerm',
        labelAr: 'العصري الجريء',
        layout: 'cyber-lab',
        color: 'rose',
        font: 'Cairo',
        description: 'Young, vibrant, bold',
        descriptionAr: 'شاب، حيوي، جريء',
    },
    pearlwhite: {
        label: 'PearlWhite',
        labelAr: 'اللؤلؤي الفاخر',
        layout: 'luxury-boutique',
        color: 'luxury',
        font: 'El Messiri',
        description: 'Elegant, luxurious, classic',
        descriptionAr: 'أنيق، فاخر، كلاسيكي',
    },
};

function applyStylePreset(presetKey) {
    const preset = STYLE_PRESETS[presetKey];
    if (!preset) return;

    localStorage.setItem('style_preset', presetKey);
    localStorage.setItem('layout', preset.layout);
    localStorage.setItem('theme_color', preset.color);
    localStorage.setItem('font', preset.font);

    document.documentElement.setAttribute('data-layout', preset.layout);
    document.documentElement.setAttribute('data-theme-color', preset.color);

    loadThemeCSS(preset.color);
    setLayout(preset.layout);
    setFont(preset.font);

    dispatchEvent(new CustomEvent('style-preset-changed', {
        detail: { preset: presetKey, ...preset },
    }));
}

function getActivePreset() {
    const saved = localStorage.getItem('style_preset');
    if (saved && STYLE_PRESETS[saved]) return saved;
    return 'medicore';
}

function loadThemeCSS(color) {
    const existing = document.getElementById('theme-css');
    if (existing) existing.remove();

    const link = document.createElement('link');
    link.id = 'theme-css';
    link.rel = 'stylesheet';
    link.href = `/css/themes/${color}.css`;
    document.head.appendChild(link);
}

function setLayout(layout) {
    // The layout is typically handled by Blade views,
    // but we set the data attribute for CSS targeting
    document.documentElement.setAttribute('data-layout', layout);
}

function setFont(font) {
    const fontMap = {
        'Tajawal': 'Tajawal',
        'Cairo': 'Cairo',
        'El Messiri': 'El Messiri',
        'Changa': 'Changa',
        'Inter': 'Inter',
        'Poppins': 'Poppins',
    };
    const cssFont = fontMap[font] || 'Tajawal';
    document.documentElement.style.setProperty('--font-family', cssFont);
    document.body.style.fontFamily = `'${cssFont}', sans-serif`;
}

// Initialize from saved preset on load
document.addEventListener('DOMContentLoaded', () => {
    const saved = getActivePreset();
    if (saved !== 'medicore') {
        applyStylePreset(saved);
    }
});

// Export for use in theme switcher UI
if (typeof window !== 'undefined') {
    window.STYLE_PRESETS = STYLE_PRESETS;
    window.applyStylePreset = applyStylePreset;
    window.getActivePreset = getActivePreset;
}
