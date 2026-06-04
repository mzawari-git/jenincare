<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::withTrashed()->latest()->paginate(20);
        $trashedCount = BlogPost::onlyTrashed()->count();
        return view('admin.blog.index', compact('posts', 'trashedCount'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    private function getUploadedImage(Request $request): ?UploadedFile
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file && $file->isValid()) return $file;
        }

        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            return new UploadedFile(
                $_FILES['image']['tmp_name'],
                $_FILES['image']['name'],
                $_FILES['image']['type'],
                $_FILES['image']['error'],
                true
            );
        }

        return null;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'category' => 'required|in:articles,tips,news,guides',
            'excerpt_ar' => 'nullable|string|max:500',
            'content_ar' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data['slug'] = $this->uniqueSlug($data['title_ar']);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');

        $image = $this->getUploadedImage($request);
        if ($image) {
            $data['image'] = $this->saveImage($image);
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'تم إنشاء المقال بنجاح.');
    }

    public function edit($id)
    {
        $post = BlogPost::withTrashed()->findOrFail($id);
        return view('admin.blog.edit', ['post' => $post]);
    }

    public function update(Request $request, $id)
    {
        $blog = BlogPost::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'category' => 'required|in:articles,tips,news,guides',
            'excerpt_ar' => 'nullable|string|max:500',
            'content_ar' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        if ($data['title_ar'] !== $blog->title_ar) {
            $data['slug'] = $this->uniqueSlug($data['title_ar'], $blog->id);
        }

        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');

        $image = $this->getUploadedImage($request);
        if ($image) {
            $this->deleteImage($blog->image);
            $data['image'] = $this->saveImage($image);
        }

        $blog->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'تم تحديث المقال بنجاح.');
    }

    public function destroy($id)
    {
        $blog = BlogPost::withTrashed()->findOrFail($id);
        if ($blog->trashed()) {
            $this->deleteImage($blog->image);
        }
        $blog->delete();
        return back()->with('success', 'تم حذف المقال.');
    }

    public function restore($id)
    {
        BlogPost::withTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'تم استعادة المقال.');
    }

    public function toggle($id)
    {
        $blog = BlogPost::withTrashed()->findOrFail($id);
        $blog->update(['is_published' => !$blog->is_published]);
        return back()->with('success', 'تم تحديث حالة النشر.');
    }

    public function emptyTrash()
    {
        BlogPost::onlyTrashed()->get()->each(function ($post) {
            $this->deleteImage($post->image);
        });
        BlogPost::onlyTrashed()->forceDelete();
        return back()->with('success', 'تم إفراغ سلة المحذوفات.');
    }

    public function uploadInlineImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $this->saveImage($request->file('image'), 'uploads/blog/inline');
        $url = asset($path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'html' => '<img src="' . $url . '" alt="" class="rounded-xl max-w-full mx-auto block" loading="lazy">',
        ]);
    }

    private function saveImage($file, string $subdir = 'uploads/blog'): string
    {
        $dir = public_path($subdir);
        if (!File::exists($dir)) File::makeDirectory($dir, 0755, true);
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return $subdir . '/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

    private function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug(preg_replace('/[^\x{0600}-\x{06FF}\w\s]/u', '', $title), '-');
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
}
