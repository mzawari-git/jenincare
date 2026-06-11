<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $featuredPosts = BlogPost::published()->featured()->orderByDesc('created_at')->limit(3)->get();
        $latestPosts = BlogPost::published()->ordered()->limit(12)->get();
        $articlePosts = BlogPost::published()->category('articles')->ordered()->limit(4)->get();
        $tipPosts = BlogPost::published()->category('tips')->ordered()->limit(4)->get();
        $guidePosts = BlogPost::published()->category('guides')->ordered()->limit(4)->get();

        return view('frontend.blog.index', compact(
            'featuredPosts', 'latestPosts', 'articlePosts', 'tipPosts', 'guidePosts'
        ));
    }

    public function category($category)
    {
        $posts = BlogPost::published()->category($category)->ordered()->paginate(12);
        $labels = [
            'articles' => 'مقالات عن المنتجات',
            'tips' => 'نصائح للعناية الشاملة',
            'news' => 'أخبار التجميل',
            'guides' => 'أدلة الاستخدام',
        ];
        $categoryTitle = $labels[$category] ?? $category;

        return view('frontend.blog.category', compact('posts', 'category', 'categoryTitle'));
    }

    public function designTool()
    {
        return view('frontend.blog.design-tool');
    }

    public function insertFromTool(Request $request)
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'category' => 'required|in:articles,tips,news,guides',
            'content_ar' => 'required|string',
            'excerpt_ar' => 'nullable|string|max:500',
            'image' => 'nullable|string|max:500',
            'design_color' => 'nullable|string|max:7',
        ]);

        $slug = $this->uniqueSlug($data['title_ar']);
        $content = $this->sanitizeContent($data['content_ar']);

        $post = BlogPost::create([
            'title_ar' => $data['title_ar'],
            'slug' => $slug,
            'category' => $data['category'],
            'content_ar' => $content,
            'excerpt_ar' => $data['excerpt_ar'] ?? null,
            'image' => $data['image'] ?? null,
            'design_color' => $data['design_color'] ?? null,
            'is_published' => true,
            'sort_order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إدراج المقال بنجاح',
            'post' => $post,
            'url' => route('blog.show', $post->slug),
        ]);
    }

    private function sanitizeContent(string $html): string
    {
        // Remove <style> and <script> blocks
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);

        // Strip full-page layout wrapper: <div style="max-width:780px...">...</div>
        if (preg_match('/<div[^>]*class="blog-content"[^>]*>(.*?)<\/div>/is', $html, $m)) {
            return trim($m[1]);
        }

        // Remove outer wrapper divs with max-width (layout wrappers)
        $html = preg_replace('/<div[^>]*style="[^"]*max-width:\s*7[58]\dpx[^"]*"[^>]*>/is', '', $html);
        $html = preg_replace('/<\/div>\s*$/s', '', trim($html));

        // If plain text (no HTML tags), convert to HTML with auto-detected headings
        if (!preg_match('/<[a-z][\s\S]*>/i', $html)) {
            $paragraphs = preg_split('/\n\s*\n/', $html);
            $result = [];
            foreach ($paragraphs as $p) {
                $p = trim($p);
                if (empty($p)) continue;
                $lines = array_filter(array_map('trim', explode("\n", $p)));
                if (count($lines) === 1 && mb_strlen($lines[0]) < 80 && !preg_match('/[.!؟!]$/u', $lines[0])) {
                    $result[] = '<h2>' . e($lines[0]) . '</h2>';
                } else {
                    $result[] = implode("\n", array_map(fn($l) => '<p>' . e($l) . '</p>', $lines));
                }
            }
            return implode("\n", $result);
        }

        // Ensure content is wrapped in blog-section divs
        if (!str_contains($html, 'blog-section')) {
            $sections = preg_split('/(?=<h2)/i', $html);
            $result = '';
            foreach ($sections as $section) {
                $section = trim($section);
                if (empty($section)) continue;
                if (preg_match('/^<h2/i', $section)) {
                    if (preg_match('/^<h2>(.*?)<\/h2>/i', $section, $m)) {
                        $h2Text = $m[1];
                        $rest = substr($section, strlen($m[0]));
                        $icon = '<i class="fas fa-star"></i>';
                        if (preg_match('/<i[^>]*><\/i>/', $h2Text, $iconMatch)) {
                            $icon = $iconMatch[0];
                        }
                        $cleanTitle = trim(strip_tags(str_replace($icon, '', $h2Text)));
                        $result .= '<div class="blog-section">' . "\n    <h2>" . $icon . ' ' . $cleanTitle . "</h2>\n" . $rest . "\n</div>\n";
                    } else {
                        $result .= '<div class="blog-section">' . "\n" . $section . "\n</div>\n";
                    }
                } else {
                    $result .= '<div class="blog-section">' . "\n" . $section . "\n</div>\n";
                }
            }
            return trim($result);
        }

        return trim($html);
    }

    private function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = \Illuminate\Support\Str::slug(preg_replace('/[^\x{0600}-\x{06FF}\w\s]/u', '', $title), '-');
        $slug = $slug ?: 'post';
        $original = $slug;
        $counter = 1;

        $query = BlogPost::where('slug', $slug);
        if ($excludeId) $query->where('id', '!=', $excludeId);

        while ($query->exists()) {
            $slug = $original . '-' . $counter;
            $query = BlogPost::where('slug', $slug);
            if ($excludeId) $query->where('id', '!=', $excludeId);
            $counter++;
        }

        return $slug;
    }

    public static function adjustBrightnessStatic(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) return $hex;
        $num = hexdec($hex);
        $amt = (int) round(2.55 * $percent);
        $R = max(0, min(255, (($num >> 16) & 0xFF) + $amt));
        $G = max(0, min(255, (($num >> 8) & 0xFF) + $amt));
        $B = max(0, min(255, ($num & 0xFF) + $amt));
        return sprintf('#%02x%02x%02x', $R, $G, $B);
    }

    public function show($slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();
        $relatedPosts = BlogPost::published()
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->ordered()
            ->limit(3)
            ->get();

        return view('frontend.blog.show', compact('post', 'relatedPosts'));
    }
}
