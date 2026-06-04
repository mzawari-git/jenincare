<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

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
