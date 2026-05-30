@extends('frontend.layouts.editorial.app')

@section('title', $categoryTitle . ' | مدونة جنين للتجميل')

@php
function readingTime($content) {
    $words = str_word_count(strip_tags($content));
    return max(1, ceil($words / 200));
}
@endphp

@section('content')
<section style="background:var(--surface);min-height:100vh;">
    <div style="position:relative;overflow:hidden;padding:7rem 1rem 3.5rem;text-align:center;background:var(--surface-alt);border-bottom:1px solid var(--glass-border);">
        <div style="max-width:600px;margin:0 auto;position:relative;z-index:1;">
            <a href="{{ route('blog.index') }}" style="color:var(--brand-500);font-size:.78rem;font-weight:700;text-decoration:none;margin-bottom:1.25rem;display:inline-flex;align-items:center;gap:.35rem;transition:gap .2s;"><span style="font-size:.85rem;">&rarr;</span> العودة للمدونة</a>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;color:var(--ink);margin-bottom:.5rem;">{{ $categoryTitle }}</h1>
            <p style="color:var(--ink-muted);font-size:.85rem;">{{ $posts->total() }} مقال</p>
        </div>
    </div>

    <div style="max-width:1100px;margin:0 auto;padding:3rem 1rem;">
        @if($posts->isEmpty())
            <div style="text-align:center;padding:3rem;">
                <p style="color:var(--ink-muted);">لا توجد مقالات في هذا القسم بعد.</p>
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
                @foreach($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card" style="text-decoration:none;display:flex;flex-direction:column;border-radius:1.25rem;overflow:hidden;border:1px solid var(--glass-border);background:var(--surface-alt);transition:all .3s cubic-bezier(.4,0,.2,1);box-shadow:0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);">
                    @if($post->image_url)
                    <div style="position:relative;overflow:hidden;">
                        <img src="{{ $post->image_url }}" alt="{{ $post->title_ar }}" style="width:100%;height:200px;object-fit:cover;transition:transform .5s ease;display:block;" loading="lazy">
                    </div>
                    @else
                    <div style="height:120px;background:linear-gradient(135deg,{{ $post->category_color }}10,{{ $post->category_color }}05);"></div>
                    @endif
                    <div style="padding:1.25rem;flex:1;display:flex;flex-direction:column;">
                        <span style="display:inline-block;font-size:.6rem;font-weight:700;color:{{ $post->category_color }};background:{{ $post->category_color }}12;padding:.2rem .65rem;border-radius:9999px;margin-bottom:.75rem;align-self:flex-start;">{{ $post->category_label }}</span>
                        <h3 style="font-size:1.05rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;line-height:1.5;">{{ $post->title_ar }}</h3>
                        <p style="color:var(--ink-muted);font-size:.78rem;line-height:1.7;flex:1;">{{ Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 100) }}</p>
                        <div style="margin-top:.75rem;display:flex;align-items:center;gap:.75rem;font-size:.65rem;color:var(--ink-dim);">
                            <span>{{ $post->created_at->format('Y-m-d') }}</span>
                            <span>&middot;</span>
                            <span>{{ readingTime($post->content_ar) }} دقيقة قراءة</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div style="margin-top:2.5rem;">{{ $posts->links() }}</div>
        @endif
    </div>
</section>

<style>
.blog-card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--brand-500) 30%, var(--glass-border)) !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
}
.blog-card:hover img {
    transform: scale(1.06);
}
</style>
@endsection
