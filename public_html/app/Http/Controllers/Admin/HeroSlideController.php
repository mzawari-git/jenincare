<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::with("product")->orderBy("sort_order")->orderBy("created_at", "desc")->get();
        return view("admin.hero-slides.index", compact("slides"));
    }

    public function create()
    {
        $products = Product::where("status", "active")->orderBy("name_ar")->get();
        return view("admin.hero-slides.create", compact("products"));
    }

    public function store(Request $request)
    {
        $data = $this->validateSlide($request);
        $data = $this->handleImageUpload($request, $data);
        HeroSlide::create($data);
        return redirect()->route("admin.hero-slides.index")->with("success", "تم إضافة الشريحة بنجاح");
    }

    public function edit(HeroSlide $heroSlide)
    {
        $products = Product::where("status", "active")->orderBy("name_ar")->get();
        return view("admin.hero-slides.edit", compact("heroSlide", "products"));
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $data = $this->validateSlide($request);
        $data = $this->handleImageUpload($request, $data);
        $heroSlide->update($data);
        return redirect()->route("admin.hero-slides.index")->with("success", "تم تحديث الشريحة بنجاح");
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $heroSlide->delete();
        return redirect()->route("admin.hero-slides.index")->with("success", "تم حذف الشريحة بنجاح");
    }

    public function toggle(HeroSlide $heroSlide)
    {
        $heroSlide->update(["is_active" => !$heroSlide->is_active]);
        return back()->with("success", $heroSlide->is_active ? "تم تفعيل الشريحة" : "تم إخفاء الشريحة");
    }

    private function handleImageUpload(Request $request, array $data): array
    {
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $path = $request->file('image_file')->store('hero-slides', 'public');
            $data['image'] = $path;
        }
        return $data;
    }

    private function validateSlide(Request $request): array
    {
        return $request->validate([
            "product_id" => "nullable|exists:products,id",
            "title_ar" => "required|string|max:255",
            "title_en" => "nullable|string|max:255",
            "subtitle_ar" => "nullable|string|max:255",
            "subtitle_en" => "nullable|string|max:255",
            "description_ar" => "nullable|string|max:500",
            "description_en" => "nullable|string|max:500",
            "button_text_ar" => "nullable|string|max:100",
            "button_text_en" => "nullable|string|max:100",
            "button_url" => "nullable|string|max:255",
            "second_button_text_ar" => "nullable|string|max:100",
            "second_button_url" => "nullable|string|max:255",
            "image" => "nullable|string|max:500",
            "mobile_image" => "nullable|string|max:500",
            "video_url" => "nullable|string|max:500",
            "html_content" => "nullable|string|max:5000",
            "image_position" => "nullable|string|in:right,left,background,none",
            "text_color" => "nullable|string|max:20",
            "text_align" => "nullable|string|in:right,center,left",
            "overlay_opacity" => "nullable|numeric|min:0|max:1",
            "animation_type" => "nullable|string|in:fade,slide,zoom",
            "parallax" => "boolean",
            "full_width_image" => "boolean",
            "content_width" => "nullable|string|in:container,container-fluid",
            "badge_text_ar" => "nullable|string|max:100",
            "badge_text_en" => "nullable|string|max:100",
            "gradient_from" => "nullable|string|max:50",
            "gradient_to" => "nullable|string|max:50",
            "sort_order" => "nullable|integer|min:0",
            "is_active" => "boolean",
        ]);
    }
}
