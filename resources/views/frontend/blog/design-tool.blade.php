@extends('frontend.layouts.editorial.app')

@section('title', 'أداة تصميم المقالات | مدونة جنين للتجميل')

@section('content')
<section style="background:#ffffff;min-height:100vh;">
    <div style="max-width:1400px;margin:0 auto;padding:7rem 1rem 3rem;display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
        <div style="position:sticky;top:6rem;align-self:start;height:calc(100vh - 8rem);overflow-y:auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <h2 style="font-size:1.1rem;font-weight:900;color:#0f172a;margin:0;">معاينة المقال</h2>
                <span id="statusBadge" style="display:none;font-size:.65rem;font-weight:700;color:#16a34a;background:#dcfce7;padding:.25rem .75rem;border-radius:9999px;">تم الإدراج ✓</span>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;overflow:hidden;">
                <div id="preview" style="background:#ffffff;">
                    <article style="max-width:800px;margin:0 auto;padding:4rem 1rem;">
                        <div style="text-align:center;padding:3rem 1rem;color:#94a3b8;">
                            <i class="ph ph-article" style="font-size:3rem;display:block;margin-bottom:.75rem;opacity:.4;"></i>
                            <p style="font-size:.85rem;">املأ البيانات وارفع ملف المقال<br>المعاينة تظهر هنا</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <div>
            <h2 style="font-size:1.1rem;font-weight:900;color:#0f172a;margin-bottom:1.25rem;">إنشاء مقال جديد</h2>

            <form id="articleForm" style="display:flex;flex-direction:column;gap:1rem;">
                @csrf
                <div>
                    <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:.35rem;">عنوان المقال</label>
                    <input type="text" id="titleInput" style="width:100%;padding:.65rem .85rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;outline:none;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" oninput="updatePreview()" placeholder="أدخل عنوان المقال">
                </div>

                <div>
                    <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:.35rem;">القسم</label>
                    <select id="categoryInput" style="width:100%;padding:.65rem .85rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;outline:none;" onchange="updatePreview()">
                        <option value="articles">📦 مقالات عن المنتجات</option>
                        <option value="tips">💡 نصائح للعناية الشاملة</option>
                        <option value="news">📰 أخبار التجميل</option>
                        <option value="guides">📖 أدلة الاستخدام</option>
                    </select>
                </div>


                <div>
                    <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:.35rem;">ملخص المقال (اختياري)</label>
                    <textarea id="excerptInput" rows="2" style="width:100%;padding:.65rem .85rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;outline:none;resize:vertical;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" oninput="updatePreview()" placeholder="ملخص قصير"></textarea>
                </div>

                <div>
                    <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:.35rem;">رابط الصورة الرئيسية (اختياري)</label>
                    <input type="text" id="imageInput" style="width:100%;padding:.65rem .85rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;outline:none;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" oninput="updatePreview()" placeholder="https://...">
                </div>

                <div>
                    <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:.35rem;">لون التصميم</label>
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        <input type="color" id="colorInput" value="#D97706" style="width:50px;height:40px;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;" onchange="updateColorFromPicker()">
                        <input type="text" id="colorHexInput" value="#D97706" style="flex:1;padding:.65rem .85rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;outline:none;" oninput="updateColorFromHex()">
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:.35rem;">رفع ملف المقال (.txt)</label>
                    <div id="dropZone" style="border:2px dashed #e2e8f0;border-radius:.85rem;padding:2rem 1rem;text-align:center;cursor:pointer;transition:all .2s;" onclick="document.getElementById('fileInput').click()" ondragover="this.style.borderColor='#ec4899';this.style.background='#fdf2f8'" ondragleave="this.style.borderColor='#e2e8f0';this.style.background=''">
                        <input type="file" id="fileInput" accept=".txt" style="display:none;" onchange="handleFile(this)">
                        <i class="ph ph-cloud-arrow-up" style="font-size:2rem;color:#94a3b8;display:block;margin-bottom:.5rem;"></i>
                        <p id="dropText" style="font-size:.85rem;color:#64748b;margin:0;">اسحب ملف .txt أو اضغط للاختيار</p>
                        <p id="fileName" style="font-size:.75rem;color:#94a3b8;margin-top:.35rem;display:none;"></p>
                    </div>
                </div>

                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem;">
                        <label style="display:block;font-size:.75rem;font-weight:700;color:#475569;">محتوى المقال (HTML)</label>
                        <span style="font-size:.65rem;color:#94a3b8;"><span id="wordCount">0</span> كلمة</span>
                    </div>
                    <textarea id="contentInput" rows="12" style="width:100%;padding:.75rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.8rem;outline:none;resize:vertical;direction:ltr;text-align:left;font-family:monospace;line-height:1.7;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" oninput="updatePreview()" placeholder="محتوى المقال بصيغة HTML"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <button type="button" onclick="updatePreview()" style="padding:.7rem;border:none;border-radius:.75rem;background:#0f172a;color:#fff;font-weight:800;font-size:.8rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.5rem;">
                        <i class="ph ph-eye"></i> معاينة
                    </button>
                    <button type="button" id="insertBtn" onclick="insertArticle()" style="padding:.7rem;border:none;border-radius:.75rem;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;font-weight:800;font-size:.8rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:.5rem;">
                        <i class="ph ph-plus-circle"></i> إدراج كمقال جديد
                    </button>
                </div>
                <div id="insertMsg" style="display:none;padding:.65rem;border-radius:.75rem;font-size:.8rem;font-weight:700;text-align:center;"></div>
            </form>
        </div>
    </div>
