<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="شركة جنين للتجميل - منصة العناية بالشعر والبشرة الأولى في فلسطين. منتجات أصلية، توصيل سريع.">
    <title>شركة جنين للتجميل - منصة العناية بالشعر والبشرة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        :root {
            --pink-50: #FDF2F8;
            --pink-100: #FCE7F3;
            --pink-500: #EC4899;
            --pink-600: #DB2777;
            --pink-700: #BE185D;
            --gray-500: #737373;
            --gray-700: #404040;
            --gray-800: #262626;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Tajawal','Cairo',sans-serif;
            background:linear-gradient(135deg,#FFF5F9 0%,#FDF2F8 50%,#FCE7F3 100%);
            color:var(--gray-800); min-height:100vh; display:flex;
            align-items:center; justify-content:center; text-align:center;
            direction:rtl; overflow:hidden;
        }
        .bg-pattern {
            position:fixed; inset:0; opacity:.03;
            background:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23DB2777\' fill-opacity=\'0.03\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
        }
        .splash-container { position:relative; z-index:1; padding:40px 20px; }
        .logo-icon {
            width:64px; height:64px; margin:0 auto 16px;
            background:linear-gradient(135deg,var(--pink-600),var(--pink-500));
            border-radius:16px; display:flex; align-items:center; justify-content:center;
            font-weight:900; font-size:1.3rem; color:#fff;
            box-shadow:0 8px 32px rgba(219,39,119,0.25);
        }
        .logo-text { font-size:2rem; font-weight:800; color:var(--gray-800); margin-bottom:4px; }
        .logo-text span { color:var(--pink-600); }
        h1 { font-size:1.4rem; font-weight:500; color:var(--gray-700); margin-bottom:8px; }
        p { font-size:1.05rem; color:var(--gray-500); margin-bottom:32px; max-width:480px; margin-left:auto; margin-right:auto; line-height:1.7; }
        .btn-splash {
            display:inline-flex; align-items:center; gap:8px;
            padding:14px 36px; border-radius:50px;
            text-decoration:none; font-weight:700; font-size:.95rem;
            transition:all .3s; cursor:pointer; border:none;
        }
        .btn-primary { background:linear-gradient(135deg,var(--pink-600),var(--pink-500)); color:#fff; box-shadow:0 8px 25px rgba(219,39,119,0.3); }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 12px 35px rgba(219,39,119,0.4); color:#fff; }
        .btn-outline { background:transparent; color:var(--pink-600); border:2px solid var(--pink-600); }
        .btn-outline:hover { background:var(--pink-600); color:#fff; }
        .features { display:flex; gap:32px; justify-content:center; margin-top:40px; flex-wrap:wrap; }
        .feature { text-align:center; }
        .feature i { font-size:1.5rem; color:var(--pink-600); margin-bottom:8px; width:48px; height:48px; background:var(--pink-50); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-left:auto; margin-right:auto; }
        .feature span { display:block; font-size:.85rem; color:var(--gray-500); }
        @media (max-width:640px) {
            .logo-text { font-size:1.5rem; }
            h1 { font-size:1.1rem; }
            p { font-size:.95rem; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="splash-container">
        <div class="logo-icon">JC</div>
        <div class="logo-text">Jeni<span>Care</span></div>
        <h1>منصة العناية بالشعر والبشرة</h1>
        <p>أفضل منتجات العناية بالشعر والبشرة من أشهر الماركات العالمية. جودة أصلية، توصيل سريع لجميع أنحاء فلسطين.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('home') }}" class="btn-splash btn-primary">
                <i class="fas fa-store"></i> دخول المتجر
            </a>
            <a href="{{ route('login') }}" class="btn-splash btn-outline">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </a>
        </div>
        <div class="features">
            <div class="feature"><i class="fas fa-truck"></i><span>توصيل سريع</span></div>
            <div class="feature"><i class="fas fa-shield-alt"></i><span>منتجات أصلية</span></div>
            <div class="feature"><i class="fas fa-percent"></i><span>أفضل الأسعار</span></div>
        </div>
    </div>
</body>
</html>
