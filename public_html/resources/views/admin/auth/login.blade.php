<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - لوحة التحكم - {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(236,72,153,0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 50%, rgba(236,72,153,0.06) 0%, transparent 50%);
            pointer-events: none;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }
        .login-card {
            background: #1E293B;
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #EC4899, #DB2777);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: #fff;
            box-shadow: 0 8px 24px rgba(219,39,119,0.3);
        }
        .brand h1 {
            color: #fff;
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }
        .brand p {
            color: rgba(255,255,255,0.4);
            font-size: 0.85rem;
            font-weight: 500;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-error {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.2);
            color: #FCA5A5;
        }
        .alert-success {
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.2);
            color: #86EFAC;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }
        .form-group label .required {
            color: #F87171;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.25);
            font-size: 0.95rem;
            pointer-events: none;
        }
        .input-wrapper input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.75rem;
            padding: 0.85rem 2.75rem 0.85rem 1rem;
            color: #fff;
            font-size: 0.9rem;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.2s;
            outline: none;
        }
        .input-wrapper input::placeholder { color: rgba(255,255,255,0.2); }
        .input-wrapper input:focus {
            border-color: #EC4899;
            box-shadow: 0 0 0 3px rgba(236,72,153,0.15);
            background: rgba(255,255,255,0.08);
        }
        .input-wrapper input.has-error {
            border-color: #EF4444;
        }
        .input-wrapper .toggle-password {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.25);
            cursor: pointer;
            font-size: 0.95rem;
            padding: 0;
            transition: color 0.2s;
        }
        .input-wrapper .toggle-password:hover { color: rgba(255,255,255,0.5); }
        .field-error {
            color: #FCA5A5;
            font-size: 0.75rem;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.8rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .checkbox-label:hover { color: rgba(255,255,255,0.7); }
        .checkbox-label input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: #EC4899;
            cursor: pointer;
        }
        .forgot-link {
            color: rgba(236,72,153,0.7);
            font-size: 0.8rem;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: #EC4899; text-decoration: underline; }
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #DB2777, #EC4899);
            border: none;
            border-radius: 0.75rem;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 16px rgba(219,39,119,0.3);
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(219,39,119,0.4);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-submit .spinner {
            display: none;
            width: 1.15rem;
            height: 1.15rem;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .footer-links a {
            color: rgba(255,255,255,0.35);
            font-size: 0.8rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: rgba(255,255,255,0.6); }
        .footer-links .separator {
            color: rgba(255,255,255,0.15);
            margin: 0 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-spa"></i>
                </div>
                <h1>لوحة التحكم</h1>
                <p>{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</p>
            </div>

            @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" id="adminLoginForm">
                @csrf

                <div class="form-group">
                    <label>البريد الإلكتروني <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@example.com"
                            class="@error('email') has-error @enderror">
                    </div>
                    @error('email')<div class="field-error"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>كلمة المرور <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="adminPassword" required
                            placeholder="********"
                            class="@error('password') has-error @enderror">
                        <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    @error('password')<div class="field-error"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        تذكرني
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot-link">نسيت كلمة المرور؟</a>
                </div>

                <button type="submit" class="btn-submit" id="adminLoginBtn">
                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</span>
                    <span class="spinner"></span>
                </button>
            </form>

            <div class="footer-links">
                <a href="{{ route('home') }}"><i class="fas fa-external-link-alt"></i> العودة للموقع</a>
                <span class="separator">|</span>
                <a href="{{ route('login') }}">تسجيل دخول العملاء</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('adminPassword');
            const icon = document.getElementById('passwordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        document.getElementById('adminLoginForm').addEventListener('submit', function() {
            const btn = document.getElementById('adminLoginBtn');
            btn.disabled = true;
            btn.classList.add('loading');
        });
    </script>
</body>
</html>