</section>

<style>
#preview {
    --primary-color: #D97706;
    --primary-light: #FDE68A;
    --primary-gradient-start: #F59E0B;
    --primary-gradient-end: #D97706;
    --text-primary: #B45309;
    --text-secondary: #92400E;
    --bg-light: #FFFBEB;
}

#preview .blog-content h2 { font-size:1.5rem; font-weight:900; color:#0f172a; margin-top:2rem; margin-bottom:1rem; }
#preview .blog-content h3 { font-size:1.2rem; font-weight:800; color:#1e293b; margin-top:1.5rem; margin-bottom:.75rem; }
#preview .blog-content p { margin-bottom:1rem; line-height:1.9; text-align:justify; color:#475569; }
#preview .blog-content ul, #preview .blog-content ol { margin-bottom:1rem; padding-right:1.5rem; }
#preview .blog-content li { margin-bottom:.5rem; line-height:1.8; color:#475569; }
#preview .blog-content strong { color:#0f172a; }
#preview .blog-content a { color:#be185d; text-decoration:underline; }
#preview .blog-content blockquote { border-right:3px solid #ec4899; padding:.75rem 1.25rem; margin:1.5rem 0; background:#fdf2f8; border-radius:0 .75rem .75rem 0; font-size:.95rem; color:#475569; }
#preview .blog-content img { max-width:100%; border-radius:.75rem; margin:1rem 0; }

#preview .blog-section { background:#fff; border-radius:16px; padding:0; margin-bottom:0; }
#preview .blog-content .blog-section h2 { color:var(--primary-color) !important; font-size:1.4rem; font-weight:700; margin-bottom:25px; padding-bottom:15px; border-bottom:3px solid var(--primary-light); display:flex; align-items:center; gap:12px; }
#preview .blog-content .blog-section h2 i { width:42px; height:42px; background:linear-gradient(135deg,var(--primary-gradient-start),var(--primary-gradient-end)); color:#fff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
#preview .blog-content .blog-section h3 { color:var(--text-primary) !important; font-size:1.15rem; font-weight:600; margin:25px 0 15px; display:flex; align-items:center; gap:8px; }
#preview .blog-section p { color:#4B5563; line-height:1.9; margin-bottom:15px; text-align:justify; }
#preview .blog-section ul { list-style:none; padding:0; margin:15px 0; }
#preview .blog-section ul li { padding:12px 18px; margin-bottom:10px; background:var(--bg-light); border-radius:10px; border-right:4px solid var(--primary-gradient-start); color:#4B5563; line-height:1.7; }
#preview .blog-section ul li strong { color:var(--text-secondary); }
#preview .blog-section ol { margin:15px 0; padding-right:1.5rem; }
#preview .blog-section ol li { padding:8px 0; margin-bottom:10px; color:#4B5563; line-height:1.7; }
#preview .blog-warning-box { background:linear-gradient(135deg,#FEE2E2,#FECACA); border:2px solid #EF4444; border-radius:12px; padding:20px; margin:20px 0; }
#preview .blog-warning-box h4 { color:#DC2626; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
#preview .blog-warning-box p { color:#7F1D1D; }
#preview .blog-info-box { background:linear-gradient(135deg,#DBEAFE,#BFDBFE); border:2px solid #3B82F6; border-radius:12px; padding:20px; margin:20px 0; }
#preview .blog-info-box h4 { color:#1E40AF; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
#preview .blog-info-box p { color:#1E3A5F; }
#preview .blog-highlight { background:#FEF3C7; padding:2px 8px; border-radius:4px; font-weight:600; color:#92400E; }
#preview .blog-section table { width:100%; border-collapse:collapse; margin:20px 0; font-size:.9rem; overflow-x:auto; display:block; }
#preview .blog-section table th, #preview .blog-section table td { padding:10px 12px; border:1px solid var(--primary-light); }
#preview .blog-section table th { background:linear-gradient(135deg,var(--primary-gradient-start),var(--primary-gradient-end)); color:#fff; font-weight:700; }
#preview .blog-section table tr:nth-child(even) { background:var(--bg-light); }

