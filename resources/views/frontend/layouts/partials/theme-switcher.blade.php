{{-- ═══ Universal Theme Switcher: Architecture + Color + Font + Dark/Light ═══ --}}
<div id="themeSwitcher" class="fixed bottom-20 left-5 z-[9999] flex flex-col gap-3">
    <button onclick="toggleThemePalette()" id="themeToggleBtn" class="w-11 h-11 rounded-full flex items-center justify-center text-white/60 hover:text-white hover:scale-110 transition-all cursor-pointer shadow-lg border border-white/10" style="background:rgba(20,20,20,0.6); backdrop-filter:blur(8px);" title="تخصيص المظهر" aria-label="تخصيص المظهر">
        <i class="ph ph-paint-brush text-lg"></i>
    </button>

    {{-- Dark/Light Toggle --}}
    <button onclick="toggleDarkLight()" id="darkLightBtn" class="w-11 h-11 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-lg border border-white/10" style="background:rgba(20,20,20,0.6); backdrop-filter:blur(8px);" title="داكن / فاتح" aria-label="داكن / فاتح">
        <i id="darkLightIcon" class="ph ph-sun text-lg text-white/60"></i>
    </button>

    <div id="themePalette" class="hidden flex-col gap-4 p-4 rounded-2xl mb-2 min-w-[280px]" style="background:rgba(12,12,12,0.92); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.08); box-shadow:0 8px 40px rgba(0,0,0,0.6);">

        {{-- Architecture Picker --}}
        <div>
            <h6 class="text-[10px] font-bold uppercase tracking-widest text-ink-dim mb-3">نمط التصميم</h6>
            <div class="grid grid-cols-2 gap-2">
                @php
                $architectures = [
                    'cyber-lab' => ['name' => 'سايبر', 'icon' => 'ph ph-cpu', 'color' => '#ff2a85'],
                    'organic-spa' => ['name' => 'طبيعة', 'icon' => 'ph ph-leaf', 'color' => '#00ff88'],
                    'editorial' => ['name' => 'تحريري', 'icon' => 'ph ph-text-aa', 'color' => '#ffffff'],
                    'luxury-boutique' => ['name' => 'فخامة', 'icon' => 'ph ph-crown', 'color' => '#d4af37'],
                ];
                $currentArch = $layoutArchitecture ?? 'cyber-lab';
                @endphp
                @foreach($architectures as $key => $arch)
                <button onclick="switchArchitecture('{{ $key }}')" data-arch="{{ $key }}" class="arch-btn text-right p-2.5 rounded-xl border transition-all text-xs {{ $currentArch === $key ? 'border-white/40 bg-white/10' : 'border-white/5 bg-white/5 hover:border-white/20' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="{{ $arch['icon'] }} text-sm" style="color:{{ $arch['color'] }};"></i>
                        <span class="font-bold text-white">{{ $arch['name'] }}</span>
                    </div>
                    <span class="text-[10px] text-ink-dim">نمط {{ $arch['name'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Color Palette Picker --}}
        <div>
            <h6 class="text-[10px] font-bold uppercase tracking-widest text-ink-dim mb-3">لوحة الألوان</h6>
            <div class="flex gap-2 flex-wrap" id="colorPaletteContainer">
                @php
                $palettes = [
                    'rose' => ['name' => 'روز', 'color' => '#ff2a85', 'arch' => 'cyber-lab'],
                    'midnight' => ['name' => 'ليلي', 'color' => '#7c3aed', 'arch' => 'cyber-lab'],
                    'natural' => ['name' => 'طبيعي', 'color' => '#00ff88', 'arch' => 'organic-spa'],
                    'forest' => ['name' => 'غابة', 'color' => '#00c853', 'arch' => 'organic-spa'],
                    'minimal' => ['name' => 'مينيمال', 'color' => '#ffffff', 'arch' => 'editorial'],
                    'ocean' => ['name' => 'محيط', 'color' => '#00b4d8', 'arch' => 'editorial'],
                    'sunset' => ['name' => 'غروب', 'color' => '#ff6b35', 'arch' => 'luxury-boutique'],
                    'luxury' => ['name' => 'فخامة', 'color' => '#d4af37', 'arch' => 'luxury-boutique'],
                ];
                $currentColor = $siteSettings['site_theme'] ?? 'rose';
                @endphp
                @foreach($palettes as $key => $p)
                <button onclick="switchColor('{{ $key }}')" data-color="{{ $key }}" data-arch="{{ $p['arch'] }}" class="color-btn w-8 h-8 rounded-full border-2 transition-all {{ $currentColor === $key ? 'border-white scale-125' : 'border-transparent opacity-60 hover:opacity-100' }}" style="background:{{ $p['color'] }};" title="{{ $p['name'] }}" aria-label="لون {{ $p['name'] }}"></button>
                @endforeach
            </div>
        </div>

        {{-- Font Picker --}}
        <div>
            <h6 class="text-[10px] font-bold uppercase tracking-widest text-ink-dim mb-3">الخط</h6>
            <div class="flex gap-2 flex-wrap">
                @php
                $fonts = [
                    'Tajawal' => 'تجال',
                    'Cairo' => 'قاهرة',
                    'El+Messiri' => 'المسيري',
                    'Changa' => 'تشانغا',
                ];
                @endphp
                @foreach($fonts as $fontKey => $fontName)
                <button onclick="switchFont('{{ $fontKey }}')" data-font="{{ $fontKey }}" class="font-btn px-3 py-1.5 rounded-lg border border-white/10 text-xs text-white/60 hover:text-white hover:border-white/30 transition-all" style="font-family:'{{ str_replace('+',' ',$fontKey) }}',sans-serif;">{{ $fontName }}</button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
#themePalette { animation: fadeUpTheme 0.25s ease; }
@keyframes fadeUpTheme { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
#themeToggleBtn:hover { box-shadow: 0 0 16px rgba(255,255,255,0.15); }
</style>

<script>
function setCookie(name, value, days) { var d = new Date(); d.setTime(d.getTime() + (days*86400000)); document.cookie = name + '=' + value + ';path=/;expires=' + d.toUTCString(); }
function getCookie(name) { var n = name + '='; var ca = document.cookie.split(';'); for(var i=0; i<ca.length; i++) { var c = ca[i].trim(); if(c.indexOf(n)===0) return c.substring(n.length); } return null; }

function toggleDarkLight() {
    var html = document.documentElement;
    var isLight = html.getAttribute('data-theme-mode') === 'light';
    if (isLight) {
        html.removeAttribute('data-theme-mode');
        localStorage.setItem('شركة جنين للتجميل_mode', 'dark');
        setCookie('شركة جنين للتجميل_mode', 'dark', 365);
        updateDarkLightIcon(false);
    } else {
        html.setAttribute('data-theme-mode', 'light');
        localStorage.setItem('شركة جنين للتجميل_mode', 'light');
        setCookie('شركة جنين للتجميل_mode', 'light', 365);
        updateDarkLightIcon(true);
    }
}

function updateDarkLightIcon(isLight) {
    var icon = document.getElementById('darkLightIcon');
    if (icon) {
        icon.className = isLight ? 'ph ph-moon text-lg' : 'ph ph-sun text-lg';
        icon.style.color = isLight ? '#1c1917' : '';
    }
    var btn = document.getElementById('darkLightBtn');
    if (btn) {
        btn.style.background = isLight ? 'rgba(255,255,255,0.85)' : 'rgba(20,20,20,0.6)';
        btn.style.borderColor = isLight ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)';
    }
}

function toggleThemePalette() {
    var p = document.getElementById('themePalette');
    p.classList.contains('hidden') ? (p.classList.remove('hidden'), p.classList.add('flex')) : (p.classList.add('hidden'), p.classList.remove('flex'));
}

function switchArchitecture(arch) {
    var colorMap = { 'cyber-lab': ['rose','midnight'], 'organic-spa': ['natural','forest'], 'editorial': ['minimal','ocean'], 'luxury-boutique': ['sunset','luxury'] };
    var defaults = { 'cyber-lab': 'rose', 'organic-spa': 'natural', 'editorial': 'minimal', 'luxury-boutique': 'sunset' };
    var currentColor = localStorage.getItem('شركة جنين للتجميل_color') || defaults[arch] || 'rose';
    var compatibleColors = colorMap[arch] || [];
    // If current color doesn't belong to new architecture, switch to default color for that architecture
    if (compatibleColors.indexOf(currentColor) === -1) {
        currentColor = defaults[arch] || 'rose';
    }
    localStorage.setItem('شركة جنين للتجميل_architecture', arch);
    localStorage.setItem('شركة جنين للتجميل_color', currentColor);
    setCookie('شركة جنين للتجميل_arch', arch, 365);
    setCookie('شركة جنين للتجميل_color', currentColor, 365);
    location.reload();
}

function switchColor(color) {
    // Only change color, keep current architecture
    localStorage.setItem('شركة جنين للتجميل_color', color);
    setCookie('شركة جنين للتجميل_color', color, 365);
    // Do NOT change architecture cookie
    location.reload();
}

function switchFont(font) {
    localStorage.setItem('شركة جنين للتجميل_font', font);
    setCookie('شركة جنين للتجميل_font', font, 365);
    var link = document.getElementById('googleFontsLink');
    if(link) {
        link.href = 'https://fonts.googleapis.com/css2?family=' + font.replace('+','+') + ':wght@300;400;500;700;800;900&display=swap';
        document.body.style.fontFamily = "'" + font.replace('+',' ') + "', sans-serif";
    }
    var btns = document.querySelectorAll('.font-btn');
    btns.forEach(function(b) { b.classList.remove('text-white','border-white/30'); b.classList.add('text-white/60','border-white/10'); });
    var fb = document.querySelector('.font-btn[data-font="' + font + '"]');
    if(fb) fb.classList.add('text-white','border-white/30');
}

document.addEventListener('DOMContentLoaded', function() {
    var savedArch = localStorage.getItem('شركة جنين للتجميل_architecture') || 'cyber-lab';
    var savedColor = localStorage.getItem('شركة جنين للتجميل_color') || 'rose';
    var savedFont = localStorage.getItem('شركة جنين للتجميل_font') || 'Tajawal';
    var savedMode = localStorage.getItem('شركة جنين للتجميل_mode') || getCookie('شركة جنين للتجميل_mode') || 'dark';

    // Apply dark/light mode
    if (savedMode === 'light') {
        document.documentElement.setAttribute('data-theme-mode', 'light');
        updateDarkLightIcon(true);
    }

    var archBtns = document.querySelectorAll('.arch-btn');
    archBtns.forEach(function(b) { b.classList.remove('border-white/40','bg-white/10'); b.classList.add('border-white/5','bg-white/5'); });
    var ab = document.querySelector('.arch-btn[data-arch="' + savedArch + '"]');
    if(ab) { ab.classList.add('border-white/40','bg-white/10'); ab.classList.remove('border-white/5','bg-white/5'); }

    var colorBtns = document.querySelectorAll('.color-btn');
    colorBtns.forEach(function(b) { b.classList.remove('border-white','scale-125'); b.classList.add('border-transparent','opacity-60'); });
    var cb = document.querySelector('.color-btn[data-color="' + savedColor + '"]');
    if(cb) { cb.classList.add('border-white','scale-125'); cb.classList.remove('border-transparent','opacity-60'); }

    if(savedFont && savedFont !== 'Tajawal') {
        var link = document.getElementById('googleFontsLink');
        if(link) {
            link.href = 'https://fonts.googleapis.com/css2?family=' + savedFont.replace('+','+') + ':wght@300;400;500;700;800;900&display=swap';
            document.body.style.fontFamily = "'" + savedFont.replace('+',' ') + "', sans-serif";
        }
        var fb = document.querySelector('.font-btn[data-font="' + savedFont + '"]');
        if(fb) fb.classList.add('text-white','border-white/30');
    }
});

document.addEventListener('click', function(e) {
    var palette = document.getElementById('themePalette');
    var toggleBtn = document.getElementById('themeToggleBtn');
    if(palette && toggleBtn && !palette.contains(e.target) && !toggleBtn.contains(e.target)) {
        palette.classList.add('hidden'); palette.classList.remove('flex');
    }
});
</script>
