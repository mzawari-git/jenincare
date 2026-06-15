@extends($layoutPath ?? 'frontend.layouts.organic-spa.app')

@section('title', 'الجولة الافتراضية في المتجر')
@section('meta_description', 'تجول داخل متجرنا بجولة 360 درجة واكتشف المنتجات')

@push('styles')
<style>
.store-scene-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px;
}
.store-scene-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
}
.scene-thumb {
    position: relative;
    height: 220px;
    overflow: hidden;
    background-size: cover;
    background-position: center;
}
.scene-thumb video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.scene-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 2;
}
.store-scene-card:hover .scene-overlay {
    opacity: 1;
}
.scene-video-indicator {
    position: absolute;
    bottom: 8px;
    left: 8px;
    z-index: 2;
    background: rgba(0,0,0,0.6);
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.bg-pink {
    background-color: #e91e63 !important;
    color: white;
}
.text-pink {
    color: #e91e63;
}
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">جولة افتراضية في المتجر</h1>
        <p class="lead text-muted">تجول بين أقسام المتجر وتعرف على المنتجات عن قرب</p>
    </div>

    <div class="row g-4">
        @forelse($scenes as $scene)
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('virtual-store.scene', $scene->slug) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 overflow-hidden store-scene-card">
                    <div class="position-relative">
                        <div class="scene-thumb"
                             style="background-image: url('{{ $scene->thumbnail ?: $scene->image_path }}');"
                             data-video="{{ $scene->video_path ? Storage::url($scene->video_path) : '' }}"
                             onmouseenter="this.querySelector('video')?.play()"
                             onmouseleave="this.querySelector('video')?.pause()">
                            @if($scene->video_path)
                            <video src="{{ Storage::url($scene->video_path) }}" muted loop playsinline preload="metadata"></video>
                            <span class="scene-video-indicator"><i class="fas fa-play"></i> شاهد الجولة</span>
                            @endif
                            <div class="scene-overlay">
                                <span class="badge bg-white text-dark px-3 py-2">
                                    <i class="fas fa-street-view ms-1"></i>
                                    استعرض الآن
                                </span>
                            </div>
                        </div>
                        @if($scene->section)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-pink">
                            {{ $scene->section }}
                        </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-1">{{ $scene->name_ar }}</h5>
                        @if($scene->name_en)
                        <small class="text-muted">{{ $scene->name_en }}</small>
                        @endif
                        @if($scene->aisle)
                        <p class="card-text mt-2 mb-0">
                            <i class="fas fa-map-pin ms-1 text-pink"></i>
                            {{ $scene->aisle }}
                        </p>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-store-slash fa-3x text-muted mb-3"></i>
            <p class="text-muted">لا توجد مشاهد متاحة حالياً</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