@media (max-width:1024px) {
    [style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
    [style*="position:sticky"] { position:static !important; height:auto !important; }
}
</style>

<script>
const CAT_COLORS = { articles: '#ec4899', tips: '#0891b2', news: '#d4af37', guides: '#16a34a' };
const CAT_LABELS = { articles: 'مقالات عن المنتجات', tips: 'نصائح للعناية الشاملة', news: 'أخبار التجميل', guides: 'أدلة الاستخدام' };

function updateColorFromPicker() {
    const color = document.getElementById('colorInput').value;
    document.getElementById('colorHexInput').value = color;
    updatePreview();
}

function updateColorFromHex() {
    const hex = document.getElementById('colorHexInput').value;
    if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
        document.getElementById('colorInput').value = hex;
        updatePreview();
    }
}

function generateColorPalette(hexColor) {
    const primary = hexColor;
    const light = adjustBrightness(hexColor, 40);
    const dark = adjustBrightness(hexColor, -20);
    const gradientStart = adjustBrightness(hexColor, 10);
    const gradientEnd = hexColor;
    const bgLight = adjustBrightness(hexColor, 50);
    const textSecondary = adjustBrightness(hexColor, -30);
    
    return { primary, light, dark, gradientStart, gradientEnd, bgLight, textSecondary };
}

function adjustBrightness(hex, percent) {
    const num = parseInt(hex.replace('#', ''), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) + amt;
    const G = (num >> 8 & 0x00FF) + amt;
    const B = (num & 0x0000FF) + amt;
    return '#' + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255)).toString(16).slice(1);
}

function handleFile(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('fileName').textContent = '📄 ' + file.name;
    document.getElementById('fileName').style.display = 'block';
    document.getElementById('dropText').textContent = 'تم اختيار الملف';
    document.getElementById('dropText').style.color = '#16a34a';
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('contentInput').value = e.target.result;
        updatePreview();
    };
    reader.readAsText(file, 'UTF-8');
}

function updatePreview() {
    const title = document.getElementById('titleInput').value || 'عنوان المقال';
    const category = document.getElementById('categoryInput').value;
    const imageUrl = document.getElementById('imageInput').value;
    const excerpt = document.getElementById('excerptInput').value;
    let content = document.getElementById('contentInput').value;
    const catColor = CAT_COLORS[category] || '#64748b';
    const catLabel = CAT_LABELS[category] || category;
    const today = new Date().toISOString().split('T')[0];

    document.getElementById('wordCount').textContent = content.replace(/<[^>]*>/g, '').trim() ? content.replace(/<[^>]*>/g, '').trim().split(/\s+/).length : 0;

    let html = '<article style="max-width:800px;margin:0 auto;padding:6rem 1rem 4rem;">';
    html += '<a href="#" style="color:#be185d;font-size:.8rem;font-weight:700;text-decoration:none;margin-bottom:1.5rem;display:inline-block;">&larr; العودة للمدونة</a>';
    html += '<div style="margin-bottom:2rem;">';
    html += '<span style="display:inline-block;font-size:.7rem;font-weight:700;color:' + catColor + ';background:' + catColor + '10;padding:.3rem .85rem;border-radius:9999px;margin-bottom:1rem;">' + catLabel + '</span>';
    html += '<h1 style="font-size:clamp(1.5rem,4vw,2.5rem);font-weight:900;color:#0f172a;line-height:1.3;margin-bottom:.75rem;">' + escHtml(title) + '</h1>';
    html += '<div style="display:flex;align-items:center;gap:.75rem;color:#94a3b8;font-size:.8rem;">';
    html += '<span><i class="ph ph-calendar ml-1"></i> ' + today + '</span>';
    if (excerpt) {
        html += '<span style="color:#cbd5e1;">|</span>';
        html += '<span>' + escHtml(excerpt.length > 60 ? excerpt.substring(0, 60) + '...' : excerpt) + '</span>';
    }
    html += '</div></div>';
    if (imageUrl) {
        html += '<img src="' + escAttr(imageUrl) + '" alt="' + escHtml(title) + '" style="width:100%;max-height:450px;object-fit:cover;border-radius:1.25rem;margin-bottom:2rem;" onerror="this.style.display=\'none\'">';
    }
    const designColor = document.getElementById('colorInput').value;
    const palette = generateColorPalette(designColor);

    html += '<div style="color:#334155;font-size:1.05rem;line-height:2;text-align:justify;" class="blog-content">';

    let cleaned = stripLayoutWrapper(content);
    cleaned = textToHtml(cleaned);
    html += wrapContentInSections(cleaned, catColor);

    html += '</div></article>';

    const previewEl = document.getElementById('preview');
    previewEl.innerHTML = html;
    previewEl.style.setProperty('--primary-color', palette.primary);
    previewEl.style.setProperty('--primary-light', palette.light);
    previewEl.style.setProperty('--primary-gradient-start', palette.gradientStart);
    previewEl.style.setProperty('--primary-gradient-end', palette.gradientEnd);
    previewEl.style.setProperty('--text-primary', palette.dark);
    previewEl.style.setProperty('--text-secondary', palette.textSecondary);
    previewEl.style.setProperty('--bg-light', palette.bgLight);
}

