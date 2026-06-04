@extends('frontend.layouts.editorial.app')

@section('title', 'مدونة جنين للتجميل | مقالات ونصائح للعناية الشاملة')
@section('meta_description', 'مدونة جنين للتجميل - مقالات عن المنتجات، نصائح للعناية الشاملة، أحدث أخبار التجميل، وأدلة استخدام متكاملة.')

@php
function readingTime($content) {
    $words = str_word_count(strip_tags($content));
    return max(1, ceil($words / 200));
}
@endphp

@section('content')
<section style="background:var(--surface);min-height:100vh;">

    {{-- HERO --}}
    <div style="position:relative;overflow:hidden;padding:8rem 1rem 5rem;text-align:center;background:linear-gradient(135deg,var(--surface) 0%,color-mix(in srgb,var(--brand-500) 6%,var(--surface)) 50%,var(--surface) 100%);">
        <div style="position:absolute;top:-40%;right:-10%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,color-mix(in srgb,var(--brand-500) 12%,transparent) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-30%;left:-10%;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,color-mix(in srgb,var(--accent-500,var(--brand-500)) 8%,transparent) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="max-width:720px;margin:0 auto;position:relative;z-index:1;">
            <div style="display:inline-flex;align-items:center;gap:.5rem;padding:.35rem 1.25rem;border-radius:9999px;border:1px solid color-mix(in srgb,var(--brand-500) 25%,transparent);background:color-mix(in srgb,var(--brand-500) 6%,var(--surface));margin-bottom:1.75rem;">
                <span style="font-size:.7rem;font-weight:800;color:var(--brand-500);letter-spacing:.1em;">مدونة جنين للتجميل</span>
            </div>
            <h1 style="font-size:clamp(2rem,5vw,3.25rem);font-weight:900;color:var(--ink);line-height:1.15;margin-bottom:1rem;">جمالكِ يبدأ من <span style="color:var(--brand-500);">المعلومة الصحيحة</span></h1>
            <p style="color:var(--ink-muted);font-size:1.05rem;line-height:1.8;max-width:580px;margin:0 auto 2rem;">اكتشفي أحدث المقالات والنصائح في عالم التجميل والعناية. كل ما تحتاجين معرفته عن المنتجات، التركيبات، وطرق العناية المثلى.</p>

            {{-- Category filter pills --}}
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem;">
                <a href="{{ route('blog.index') }}" style="padding:.4rem 1.1rem;border-radius:9999px;font-size:.75rem;font-weight:700;text-decoration:none;background:var(--brand-500);color:#fff;border:none;transition:all .2s;">الكل</a>
                <a href="{{ route('blog.category', 'articles') }}" style="padding:.4rem 1.1rem;border-radius:9999px;font-size:.75rem;font-weight:700;text-decoration:none;border:1px solid color-mix(in srgb,var(--brand-500) 25%,transparent);color:var(--ink);background:transparent;transition:all .2s;">مقالات عن المنتجات</a>
                <a href="{{ route('blog.category', 'guides') }}" style="padding:.4rem 1.1rem;border-radius:9999px;font-size:.75rem;font-weight:700;text-decoration:none;border:1px solid color-mix(in srgb,var(--brand-500) 25%,transparent);color:var(--ink);background:transparent;transition:all .2s;">أدلة الاستخدام</a>
                <a href="{{ route('blog.category', 'tips') }}" style="padding:.4rem 1.1rem;border-radius:9999px;font-size:.75rem;font-weight:700;text-decoration:none;border:1px solid color-mix(in srgb,var(--brand-500) 25%,transparent);color:var(--ink);background:transparent;transition:all .2s;">نصائح للعناية</a>
            </div>
        </div>
    </div>

    {{-- FEATURED POSTS --}}
    @if($featuredPosts->isNotEmpty())
    <div style="padding:3rem 1rem;background:var(--surface);">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;">
                <span style="font-size:.7rem;font-weight:800;color:var(--brand-500);letter-spacing:.1em;background:color-mix(in srgb,var(--brand-500) 8%,var(--surface));padding:.3rem .85rem;border-radius:9999px;">مقالات مميزة</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
                @foreach($featuredPosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card blog-card-featured" style="text-decoration:none;display:flex;flex-direction:column;border-radius:1.25rem;overflow:hidden;border:1px solid var(--glass-border);background:var(--surface-alt);transition:all .3s cubic-bezier(.4,0,.2,1);position:relative;">
                    <div style="position:relative;overflow:hidden;">
                        @if($post->image_url)
                        <img src="{{ $post->image_url }}" alt="{{ $post->title_ar }}" style="width:100%;height:220px;object-fit:cover;transition:transform .5s ease;display:block;" loading="lazy">
                        <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,rgba(0,0,0,.6));"></div>
                        @else
                        <div style="height:160px;background:linear-gradient(135deg,{{ $post->category_color }}15,{{ $post->category_color }}08);display:flex;align-items:center;justify-content:center;">
                            <i class="ph ph-article" style="font-size:3rem;color:{{ $post->category_color }}30;"></i>
                        </div>
                        @endif
                        <span style="position:absolute;top:1rem;right:1rem;z-index:2;font-size:.6rem;font-weight:800;color:#fff;background:var(--brand-500);padding:.25rem .75rem;border-radius:9999px;backdrop-filter:blur(4px);">مميز</span>
                    </div>
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
        </div>
    </div>
    @endif

    {{-- ARTICLES SECTION --}}
    @if($articlePosts->isNotEmpty())
    <div style="padding:3rem 1rem;background:var(--surface-alt);">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:color-mix(in srgb,var(--brand-500) 12%,transparent);">
                        <i class="ph ph-flask" style="color:var(--brand-500);"></i>
                    </span>
                    <div>
                        <h2 style="font-size:1.3rem;font-weight:900;color:var(--ink);margin:0;">مقالات عن المنتجات</h2>
                        <p style="color:var(--ink-muted);font-size:.75rem;margin:.25rem 0 0;">تعرفي على كل ما يخص منتجات التجميل والعناية</p>
                    </div>
                </div>
                <a href="{{ route('blog.category', 'articles') }}" style="color:var(--brand-500);font-size:.78rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:gap .2s;">عرض الكل <span style="font-size:.85rem;">&larr;</span></a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;">
                @foreach($articlePosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card" style="text-decoration:none;display:flex;flex-direction:column;background:var(--surface);border:1px solid var(--glass-border);border-radius:1rem;padding:1.25rem;transition:all .3s cubic-bezier(.4,0,.2,1);">
                    <span style="display:inline-block;font-size:.6rem;font-weight:700;color:{{ $post->category_color }};background:{{ $post->category_color }}12;padding:.2rem .65rem;border-radius:9999px;margin-bottom:.75rem;align-self:flex-start;">{{ $post->category_label }}</span>
                    <h3 style="font-size:.95rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;line-height:1.5;">{{ $post->title_ar }}</h3>
                    <p style="color:var(--ink-muted);font-size:.73rem;line-height:1.7;flex:1;">{{ Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 80) }}</p>
                    <div style="margin-top:.75rem;display:flex;align-items:center;gap:.75rem;font-size:.6rem;color:var(--ink-dim);">
                        <span>{{ $post->created_at->format('Y-m-d') }}</span>
                        <span>&middot;</span>
                        <span>{{ readingTime($post->content_ar) }} دقائق</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- TIPS SECTION --}}
    @if($tipPosts->isNotEmpty())
    <div style="padding:3rem 1rem;background:var(--surface);">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:color-mix(in srgb,var(--accent-500,#0891b2) 12%,transparent);">
                        <i class="ph ph-sparkle" style="color:var(--accent-500,#0891b2);"></i>
                    </span>
                    <div>
                        <h2 style="font-size:1.3rem;font-weight:900;color:var(--ink);margin:0;">نصائح للعناية الشاملة</h2>
                        <p style="color:var(--ink-muted);font-size:.75rem;margin:.25rem 0 0;">دليلكِ المتكامل لروتين عناية صحيح وفعّال</p>
                    </div>
                </div>
                <a href="{{ route('blog.category', 'tips') }}" style="color:var(--accent-500,#0891b2);font-size:.78rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:gap .2s;">عرض الكل <span style="font-size:.85rem;">&larr;</span></a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;">
                @foreach($tipPosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card" style="text-decoration:none;display:flex;flex-direction:column;background:var(--surface-alt);border:1px solid var(--glass-border);border-radius:1rem;padding:1.25rem;transition:all .3s cubic-bezier(.4,0,.2,1);">
                    <span style="display:inline-block;font-size:.6rem;font-weight:700;color:{{ $post->category_color }};background:{{ $post->category_color }}12;padding:.2rem .65rem;border-radius:9999px;margin-bottom:.75rem;align-self:flex-start;">{{ $post->category_label }}</span>
                    <h3 style="font-size:.95rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;line-height:1.5;">{{ $post->title_ar }}</h3>
                    <p style="color:var(--ink-muted);font-size:.73rem;line-height:1.7;flex:1;">{{ Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 80) }}</p>
                    <div style="margin-top:.75rem;display:flex;align-items:center;gap:.75rem;font-size:.6rem;color:var(--ink-dim);">
                        <span>{{ $post->created_at->format('Y-m-d') }}</span>
                        <span>&middot;</span>
                        <span>{{ readingTime($post->content_ar) }} دقائق</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- GUIDES SECTION --}}
    @if($guidePosts->isNotEmpty())
    <div style="padding:3rem 1rem;background:var(--surface-alt);">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:color-mix(in srgb,var(--green-500,#16a34a) 12%,transparent);">
                        <i class="ph ph-book-open" style="color:var(--green-500,#16a34a);"></i>
                    </span>
                    <div>
                        <h2 style="font-size:1.3rem;font-weight:900;color:var(--ink);margin:0;">أدلة الاستخدام</h2>
                        <p style="color:var(--ink-muted);font-size:.75rem;margin:.25rem 0 0;">أدلة شاملة ومقارنات بين الأجهزة والمنتجات</p>
                    </div>
                </div>
                <a href="{{ route('blog.category', 'guides') }}" style="color:var(--green-500,#16a34a);font-size:.78rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:gap .2s;">عرض الكل <span style="font-size:.85rem;">&larr;</span></a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;">
                @foreach($guidePosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card" style="text-decoration:none;display:flex;flex-direction:column;background:var(--surface);border:1px solid var(--glass-border);border-radius:1rem;padding:1.25rem;transition:all .3s cubic-bezier(.4,0,.2,1);">
                    <span style="display:inline-block;font-size:.6rem;font-weight:700;color:{{ $post->category_color }};background:{{ $post->category_color }}12;padding:.2rem .65rem;border-radius:9999px;margin-bottom:.75rem;align-self:flex-start;">{{ $post->category_label }}</span>
                    <h3 style="font-size:.95rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;line-height:1.5;">{{ $post->title_ar }}</h3>
                    <p style="color:var(--ink-muted);font-size:.73rem;line-height:1.7;flex:1;">{{ Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 80) }}</p>
                    <div style="margin-top:.75rem;display:flex;align-items:center;gap:.75rem;font-size:.6rem;color:var(--ink-dim);">
                        <span>{{ $post->created_at->format('Y-m-d') }}</span>
                        <span>&middot;</span>
                        <span>{{ readingTime($post->content_ar) }} دقائق</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- LATEST POSTS --}}
    @if($latestPosts->isNotEmpty())
    <div style="padding:3rem 1rem 4rem;background:var(--surface);">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="text-align:center;margin-bottom:2.5rem;">
                <h2 style="font-size:1.6rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;">أحدث المقالات</h2>
                <p style="color:var(--ink-muted);font-size:.85rem;">كل ما هو جديد في عالم التجميل والعناية</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
                @foreach($latestPosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="blog-card blog-card-image" style="text-decoration:none;display:flex;flex-direction:column;border-radius:1.25rem;overflow:hidden;border:1px solid var(--glass-border);background:var(--surface-alt);transition:all .3s cubic-bezier(.4,0,.2,1);">
                    @if($post->image_url)
                    <div style="position:relative;overflow:hidden;">
                        <img src="{{ $post->image_url }}" alt="{{ $post->title_ar }}" style="width:100%;height:200px;object-fit:cover;transition:transform .5s ease;display:block;" loading="lazy">
                    </div>
                    @else
                    <div style="height:140px;background:linear-gradient(135deg,{{ $post->category_color }}10,{{ $post->category_color }}05);"></div>
                    @endif
                    <div style="padding:1.25rem;flex:1;display:flex;flex-direction:column;">
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.65rem;">
                            <span style="font-size:.55rem;font-weight:700;color:{{ $post->category_color }};background:{{ $post->category_color }}12;padding:.15rem .55rem;border-radius:9999px;letter-spacing:.02em;">{{ $post->category_label }}</span>
                            <span style="font-size:.6rem;color:var(--ink-dim);">{{ $post->created_at->format('Y-m-d') }}</span>
                        </div>
                        <h3 style="font-size:.95rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;line-height:1.5;">{{ $post->title_ar }}</h3>
                        <p style="color:var(--ink-muted);font-size:.75rem;line-height:1.7;flex:1;">{{ Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 90) }}</p>
                        <div style="margin-top:.75rem;display:flex;align-items:center;gap:.5rem;font-size:.6rem;color:var(--ink-dim);">
                            <span>{{ readingTime($post->content_ar) }} دقيقة قراءة</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- NEWSLETTER CTA --}}
    <div style="padding:3.5rem 1rem;background:var(--surface-alt);">
        <div style="max-width:600px;margin:0 auto;text-align:center;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border-radius:1rem;background:color-mix(in srgb,var(--brand-500) 12%,transparent);margin-bottom:1.25rem;">
                <i class="ph ph-envelope-open" style="font-size:1.3rem;color:var(--brand-500);"></i>
            </span>
            <h2 style="font-size:1.3rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;">انضمي إلى نشرتنا البريدية</h2>
            <p style="color:var(--ink-muted);font-size:.85rem;margin-bottom:1.5rem;">احصلي على أحدث المقالات والنصائح الحصرية مباشرة في بريدك الإلكتروني</p>
            <form action="#" method="POST" style="display:flex;gap:.5rem;max-width:420px;margin:0 auto;">
                @csrf
                <input type="email" name="email" placeholder="بريدك الإلكتروني" required style="flex:1;padding:.65rem 1rem;border-radius:.75rem;border:1px solid var(--glass-border);background:var(--surface);color:var(--ink);font-size:.8rem;outline:none;transition:border-color .2s;">
                <button type="submit" style="padding:.65rem 1.5rem;border-radius:.75rem;border:none;background:var(--brand-500);color:#fff;font-size:.8rem;font-weight:800;cursor:pointer;transition:all .2s;">اشتراك</button>
            </form>
        </div>
    </div>

    {{-- EMPTY STATE --}}
    @if($latestPosts->isEmpty())
    <div style="padding:5rem 1rem;text-align:center;">
        <i class="ph ph-article" style="font-size:4rem;color:var(--ink-dim);opacity:.3;margin-bottom:1rem;"></i>
        <h2 style="font-size:1.5rem;font-weight:900;color:var(--ink);margin-bottom:.5rem;">المقالات قريباً</h2>
        <p style="color:var(--ink-muted);">نعمل على تجهيز محتوى قيّم ومفيد لكِ. تابعينا!</p>
    </div>
    @endif

</section>

<style>
.blog-card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
}
.blog-card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--brand-500) 30%, var(--glass-border)) !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
}
.blog-card-featured:hover img {
    transform: scale(1.06);
}
.blog-card-image:hover img {
    transform: scale(1.06);
}
.blog-card-featured:hover {
    border-color: color-mix(in srgb, var(--brand-500) 40%, var(--glass-border)) !important;
}
.category-pill:hover {
    background: var(--brand-500) !important;
    color: #fff !important;
    border-color: var(--brand-500) !important;
}
.newsletter-input:focus {
    border-color: var(--brand-500) !important;
}
</style>
@endsection
