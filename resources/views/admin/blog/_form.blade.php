<div class="row g-4">

    {{-- Main Content Column --}}
    <div class="col-lg-8">

        {{-- Tab Navigation --}}
        <ul class="nav nav-tabs nav-fill mb-3" id="blogTabs" role="tablist" style="border-bottom: 2px solid var(--gray-200); gap: 0;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-content-tab" data-bs-toggle="tab" data-bs-target="#tab-content" type="button" role="tab" style="font-weight: 700; font-size: .8rem; color: var(--gray-500); border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: .6rem 1rem;">
                    <i class="fas fa-pen-fancy ml-1"></i> المحتوى الرئيسي
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-seo-tab" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button" role="tab" style="font-weight: 700; font-size: .8rem; color: var(--gray-500); border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: .6rem 1rem;">
                    <i class="fas fa-search ml-1"></i> تحسين SEO
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-images-tab" data-bs-toggle="tab" data-bs-target="#tab-images" type="button" role="tab" style="font-weight: 700; font-size: .8rem; color: var(--gray-500); border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: .6rem 1rem;">
                    <i class="fas fa-images ml-1"></i> الصور
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-preview-tab" data-bs-toggle="tab" data-bs-target="#tab-preview" type="button" role="tab" style="font-weight: 700; font-size: .8rem; color: var(--gray-500); border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; padding: .6rem 1rem;">
                    <i class="fas fa-eye ml-1"></i> معاينة
                </button>
            </li>
        </ul>

        <div class="tab-content" id="blogTabsContent">

            {{-- TAB 1: Main Content --}}
            <div class="tab-pane fade show active" id="tab-content" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>بيانات المقال الأساسية</span>
                        <span class="badge" style="background: var(--gray-100); color: var(--gray-500); font-size: .65rem; font-weight: 500;">جميع الحقول المطلوبة</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">عنوان المقال <span style="color: #dc2626;">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--gray-50); border: 1px solid var(--gray-200); color: var(--pink-500);">
                                    <i class="fas fa-heading"></i>
                                </span>
                                <input type="text" name="title_ar" value="{{ $post->title_ar ?? old('title_ar') }}" required
                                       class="form-control" placeholder="أدخل عنوان المقال هنا..."
                                       style="border: 1px solid var(--gray-200); padding: .6rem .75rem; font-size: .9rem;">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">القسم <span style="color: #dc2626;">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: var(--gray-50); border: 1px solid var(--gray-200); color: var(--pink-500);">
                                        <i class="fas fa-folder"></i>
                                    </span>
                                    <select name="category" required class="form-select" style="border: 1px solid var(--gray-200); padding: .6rem .75rem;">
                                        <option value="">اختر القسم</option>
                                        <option value="articles" {{ ($post->category ?? old('category')) === 'articles' ? 'selected' : '' }}>📦 مقالات عن المنتجات</option>
                                        <option value="tips" {{ ($post->category ?? old('category')) === 'tips' ? 'selected' : '' }}>💡 نصائح للعناية الشاملة</option>
                                        <option value="news" {{ ($post->category ?? old('category')) === 'news' ? 'selected' : '' }}>📰 أخبار التجميل</option>
                                        <option value="guides" {{ ($post->category ?? old('category')) === 'guides' ? 'selected' : '' }}>📖 أدلة الاستخدام</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">ترتيب العرض</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: var(--gray-50); border: 1px solid var(--gray-200); color: var(--pink-500);">
                                        <i class="fas fa-sort-numeric-down"></i>
                                    </span>
                                    <input type="number" name="sort_order" value="{{ $post->sort_order ?? old('sort_order', 0) }}"
                                           class="form-control" style="border: 1px solid var(--gray-200); padding: .6rem .75rem;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">ملخص المقال</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--gray-50); border: 1px solid var(--gray-200); color: var(--pink-500);">
                                    <i class="fas fa-paragraph"></i>
                                </span>
                                <textarea name="excerpt_ar" rows="2" maxlength="500"
                                          class="form-control" placeholder="ملخص قصير يظهر في بطاقة المقال..."
                                          style="border: 1px solid var(--gray-200); resize: vertical;">{{ $post->excerpt_ar ?? old('excerpt_ar') }}</textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span style="font-size: .7rem; color: var(--gray-400);">أقصى حد 500 حرف</span>
                                <span style="font-size: .7rem; color: var(--gray-400);" class="char-count" data-target="excerpt_ar">0/500</span>
                            </div>
                        </div>

                        {{-- Publishing Status --}}
                        <div class="d-flex align-items-center gap-4 pt-3 mt-3" style="border-top: 1px solid var(--gray-100);">
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_published" value="0">
                                <input class="form-check-input" type="checkbox" name="is_published" value="1" role="switch" id="switchPublished" {{ ($post->is_published ?? true) ? 'checked' : '' }} style="cursor: pointer; width: 2.5rem; height: 1.25rem;">
                                <label class="form-check-label fw-medium" for="switchPublished" style="color: var(--gray-600);">منشور</label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" role="switch" id="switchFeatured" {{ ($post->is_featured ?? false) ? 'checked' : '' }} style="cursor: pointer; width: 2.5rem; height: 1.25rem;">
                                <label class="form-check-label fw-medium" for="switchFeatured" style="color: var(--gray-600);"><i class="fas fa-star" style="color: #f59e0b;"></i> مميز</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content Editor --}}
                <div class="card mt-4">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap: .5rem;">
                        <span><i class="fas fa-code" style="color: var(--pink-500);"></i> محتوى المقال <span style="color: #dc2626;">*</span></span>
                        <div class="d-flex align-items-center gap-1" id="editorToolbar">
                            <button type="button" onclick="wrapText('blogContentArea', '<strong>', '</strong>')" class="btn btn-sm btn-outline-secondary" title="عريض" style="border: none;"><i class="fas fa-bold"></i></button>
                            <button type="button" onclick="wrapText('blogContentArea', '<em>', '</em>')" class="btn btn-sm btn-outline-secondary" title="مائل" style="border: none;"><i class="fas fa-italic"></i></button>
                            <button type="button" onclick="wrapText('blogContentArea', '<u>', '</u>')" class="btn btn-sm btn-outline-secondary" title="تسطير" style="border: none;"><i class="fas fa-underline"></i></button>
                            <span class="mx-1" style="width: 1px; height: 18px; background: var(--gray-200);"></span>
                            <button type="button" onclick="wrapText('blogContentArea', '<h2>', '</h2>')" class="btn btn-sm btn-outline-secondary" title="عنوان H2" style="border: none; font-weight: 700;">H2</button>
                            <button type="button" onclick="wrapText('blogContentArea', '<h3>', '</h3>')" class="btn btn-sm btn-outline-secondary" title="عنوان H3" style="border: none; font-weight: 700;">H3</button>
                            <span class="mx-1" style="width: 1px; height: 18px; background: var(--gray-200);"></span>
                            <button type="button" onclick="wrapText('blogContentArea', '<p>', '</p>')" class="btn btn-sm btn-outline-secondary" title="فقرة" style="border: none; font-weight: 700;">?</button>
                            <button type="button" onclick="insertList('blogContentArea', 'ul')" class="btn btn-sm btn-outline-secondary" title="قائمة غير مرقمة" style="border: none;"><i class="fas fa-list-ul"></i></button>
                            <button type="button" onclick="insertList('blogContentArea', 'ol')" class="btn btn-sm btn-outline-secondary" title="قائمة مرقمة" style="border: none;"><i class="fas fa-list-ol"></i></button>
                            <span class="mx-1" style="width: 1px; height: 18px; background: var(--gray-200);"></span>
                            <button type="button" onclick="wrapText('blogContentArea', '<blockquote>', '</blockquote>')" class="btn btn-sm btn-outline-secondary" title="اقتباس" style="border: none;"><i class="fas fa-quote-right"></i></button>
                            <button type="button" onclick="insertInfoBox('blogContentArea')" class="btn btn-sm btn-outline-secondary" title="معلومة" style="border: none;"><i class="fas fa-info-circle"></i></button>
                            <button type="button" onclick="insertWarningBox('blogContentArea')" class="btn btn-sm btn-outline-secondary" title="تنبيه" style="border: none;"><i class="fas fa-exclamation-triangle"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <textarea id="blogContentArea" name="content_ar" rows="20" required
                                  class="form-control font-monospace text-start"
                                  dir="ltr" placeholder="اكتب محتوى المقال هنا... (يدعم HTML)"
                                  style="border: 1px solid var(--gray-200); resize: vertical; line-height: 1.8; font-size: .85rem;">{{ $post->content_ar ?? old('content_ar') }}</textarea>
                        <div class="d-flex justify-content-between mt-2">
                            <span style="font-size: .7rem; color: var(--gray-400);">
                                <i class="fas fa-info-circle" style="color: var(--pink-500);"></i>
                                يمكن استخدام HTML: p, h2, h3, ul, ol, li, strong, a, img, blockquote, br, div, span
                            </span>
                            <span style="font-size: .7rem; color: var(--gray-400);">
                                <span id="contentWordCount">0</span> كلمة
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Inline Image Upload --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-image" style="color: var(--pink-500);"></i> إدراج صور داخل المقال
                        <span style="font-size: .7rem; color: var(--gray-400); font-weight: 400; margin-right: .5rem;">ارفع صورة وسيتم إدراجها تلقائياً في موضع المؤشر</span>
                    </div>
                    <div class="card-body">
                        <div id="inlineImageUpload"
                             class="text-center p-4 rounded-3"
                             style="border: 2px dashed var(--gray-200); cursor: pointer; transition: all .2s;"
                             onclick="document.getElementById('inlineImageInput').click()">
                            <input type="file" id="inlineImageInput" accept="image/*" class="d-none">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--gray-300);"></i>
                            <p class="mt-2 mb-1" style="font-size: .85rem; color: var(--gray-500);">اضغط لرفع صورة أو اسحب وأفلت</p>
                            <p style="font-size: .75rem; color: var(--gray-400);">PNG, JPG, WebP - أقصى حجم 5MB</p>
                            <div id="inlineImageProgress" class="d-none mt-3">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #EC4899, #DB2777); transition: width .3s;"></div>
                                </div>
                                <p class="mt-1" style="font-size: .75rem; color: var(--pink-500);">
                                    <i class="fas fa-spinner fa-spin"></i> جاري الرفع...
                                </p>
                            </div>
                            <div id="inlineImageResult" class="d-none mt-3 p-2 rounded-3" style="background: #DCFCE7; border: 1px solid #86EFAC;">
                                <p style="font-size: .8rem; color: #16a34a; margin: 0;"><i class="fas fa-check-circle"></i> تم رفع الصورة وإدراجها في المقال</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: SEO --}}
            <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-search" style="color: #f59e0b;"></i> إعدادات تحسين محركات البحث (SEO)
                        <span style="font-size: .7rem; color: var(--gray-400); font-weight: 400; margin-right: .5rem;">حسّن ظهور المقال في نتائج البحث</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">Meta Title</label>
                            <span style="font-size: .7rem; color: var(--gray-400); margin-right: .5rem;">(إذا تركت فارغاً، سيتم استخدام عنوان المقال)</span>
                            <input type="text" name="meta_title" value="{{ $post->meta_title ?? old('meta_title') }}" maxlength="255"
                                   class="form-control text-start" id="metaTitleInput"
                                   dir="ltr" placeholder="{{ Str::limit($post->title_ar ?? '', 60) }}"
                                   oninput="updateSEOPreview()"
                                   style="border: 1px solid var(--gray-200); padding: .6rem .75rem;">
                            <div class="d-flex justify-content-between mt-1">
                                <span style="font-size: .7rem; color: var(--gray-400);">أقصى حد 255 حرف</span>
                                <span style="font-size: .7rem; color: var(--gray-400);" class="char-count" data-target="meta_title">0/255</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">Meta Description</label>
                            <textarea name="meta_description" rows="3" maxlength="500"
                                      class="form-control text-start" id="metaDescInput"
                                      dir="ltr" placeholder="وصف مختصر يظهر في نتائج البحث..."
                                      oninput="updateSEOPreview()"
                                      style="border: 1px solid var(--gray-200); resize: none;">{{ $post->meta_description ?? old('meta_description') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <span style="font-size: .7rem; color: var(--gray-400);">أقصى حد 500 حرف - يُنصح بـ 150-160 حرف</span>
                                <span style="font-size: .7rem; color: var(--gray-400);" class="char-count" data-target="meta_description">0/500</span>
                            </div>
                        </div>

                        {{-- SEO Preview --}}
                        <div class="mt-4 p-3 rounded-3" style="background: var(--gray-50); border: 1px solid var(--gray-200);">
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">
                                <i class="fab fa-google" style="color: #3b82f6;"></i> معاينة نتائج البحث (Google Preview)
                            </label>
                            <div id="seoPreview" class="p-3 rounded-2" style="background: #fff; border: 1px solid var(--gray-200);">
                                <div id="seoUrl" style="color: #166534; font-size: .75rem;">{{ config('app.url') }}/blog/<span id="seoSlug">post-slug</span></div>
                                <div id="seoTitle" style="color: #1a56db; font-size: .85rem; font-weight: 500; cursor: pointer; margin-top: 2px;">{{ Str::limit($post->meta_title ?? $post->title_ar ?? 'عنوان المقال', 60) }}</div>
                                <div id="seoDesc" style="color: #4b5563; font-size: .75rem; margin-top: 2px;">{{ Str::limit(strip_tags($post->meta_description ?? $post->excerpt_ar ?? $post->content_ar ?? ''), 160) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: Images --}}
            <div class="tab-pane fade" id="tab-images" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-images" style="color: #8b5cf6;"></i> صور المقال
                        <span style="font-size: .7rem; color: var(--gray-400); font-weight: 400; margin-right: .5rem;">الصورة الرئيسية للمقال وألبوم الصور</span>
                    </div>
                    <div class="card-body">
                        {{-- Main Image --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">الصورة الرئيسية للمقال</label>
                            <div id="mainImageDropzone" class="text-center p-4 rounded-3" style="border: 2px dashed var(--gray-200);">
                                <div id="mainImagePreview" class="{{ empty($post->image_url) ? 'd-none' : '' }} mb-3">
                                    <img src="{{ $post->image_url ?? '' }}" id="mainImagePreviewImg"
                                         class="img-fluid rounded-3" style="max-height: 200px; object-fit: cover;">
                                    <div class="mt-2">
                                        <button type="button" onclick="removeMainImage()" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> إزالة الصورة
                                        </button>
                                    </div>
                                </div>
                                <div id="mainImagePlaceholder" class="{{ !empty($post->image_url) ? 'd-none' : '' }}">
                                    <i class="fas fa-camera" style="font-size: 2rem; color: var(--gray-300);"></i>
                                    <p class="mt-2 mb-1" style="font-size: .85rem; color: var(--gray-500);">اختر صورة رئيسية للمقال</p>
                                    <p style="font-size: .75rem; color: var(--gray-400);">JPEG, PNG, WebP - أقصى حجم 5MB</p>
                                    <label class="btn btn-pink btn-sm mt-2" style="cursor: pointer;">
                                        <i class="fas fa-upload"></i> اختيار صورة
                                        <input type="file" name="image" accept="image/*" class="d-none" id="mainImageInput" onchange="previewMainImage(this)">
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Gallery Images --}}
                        <div>
                            <label class="form-label fw-bold" style="font-size: .8rem; color: var(--gray-700);">ألبوم الصور الإضافية</label>
                            <div class="row g-2" id="galleryGrid">
                                <div class="col-4">
                                    <div class="text-center p-3 rounded-3" style="border: 2px dashed var(--gray-200); cursor: pointer; min-height: 100px; display: flex; flex-direction: column; align-items: center; justify-content: center;" onclick="document.getElementById('galleryInput').click()">
                                        <i class="fas fa-plus" style="font-size: 1.2rem; color: var(--gray-300);"></i>
                                        <p style="font-size: .7rem; color: var(--gray-400); margin-top: 4px;">إضافة صورة</p>
                                    </div>
                                </div>
                            </div>
                            <input type="file" id="galleryInput" accept="image/*" multiple class="d-none">
                            <p style="font-size: .7rem; color: var(--gray-400); margin-top: 8px;">يمكن إضافة صور متعددة لعرضها في معرض المقال</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: Preview --}}
            <div class="tab-pane fade" id="tab-preview" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-eye" style="color: #06b6d4;"></i> معاينة المقال
                        <span style="font-size: .7rem; color: var(--gray-400); font-weight: 400; margin-right: .5rem;">شكل المقال النهائي على الموقع</span>
                    </div>
                    <div class="card-body">
                        <div id="livePreview" class="p-4 rounded-3" style="background: #fff; border: 1px solid var(--gray-200); min-height: 200px;">
                            <div class="text-center py-5" style="color: var(--gray-400);">
                                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
                                <p class="mt-2">اضغط "تحديث المعاينة" لعرض المقال</p>
                            </div>
                        </div>
                        <button type="button" onclick="refreshPreview()" class="btn btn-pink mt-3 w-100">
                            <i class="fas fa-sync-alt"></i> تحديث المعاينة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Column --}}
    <div class="col-lg-4">
        <div style="position: sticky; top: 5.5rem;">
            {{-- Save Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" style="color: var(--gray-700); font-size: .85rem;">
                        <i class="fas fa-floppy-disk" style="color: var(--pink-500);"></i> إجراءات
                    </h6>

                    <button type="submit" class="btn btn-pink w-100 mb-2" style="padding: .7rem 1rem; font-size: .9rem;">
                        <i class="fas fa-save"></i> {{ isset($isEdit) && $isEdit ? 'حفظ التعديلات' : 'نشر المقال' }}
                    </button>

                    @if(isset($isEdit) && $isEdit)
                    <a href="{{ route('admin.blog.create') }}" class="btn btn-outline-secondary w-100 mb-2" style="padding: .6rem 1rem; font-size: .85rem;">
                        <i class="fas fa-plus"></i> مقال جديد
                    </a>
                    <a href="#" onclick="event.preventDefault(); if(confirm('متأكد من حذف هذا المقال؟')) document.getElementById('delete-form').submit();"
                       class="btn btn-outline-danger w-100" style="padding: .6rem 1rem; font-size: .85rem;">
                        <i class="fas fa-trash-alt"></i> حذف المقال
                    </a>
                    @endif

                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--gray-100);">
                        <div class="d-flex justify-content-between mb-2" style="font-size: .8rem;">
                            <span style="color: var(--gray-500);">الحالة</span>
                            <span class="fw-bold" style="color: var(--gray-700);">{{ ($post->is_published ?? true) ? 'منشور' : 'مسودة' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: .8rem;">
                            <span style="color: var(--gray-500);">تاريخ الإنشاء</span>
                            <span class="fw-bold" style="color: var(--gray-700);">{{ isset($post) && $post->created_at ? $post->created_at->format('Y-m-d') : '--' }}</span>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: .8rem;">
                            <span style="color: var(--gray-500);">آخر تحديث</span>
                            <span class="fw-bold" style="color: var(--gray-700);">{{ isset($post) && $post->updated_at ? $post->updated_at->format('Y-m-d') : '--' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Tips --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" style="color: var(--gray-700); font-size: .85rem;">
                        <i class="fas fa-lightbulb" style="color: #f59e0b;"></i> نصائح سريعة
                    </h6>
                    <ul class="list-unstyled mb-0" style="font-size: .8rem;">
                        <li class="mb-2 d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle mt-1" style="color: #16a34a; font-size: .7rem;"></i>
                            <span style="color: var(--gray-500);">استخدم عنواناً جذاباً وواضحاً</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle mt-1" style="color: #16a34a; font-size: .7rem;"></i>
                            <span style="color: var(--gray-500);">أضف ملخصاً قصيراً للمقال</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle mt-1" style="color: #16a34a; font-size: .7rem;"></i>
                            <span style="color: var(--gray-500);">استخدم صوراً عالية الجودة</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle mt-1" style="color: #16a34a; font-size: .7rem;"></i>
                            <span style="color: var(--gray-500);">اختر القسم المناسب للمقال</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-check-circle mt-1" style="color: #16a34a; font-size: .7rem;"></i>
                            <span style="color: var(--gray-500);">املأ حقول SEO لظهور أفضل</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" style="color: var(--gray-700); font-size: .85rem;">
                        <i class="fas fa-chart-bar" style="color: #3b82f6;"></i> إحصائيات
                    </h6>
                    <div style="font-size: .8rem;">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: var(--gray-500);">حروف العنوان</span>
                            <span class="fw-bold" style="color: var(--gray-700);" id="statTitleChars">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: var(--gray-500);">كلمات المحتوى</span>
                            <span class="fw-bold" style="color: var(--gray-700);" id="statContentWords">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: var(--gray-500);">سطور المحتوى</span>
                            <span class="fw-bold" style="color: var(--gray-700);" id="statContentLines">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="color: var(--gray-500);">حروف الملخص</span>
                            <span class="fw-bold" style="color: var(--gray-700);" id="statExcerptChars">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#blogTabs .nav-link.active {
    color: var(--pink-600) !important;
    border-bottom-color: var(--pink-500) !important;
    background: transparent !important;
}
#blogTabs .nav-link:hover {
    color: var(--pink-500) !important;
    border-bottom-color: transparent;
}
.form-control:focus, .form-select:focus {
    border-color: var(--pink-500) !important;
    box-shadow: 0 0 0 2px rgba(236,72,153,0.15) !important;
}
.form-check-input:checked {
    background-color: var(--pink-500);
    border-color: var(--pink-500);
}
.btn-pink {
    background: linear-gradient(135deg, #EC4899, #DB2777);
    color: #fff;
    border: none;
    font-weight: 700;
    border-radius: 10px;
    transition: all .2s;
}
.btn-pink:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(236,72,153,.35);
    color: #fff;
}
.btn-outline-secondary:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}
#inlineImageUpload:hover {
    border-color: var(--pink-300) !important;
    background: var(--gray-50);
}

/* Preview styles */
.blog-content-preview h2 { font-size:1.5rem; font-weight:900; color:#0f172a; margin-top:2rem; margin-bottom:1rem; }
.blog-content-preview h3 { font-size:1.2rem; font-weight:800; color:#1e293b; margin-top:1.5rem; margin-bottom:.75rem; }
.blog-content-preview p { margin-bottom:1rem; line-height:1.9; text-align:justify; color:#475569; }
.blog-content-preview ul, .blog-content-preview ol { margin-bottom:1rem; padding-right:1.5rem; }
.blog-content-preview li { margin-bottom:.5rem; line-height:1.8; color:#475569; }
.blog-content-preview strong { color:#0f172a; }
.blog-content-preview blockquote { border-right:3px solid #ec4899; padding:.75rem 1.25rem; margin:1.5rem 0; background:#fdf2f8; border-radius:0 .75rem .75rem 0; font-size:.95rem; color:#475569; }
.blog-content-preview img { max-width:100%; border-radius:.75rem; margin:1rem 0; }
.blog-content-preview .blog-info-box { background:linear-gradient(135deg,#DBEAFE,#BFDBFE); border:2px solid #3B82F6; border-radius:12px; padding:20px; margin:20px 0; }
.blog-content-preview .blog-info-box h4 { color:#1E40AF; font-weight:700; margin-bottom:10px; }
.blog-content-preview .blog-info-box p { color:#1E3A5F; }
.blog-content-preview .blog-warning-box { background:linear-gradient(135deg,#FEE2E2,#FECACA); border:2px solid #EF4444; border-radius:12px; padding:20px; margin:20px 0; }
.blog-content-preview .blog-warning-box h4 { color:#DC2626; font-weight:700; margin-bottom:10px; }
.blog-content-preview .blog-warning-box p { color:#7F1D1D; }

/* Textarea scrollbar */
#blogContentArea::-webkit-scrollbar { width: 6px; }
#blogContentArea::-webkit-scrollbar-track { background: var(--gray-50); border-radius: 3px; }
#blogContentArea::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 3px; }
</style>

<script>
(function() {
    const textarea = document.getElementById('blogContentArea');
    const titleInput = document.querySelector('input[name="title_ar"]');
    const excerptTextarea = document.querySelector('textarea[name="excerpt_ar"]');
    const metaTitleInput = document.getElementById('metaTitleInput');
    const metaDescInput = document.getElementById('metaDescInput');

    window.wrapText = function(textareaId, before, after) {
        const ta = document.getElementById(textareaId);
        const start = ta.selectionStart, end = ta.selectionEnd;
        const selected = ta.value.substring(start, end);
        const newText = before + selected + after;
        ta.value = ta.value.substring(0, start) + newText + ta.value.substring(end);
        ta.selectionStart = start + before.length;
        ta.selectionEnd = start + before.length + selected.length;
        ta.focus();
        updateStats();
    };

    window.insertList = function(textareaId, type) {
        const ta = document.getElementById(textareaId);
        const cursorPos = ta.selectionStart;
        const listItem = '<li>عنصر القائمة</li>';
        const list = '<' + type + '>\n    ' + listItem + '\n    ' + listItem + '\n    ' + listItem + '\n</' + type + '>\n';
        ta.value = ta.value.substring(0, cursorPos) + '\n' + list + ta.value.substring(cursorPos);
        ta.focus();
        updateStats();
    };

    window.insertInfoBox = function(textareaId) {
        const ta = document.getElementById(textareaId);
        const cursorPos = ta.selectionStart;
        const box = '\n<div class="blog-info-box">\n    <h4><i class="fas fa-info-circle"></i> معلومة مهمة</h4>\n    <p class="mb-0">اكتب المعلومة المهمة هنا...</p>\n</div>\n';
        ta.value = ta.value.substring(0, cursorPos) + box + ta.value.substring(cursorPos);
        ta.focus();
        updateStats();
    };

    window.insertWarningBox = function(textareaId) {
        const ta = document.getElementById(textareaId);
        const cursorPos = ta.selectionStart;
        const box = '\n<div class="blog-warning-box">\n    <h4><i class="fas fa-exclamation-triangle"></i> تنبيه مهم</h4>\n    <p class="mb-0">اكتب نص التنبيه هنا...</p>\n</div>\n';
        ta.value = ta.value.substring(0, cursorPos) + box + ta.value.substring(cursorPos);
        ta.focus();
        updateStats();
    };

    const inlineInput = document.getElementById('inlineImageInput');
    if (inlineInput && textarea) {
        inlineInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const inlineProgress = document.getElementById('inlineImageProgress');
            const inlineProgressBar = inlineProgress?.querySelector('.progress-bar');
            const inlineResult = document.getElementById('inlineImageResult');

            inlineProgress?.classList.remove('d-none');
            inlineResult?.classList.add('d-none');
            if (inlineProgressBar) inlineProgressBar.style.width = '30%';

            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            try {
                const response = await fetch('{{ route("admin.blog.upload-inline-image") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (inlineProgressBar) inlineProgressBar.style.width = '70%';
                const data = await response.json();

                if (data.success && data.html) {
                    const cursorPos = textarea.selectionStart;
                    const textBefore = textarea.value.substring(0, cursorPos);
                    const textAfter = textarea.value.substring(cursorPos);
                    const insert = '\n' + data.html + '\n';
                    textarea.value = textBefore + insert + textAfter;
                    textarea.selectionStart = textarea.selectionEnd = cursorPos + insert.length;
                    textarea.focus();
                    updateStats();

                    if (inlineProgressBar) inlineProgressBar.style.width = '100%';
                    setTimeout(() => {
                        inlineProgress?.classList.add('d-none');
                        inlineResult?.classList.remove('d-none');
                        setTimeout(() => inlineResult?.classList.add('d-none'), 3000);
                    }, 400);
                } else {
                    alert('حدث خطأ أثناء رفع الصورة');
                    inlineProgress?.classList.add('d-none');
                }
            } catch (err) {
                alert('حدث خطأ في الاتصال');
                inlineProgress?.classList.add('d-none');
            }
            inlineInput.value = '';
        });
    }

    window.previewMainImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('mainImagePreview').classList.remove('d-none');
                document.getElementById('mainImagePlaceholder').classList.add('d-none');
                document.getElementById('mainImagePreviewImg').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.removeMainImage = function() {
        document.getElementById('mainImagePreview').classList.add('d-none');
        document.getElementById('mainImagePlaceholder').classList.remove('d-none');
        document.getElementById('mainImageInput').value = '';
    };

    window.refreshPreview = function() {
        const preview = document.getElementById('livePreview');
        const title = titleInput?.value || 'عنوان المقال';
        const content = textarea?.value || '';
        const excerpt = excerptTextarea?.value || '';

        let html = '';

        html += '<div style="margin-bottom:1.5rem;">';
        html += '<a href="#" style="color:#be185d;font-size:.8rem;font-weight:700;text-decoration:none;">&larr; العودة للمدونة</a>';
        html += '</div>';

        const catColors = { articles: '#ec4899', tips: '#0891b2', news: '#d4af37', guides: '#16a34a' };
        const catLabels = { articles: 'مقالات عن المنتجات', tips: 'نصائح للعناية الشاملة', news: 'أخبار التجميل', guides: 'أدلة الاستخدام' };
        const catEl = document.querySelector('select[name="category"]');
        const catValue = catEl?.value || 'articles';
        const catColor = catColors[catValue] || '#64748b';
        const catLabel = catLabels[catValue] || catValue;

        html += '<div style="margin-bottom:1.5rem;">';
        html += '<span style="display:inline-block;font-size:.7rem;font-weight:700;color:' + catColor + ';background:' + catColor + '10;padding:.3rem .85rem;border-radius:9999px;margin-bottom:.75rem;">' + catLabel + '</span>';
        html += '<h1 style="font-size:1.5rem;font-weight:900;color:#0f172a;line-height:1.3;margin-bottom:.5rem;">' + escapeHtml(title) + '</h1>';
        if (excerpt) {
            html += '<p style="color:#64748b;font-size:.85rem;">' + escapeHtml(excerpt) + '</p>';
        }
        html += '</div>';

        html += '<div style="color:#334155;font-size:1.05rem;line-height:2;" class="blog-content-preview">';
        html += content || '<p style="color:#94a3b8;text-align:center;">اكتب محتوى المقال لتظهر المعاينة...</p>';
        html += '</div>';

        preview.innerHTML = html;
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.updateSEOPreview = function() {
        const titleVal = metaTitleInput?.value || titleInput?.value || 'عنوان المقال';
        const descVal = metaDescInput?.value || excerptTextarea?.value || '';
        const titleSlug = titleInput?.value?.replace(/[^\w\s]/g, '').replace(/\s+/g, '-').substring(0, 50) || 'post-slug';

        document.getElementById('seoTitle').textContent = titleVal.substring(0, 60);
        document.getElementById('seoSlug').textContent = titleSlug;
        document.getElementById('seoDesc').textContent = descVal.substring(0, 160);
    };

    function updateStats() {
        if (titleInput) document.getElementById('statTitleChars').textContent = titleInput.value.length;
        if (textarea) {
            const text = textarea.value;
            const cleanText = text.replace(/<[^>]*>/g, '');
            const words = cleanText.trim() ? cleanText.trim().split(/\s+/).length : 0;
            document.getElementById('statContentWords').textContent = words;
            document.getElementById('contentWordCount').textContent = words;
            document.getElementById('statContentLines').textContent = text.split('\n').length;
        }
        if (excerptTextarea) {
            document.getElementById('statExcerptChars').textContent = excerptTextarea.value.length;
        }
    }

    document.querySelectorAll('.char-count').forEach(el => {
        const target = el.dataset.target;
        const input = document.querySelector('[name="' + target + '"]');
        if (input) {
            input.addEventListener('input', function() {
                el.textContent = this.value.length + '/' + (this.maxLength || '∞');
            });
            el.textContent = input.value.length + '/' + (input.maxLength || '∞');
        }
    });

    if (titleInput) titleInput.addEventListener('input', updateStats);
    if (textarea) textarea.addEventListener('input', updateStats);
    if (excerptTextarea) excerptTextarea.addEventListener('input', updateStats);

    if (titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('statTitleChars').textContent = this.value.length;
            updateSEOPreview();
        });
    }

    updateStats();
    updateSEOPreview();

    if (excerptTextarea) {
        excerptTextarea.addEventListener('input', updateSEOPreview);
    }
})();
</script>