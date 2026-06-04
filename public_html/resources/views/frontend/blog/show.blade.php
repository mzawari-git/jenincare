@extends('frontend.layouts.editorial.app')

@section('title', $post->meta_title ?: $post->title_ar . ' | مدونة جنين للتجميل')
@section('meta_description', $post->meta_description ?: Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 160))

@section('content')
<section style="background:#ffffff;min-height:100vh;">
    <article style="max-width:800px;margin:0 auto;padding:6rem 1rem 4rem;">

        <a href="{{ route('blog.index') }}" style="color:#be185d;font-size:.8rem;font-weight:700;text-decoration:none;margin-bottom:1.5rem;display:inline-block;">&larr; العودة للمدونة</a>

        <div style="margin-bottom:2rem;">
            <span style="display:inline-block;font-size:.7rem;font-weight:700;color:{{ $post->category_color }};background:{{ $post->category_color }}10;padding:.3rem .85rem;border-radius:9999px;margin-bottom:1rem;">{{ $post->category_label }}</span>
            <h1 style="font-size:clamp(1.5rem,4vw,2.5rem);font-weight:900;color:#0f172a;line-height:1.3;margin-bottom:.75rem;">{{ $post->title_ar }}</h1>
            <div style="display:flex;align-items:center;gap:.75rem;color:#94a3b8;font-size:.8rem;">
                <span><i class="ph ph-calendar ml-1"></i> {{ $post->created_at->format('Y-m-d') }}</span>
                @if($post->excerpt_ar)
                <span style="color:#cbd5e1;">|</span>
                <span>{{ Str::limit(strip_tags($post->excerpt_ar), 60) }}</span>
                @endif
            </div>
        </div>

        @if($post->image_url)
        <img src="{{ $post->image_url }}" alt="{{ $post->title_ar }}" style="width:100%;max-height:450px;object-fit:cover;border-radius:1.25rem;margin-bottom:2rem;">
        @endif

        <div style="color:#334155;font-size:1.05rem;line-height:2;text-align:justify;" class="blog-content">
            {!! $post->content_ar !!}
        </div>

        {{-- RELATED POSTS --}}
        @if($relatedPosts->isNotEmpty())
        <div style="margin-top:4rem;padding-top:2.5rem;border-top:1px solid #e2e8f0;">
            <h3 style="font-size:1.25rem;font-weight:900;color:#0f172a;margin-bottom:1.5rem;">مقالات ذات صلة</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
                @foreach($relatedPosts as $related)
                <a href="{{ route('blog.show', $related->slug) }}" style="text-decoration:none;display:block;background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem;transition:all .2s;">
                    <span style="display:inline-block;font-size:.6rem;font-weight:700;color:{{ $related->category_color }};background:{{ $related->category_color }}10;padding:.2rem .55rem;border-radius:9999px;margin-bottom:.5rem;">{{ $related->category_label }}</span>
                    <h4 style="font-size:.9rem;font-weight:900;color:#0f172a;margin-bottom:.4rem;line-height:1.5;">{{ $related->title_ar }}</h4>
                    <p style="color:#94a3b8;font-size:.7rem;line-height:1.5;">{{ Str::limit(strip_tags($related->excerpt_ar ?? $related->content_ar), 60) }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
</section>

<style>
.blog-content h2 { font-size:1.5rem; font-weight:900; color:#0f172a; margin-top:2rem; margin-bottom:1rem; }
.blog-content h3 { font-size:1.2rem; font-weight:800; color:#1e293b; margin-top:1.5rem; margin-bottom:.75rem; }
.blog-content p { margin-bottom:1rem; line-height:1.9; text-align:justify; color:#475569; }
.blog-content ul, .blog-content ol { margin-bottom:1rem; padding-right:1.5rem; }
.blog-content li { margin-bottom:.5rem; line-height:1.8; color:#475569; }
.blog-content strong { color:#0f172a; }
.blog-content a { color:#be185d; text-decoration:underline; }
.blog-content blockquote { border-right:3px solid #ec4899; padding:.75rem 1.25rem; margin:1.5rem 0; background:#fdf2f8; border-radius:0 .75rem .75rem 0; font-size:.95rem; color:#475569; }
.blog-content img { max-width:100%; border-radius:.75rem; margin:1rem 0; }

.blog-section { background:#fff; border-radius:16px; padding:0; margin-bottom:0; }

.blog-section h2 { color:#D97706; font-size:1.4rem; font-weight:700; margin-bottom:25px; padding-bottom:15px; border-bottom:3px solid #FDE68A; display:flex; align-items:center; gap:12px; }
.blog-section h2 i { width:42px; height:42px; background:linear-gradient(135deg,#F59E0B,#D97706); color:#fff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.blog-section h3 { color:#B45309; font-size:1.15rem; font-weight:600; margin:25px 0 15px; display:flex; align-items:center; gap:8px; }
.blog-section p { color:#4B5563; line-height:1.9; margin-bottom:15px; text-align:justify; }
.blog-section ul { list-style:none; padding:0; margin:15px 0; }
.blog-section ul li { padding:12px 18px; margin-bottom:10px; background:#FFFBEB; border-radius:10px; border-right:4px solid #F59E0B; color:#4B5563; line-height:1.7; }
.blog-section ul li strong { color:#92400E; }
.blog-section ol { margin:15px 0; padding-right:1.5rem; }
.blog-section ol li { padding:8px 0; margin-bottom:10px; color:#4B5563; line-height:1.7; }

.blog-warning-box { background:linear-gradient(135deg,#FEE2E2,#FECACA); border:2px solid #EF4444; border-radius:12px; padding:20px; margin:20px 0; }
.blog-warning-box h4 { color:#DC2626; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
.blog-warning-box h4 i { font-size:1.2rem; }
.blog-warning-box p { color:#7F1D1D; }
.blog-info-box { background:linear-gradient(135deg,#DBEAFE,#BFDBFE); border:2px solid #3B82F6; border-radius:12px; padding:20px; margin:20px 0; }
.blog-info-box h4 { color:#1E40AF; font-weight:700; margin-bottom:10px; display:flex; align-items:center; gap:8px; }
.blog-info-box h4 i { font-size:1.2rem; }
.blog-info-box p { color:#1E3A5F; }
.blog-highlight { background:#FEF3C7; padding:2px 8px; border-radius:4px; font-weight:600; color:#92400E; }

.blog-section table { width:100%; border-collapse:collapse; margin:20px 0; font-size:.9rem; overflow-x:auto; display:block; }
.blog-section table th,
.blog-section table td { padding:10px 12px; border:1px solid #FDE68A; }
.blog-section table th { background:linear-gradient(135deg,#F59E0B,#D97706); color:#fff; font-weight:700; }
.blog-section table tr:nth-child(even) { background:#FFFBEB; }

@media (max-width:768px) {
    .blog-section h2 { font-size:1.15rem; }
    .blog-section { padding:0; }
    .blog-section h2 i { width:34px; height:34px; font-size:.9rem; }
}
</style>
@endsection