function wrapContentInSections(content, catColor) {
    if (!content.trim()) return '<p style="color:#94a3b8;text-align:center;padding:2rem 0;">انتظر رفع ملف المقال...</p>';
    if (content.includes('<div class="blog-section"')) return content;
    const sections = content.split(/(?=<h2)/i);
    return sections.map(s => {
        const trimmed = s.trim();
        if (!trimmed) return '';
        if (/^<h2/i.test(trimmed)) {
            const h2Match = trimmed.match(/^<h2>(.*?)<\/h2>/i);
            const h2Text = h2Match ? h2Match[1] : '';
            const rest = h2Match ? trimmed.substring(h2Match[0].length) : trimmed;
            const iconMatch = h2Text.match(/<i[^>]*><\/i>/);
            const icon = iconMatch ? iconMatch[0] : '<i class="fas fa-star"></i>';
            const cleanTitle = h2Text.replace(/<i[^>]*><\/i>\s*/g, '').replace(/<i[^>]*\/i>/g, '').trim();
            return '<div class="blog-section">\n    <h2>' + icon + ' ' + cleanTitle + '</h2>\n' + rest + '\n</div>';
        }
        return '<div class="blog-section">\n' + trimmed + '\n</div>';
    }).join('\n');
}

function stripLayoutWrapper(html) {
    let temp = document.createElement('div');
    temp.innerHTML = html;
    let bc = temp.querySelector('.blog-content');
    if (bc) return bc.innerHTML.trim();
    let article = temp.querySelector('article');
    if (article) return article.innerHTML.trim();
    let outer = temp.querySelector('[style*="max-width"]');
    if (outer) return outer.innerHTML.trim();
    return html;
}

function textToHtml(text) {
    if (/<[a-z][\s\S]*>/i.test(text)) return text;
    let paragraphs = text.split(/\n\s*\n/).filter(function(p) { return p.trim(); });
    return paragraphs.map(function(p) {
        p = p.trim();
        let lines = p.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l; });
        if (lines.length === 1 && lines[0].length < 80 && !/[.!؟!]$/.test(lines[0])) {
            return '<h2>' + escHtml(lines[0]) + '</h2>';
        }
        return lines.map(function(l) { return '<p>' + escHtml(l) + '</p>'; }).join('\n');
    }).join('\n');
}

function prepareContent(raw) {
    let c = stripLayoutWrapper(raw);
    c = textToHtml(c);
    c = wrapContentInSections(c);
    return c;
}

function insertArticle() {
    const btn = document.getElementById('insertBtn');
    const msg = document.getElementById('insertMsg');
    const title = document.getElementById('titleInput').value.trim();
    const category = document.getElementById('categoryInput').value;
    const rawContent = document.getElementById('contentInput').value.trim();
    const excerpt = document.getElementById('excerptInput').value.trim();
    const image = document.getElementById('imageInput').value.trim();

    if (!title) { showMsg('error', 'الرجاء إدخال عنوان المقال'); return; }
    if (!rawContent) { showMsg('error', 'الرجاء رفع ملف المقال أو كتابة المحتوى'); return; }

    const content = prepareContent(rawContent);

    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> جاري الإدراج...';

    fetch('{{ route("blog.insert-tool") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            title_ar: title,
            category: category,
            excerpt_ar: excerpt,
            content_ar: content,
            image: image,
            design_color: document.getElementById('colorInput').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMsg('success', '✅ تم إدراج المقال بنجاح!');
            document.getElementById('statusBadge').style.display = 'inline-block';
            setTimeout(() => document.getElementById('statusBadge').style.display = 'none', 5000);
        } else {
            showMsg('error', data.message || 'حدث خطأ');
        }
    })
    .catch(err => {
        showMsg('error', 'خطأ في الاتصال: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-plus-circle"></i> إدراج كمقال جديد';
    });
}

function showMsg(type, text) {
    const msg = document.getElementById('insertMsg');
    msg.style.display = 'block';
    msg.style.background = type === 'success' ? '#dcfce7' : '#fef2f2';
    msg.style.color = type === 'success' ? '#16a34a' : '#dc2626';
    msg.textContent = text;
    if (type === 'success') {
        setTimeout(() => { msg.style.display = 'none'; }, 6000);
    }
}

function escHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function escAttr(text) {
    return text.replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
</script>
@endsection
