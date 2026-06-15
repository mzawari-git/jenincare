@extends($layoutPath ?? 'frontend.layouts.organic-spa.app')

@section('title', 'الجولة الافتراضية ثلاثية الأبعاد')
@section('meta_description', 'تجول داخل متجرنا ثلاثي الأبعاد واكتشف المنتجات')

@push('styles')
<style>
    body {
        background: #0f0f1a !important;
        overflow: hidden;
    }
    .main-content-v3 {
        min-height: 0 !important;
        padding: 0 !important;
    }
    .header-spacer, .floating-social-v3, #mainHeaderV3, footer {
        display: none !important;
    }
    #store-3d-root {
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
    }
    .store-3d-loading {
        position: fixed;
        inset: 0;
        background: #0f0f1a;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        color: #fff;
        font-family: 'Tajawal', sans-serif;
    }
    .store-3d-loading .spinner {
        width: 48px;
        height: 48px;
        border: 3px solid rgba(139, 92, 246, 0.2);
        border-top-color: #8B5CF6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 16px;
    }
    .store-3d-loading p {
        font-size: 16px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.7);
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div id="store-3d-loading" class="store-3d-loading">
    <div class="spinner"></div>
    <p>جاري تحميل المتجر ثلاثي الأبعاد...</p>
</div>

<div id="store-3d-root" style="display:none;" data-base-url="{{ asset('') }}"></div>
@endsection

@push('scripts')
@php
    $manifestPath = public_path('dist/.vite/manifest.json');
    $jsFile = 'dist/assets/main.js';
    $cssFile = 'dist/assets/main.css';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (isset($manifest['main.jsx'])) {
            $jsFile = 'dist/' . $manifest['main.jsx']['file'];
            $cssFile = 'dist/' . ($manifest['main.jsx']['css'][0] ?? 'assets/main.css');
        }
    }
@endphp
<link rel="stylesheet" href="{{ asset($cssFile) }}">
<script src="{{ asset($jsFile) }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingEl = document.getElementById('store-3d-loading');
    const rootEl = document.getElementById('store-3d-root');
    if (loadingEl) loadingEl.style.display = 'none';
    if (rootEl) rootEl.style.display = 'block';
});
</script>
@endpush
