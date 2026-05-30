<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content Column --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Tab Navigation --}}
        <div class="flex items-center gap-1 bg-white/5 rounded-xl p-1 border border-white/10" role="tablist">
            <button type="button" class="tab-btn-admin active" data-tab="tab-content" onclick="switchTab('tab-content', this)">
                <i class="fas fa-pen-fancy ml-1.5"></i> المحتوى الرئيسي
            </button>
            <button type="button" class="tab-btn-admin" data-tab="tab-seo" onclick="switchTab('tab-seo', this)">
                <i class="fas fa-search ml-1.5"></i> تحسين SEO
            </button>
            <button type="button" class="tab-btn-admin" data-tab="tab-images" onclick="switchTab('tab-images', this)">
                <i class="fas fa-images ml-1.5"></i> الصور
            </button>
            <button type="button" class="tab-btn-admin" data-tab="tab-preview" onclick="switchTab('tab-preview', this)">
                <i class="fas fa-eye ml-1.5"></i> معاينة
            </button>
        </div>

        {{-- TAB 1: Main Content --}}
        <div id="tab-content" class="tab-panel-admin">
            <div class="glass-panel rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-ink">بيانات المقال الأساسية</h3>
                    <span class="text-[10px] text-ink-dim px-2 py-1 bg-white/5 rounded-lg">جميع الحقول المطلوبة</span>
                </div>

                <div>
                    <label class="block text-ink-dim text-xs mb-1.5 font-medium">عنوان المقال <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-heading absolute right-3 top-1/2 -translate-y-1/2 text-pink-400 text-xs"></i>
                        <input type="text" name="title_ar" value="{{ $post->title_ar ?? old('title_ar') }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl pr-10 px-4 py-3.5 text-sm focus:border-pink-500 focus:outline-none transition-all"
                               placeholder="أدخل عنوان المقال هنا...">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-ink-dim text-xs mb-1.5 font-medium">القسم <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <i class="fas fa-folder absolute right-3 top-1/2 -translate-y-1/2 text-pink-400 text-xs"></i>
                            <select name="category" required class="w-full bg-white/5 border border-white/10 rounded-xl pr-10 px-4 py-3.5 text-sm focus:border-pink-500 focus:outline-none appearance-none transition-all">
                                <option value="">اختر القسم</option>
                                <option value="articles" {{ ($post->category ?? old('category')) === 'articles' ? 'selected' : '' }}>📦 مقالات عن المنتجات</option>
                                <option value="tips" {{ ($post->category ?? old('category')) === 'tips' ? 'selected' : '' }}>💡 نصائح للعناية الشاملة</option>
                                <option value="news" {{ ($post->category ?? old('category')) === 'news' ? 'selected' : '' }}>📰 أخبار التجميل</option>
                                <option value="guides" {{ ($post->category ?? old('category')) === 'guides' ? 'selected' : '' }}>📖 أدلة الاستخدام</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-ink-dim text-xs mb-1.5 font-medium">ترتيب العرض</label>
                        <div class="relative">
                            <i class="fas fa-sort-numeric-down absolute right-3 top-1/2 -translate-y-1/2 text-pink-400 text-xs"></i>
                            <input type="number" name="sort_order" value="{{ $post->sort_order ?? old('sort_order', 0) }}"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl pr-10 px-4 py-3.5 text-sm focus:border-pink-500 focus:outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-ink-dim text-xs mb-1.5 font-medium">ملخص المقال</label>
                    <div class="relative">
                        <i class="fas fa-paragraph absolute right-3 top-3 text-pink-400 text-xs"></i>
                        <textarea name="excerpt_ar" rows="2" maxlength="500"
                                  class="w-full bg-white/5 border border-white/10 rounded-xl pr-10 px-4 py-3 text-sm focus:border-pink-500 focus:outline-none transition-all resize-y"
                                  placeholder="ملخص قصير يظهر في بطاقة المقال...">{{ $post->excerpt_ar ?? old('excerpt_ar') }}</textarea>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-[10px] text-ink-dim">أقصى حد 500 حرف</span>
                        <span class="text-[10px] text-ink-dim char-count" data-target="excerpt_ar">0/500</span>
                    </div>
                </div>

                {{-- Publishing Status --}}
                <div class="flex items-center gap-4 pt-3 border-t border-white/5">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <div class="relative">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1" {{ ($post->is_published ?? true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-white/10 rounded-full peer-checked:bg-pink-500 transition-all peer-checked:shadow-lg peer-checked:shadow-pink-500/30 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                        </div>
                        <span class="text-sm group-hover:text-ink transition-colors">منشور</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <div class="relative">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" {{ ($post->is_featured ?? false) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-white/10 rounded-full peer-checked:bg-yellow-500 transition-all peer-checked:shadow-lg peer-checked:shadow-yellow-500/30 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                        </div>
                        <span class="text-sm group-hover:text-ink transition-colors"><i class="fas fa-star text-yellow-500 ml-1"></i> مميز</span>
                    </label>
                </div>
            </div>

            {{-- Content Editor --}}
            <div class="glass-panel rounded-2xl p-5 mt-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-ink flex items-center gap-2">
                        <i class="fas fa-code text-pink-400"></i> محتوى المقال <span class="text-red-400">*</span>
                    </h3>
                    <div class="flex items-center gap-1" id="editorToolbar">
                        <button type="button" onclick="wrapText('blogContentArea', '<strong>', '</strong>')" class="toolbar-btn" title="عريض"><i class="fas fa-bold"></i></button>
                        <button type="button" onclick="wrapText('blogContentArea', '<em>', '</em>')" class="toolbar-btn" title="مائل"><i class="fas fa-italic"></i></button>
                        <button type="button" onclick="wrapText('blogContentArea', '<u>', '</u>')" class="toolbar-btn" title="تسطير"><i class="fas fa-underline"></i></button>
                        <span class="w-px h-4 bg-white/10 mx-1"></span>
                        <button type="button" onclick="wrapText('blogContentArea', '<h2>', '</h2>')" class="toolbar-btn" title="عنوان H2">H2</button>
                        <button type="button" onclick="wrapText('blogContentArea', '<h3>', '</h3>')" class="toolbar-btn" title="عنوان H3">H3</button>
                        <span class="w-px h-4 bg-white/10 mx-1"></span>
                        <button type="button" onclick="wrapText('blogContentArea', '<p>', '</p>')" class="toolbar-btn" title="فقرة">¶</button>
                        <button type="button" onclick="insertList('blogContentArea', 'ul')" class="toolbar-btn" title="قائمة غير مرقمة"><i class="fas fa-list-ul"></i></button>
                        <button type="button" onclick="insertList('blogContentArea', 'ol')" class="toolbar-btn" title="قائمة مرقمة"><i class="fas fa-list-ol"></i></button>
                        <span class="w-px h-4 bg-white/10 mx-1"></span>
                        <button type="button" onclick="wrapText('blogContentArea', '<blockquote>', '</blockquote>')" class="toolbar-btn" title="اقتباس"><i class="fas fa-quote-right"></i></button>
                        <button type="button" onclick="insertInfoBox('blogContentArea')" class="toolbar-btn" title="معلومة"><i class="fas fa-info-circle"></i></button>
                        <button type="button" onclick="insertWarningBox('blogContentArea')" class="toolbar-btn" title="تنبيه"><i class="fas fa-exclamation-triangle"></i></button>
                    </div>
                </div>
                <div class="relative">
                    <textarea id="blogContentArea" name="content_ar" rows="20" required
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-sm focus:border-pink-500 focus:outline-none font-mono ltr text-left transition-all resize-y leading-relaxed"
                              dir="ltr" placeholder="اكتب محتوى المقال هنا... (يدعم HTML)">{{ $post->content_ar ?? old('content_ar') }}</textarea>
                    <div class="absolute bottom-3 left-3 text-[10px] text-ink-dim bg-white/5 px-2 py-0.5 rounded">
                        <span id="contentWordCount">0</span> كلمة
                    </div>
                </div>
                <p class="text-ink-dim text-[10px] mt-2 flex items-center gap-1.5">
                    <i class="fas fa-info-circle text-pink-400"></i>
                    يمكن استخدام HTML: p, h2, h3, ul, ol, li, strong, a, img, blockquote, br, div, span
                </p>
            </div>

            {{-- Inline Image Upload --}}
            <div class="glass-panel rounded-2xl p-5 mt-5">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center">
                        <i class="fas fa-image text-pink-400 text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-ink">إدراج صور داخل المقال</h3>
                        <p class="text-[10px] text-ink-dim">ارفع صورة وسيتم إدراجها تلقائياً في موضع المؤشر</p>
                    </div>
                </div>
                <div id="inlineImageUpload" class="border-2 border-dashed border-white/10 rounded-xl p-6 text-center hover:border-pink-500/30 transition-all cursor-pointer" onclick="document.getElementById('inlineImageInput').click()">
                    <input type="file" id="inlineImageInput" accept="image/*" class="hidden">
                    <i class="fas fa-cloud-upload-alt text-2xl text-ink-dim mb-2" style="opacity:0.5;"></i>
                    <p class="text-xs text-ink-dim">اضغط لرفع صورة أو اسحب وأفلت</p>
                    <p class="text-[10px] text-ink-dim mt-1">PNG, JPG, WebP - أقصى حجم 5MB</p>
                    <div id="inlineImageProgress" class="hidden mt-3">
                        <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-pink-500 transition-all duration-300 rounded-full" style="width:0%"></div>
                        </div>
                        <p class="text-[10px] text-pink-400 mt-1.5 flex items-center gap-1.5 justify-center">
                            <i class="fas fa-spinner fa-spin"></i> جاري الرفع...
                        </p>
                    </div>
                    <div id="inlineImageResult" class="hidden mt-3 p-3 rounded-lg bg-green-500/10 border border-green-500/20">
                        <p class="text-xs text-green-400 flex items-center gap-1.5 justify-center">
                            <i class="fas fa-check-circle"></i> تم رفع الصورة وإدراجها في المقال
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: SEO --}}
        <div id="tab-seo" class="tab-panel-admin hidden">
            <div class="glass-panel rounded-2xl p-5 space-y-4">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                        <i class="fas fa-search text-yellow-400 text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-ink">إعدادات تحسين محركات البحث (SEO)</h3>
                        <p class="text-[10px] text-ink-dim">حسّن ظهور المقال في نتائج البحث</p>
                    </div>
                </div>

                <div>
                    <label class="block text-ink-dim text-xs mb-1.5 font-medium">Meta Title <span class="text-[10px] text-ink-dim">(إذا تركت فارغاً، سيتم استخدام عنوان المقال)</span></label>
                    <input type="text" name="meta_title" value="{{ $post->meta_title ?? old('meta_title') }}" maxlength="255"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-pink-500 focus:outline-none ltr text-left transition-all"
                           dir="ltr" placeholder="{{ Str::limit($post->title_ar ?? '', 60) }}" id="metaTitleInput"
                           oninput="updateSEOPreview()">
                    <div class="flex justify-between mt-1">
                        <span class="text-[10px] text-ink-dim">أقصى حد 255 حرف</span>
                        <span class="text-[10px] text-ink-dim char-count" data-target="meta_title">0/255</span>
                    </div>
                </div>

                <div>
                    <label class="block text-ink-dim text-xs mb-1.5 font-medium">Meta Description</label>
                    <textarea name="meta_description" rows="3" maxlength="500"
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-pink-500 focus:outline-none ltr text-left transition-all resize-none"
                              dir="ltr" placeholder="وصف مختصر يظهر في نتائج البحث..." id="metaDescInput"
                              oninput="updateSEOPreview()">{{ $post->meta_description ?? old('meta_description') }}</textarea>
                    <div class="flex justify-between mt-1">
                        <span class="text-[10px] text-ink-dim">أقصى حد 500 حرف - يُنصح بـ 150-160 حرف</span>
                        <span class="text-[10px] text-ink-dim char-count" data-target="meta_description">0/500</span>
                    </div>
                </div>

                {{-- SEO Preview --}}
                <div class="border border-white/10 rounded-xl p-4 mt-3">
                    <label class="block text-ink-dim text-xs mb-2 font-medium flex items-center gap-1.5">
                        <i class="fab fa-google text-blue-400"></i> معاينة نتائج البحث (Google Preview)
                    </label>
                    <div id="seoPreview" class="bg-white rounded-lg p-3" dir="ltr" style="font-family:Arial,sans-serif;">
                        <div id="seoUrl" class="text-green-700 text-xs">https://jenincare.shop/blog/<span id="seoSlug">post-slug</span></div>
                        <div id="seoTitle" class="text-blue-800 text-sm font-medium hover:underline cursor-pointer mt-0.5">{{ Str::limit($post->meta_title ?? $post->title_ar ?? 'عنوان المقال', 60) }}</div>
                        <div id="seoDesc" class="text-gray-600 text-xs mt-0.5 line-clamp-2">{{ Str::limit(strip_tags($post->meta_description ?? $post->excerpt_ar ?? $post->content_ar ?? ''), 160) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: Images --}}
        <div id="tab-images" class="tab-panel-admin hidden">
            <div class="glass-panel rounded-2xl p-5 space-y-5">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                        <i class="fas fa-images text-purple-400 text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-ink">صور المقال</h3>
                        <p class="text-[10px] text-ink-dim">الصورة الرئيسية للمقال وألبوم الصور</p>
                    </div>
                </div>

                {{-- Main Image --}}
                <div>
                    <label class="block text-ink-dim text-xs mb-1.5 font-medium">الصورة الرئيسية للمقال</label>
                    <div class="relative border-2 border-dashed border-white/10 rounded-xl p-4 hover:border-pink-500/30 transition-all text-center" id="mainImageDropzone">
                        <div id="mainImagePreview" class="{{ empty($post->image_url) ? 'hidden' : '' }} mb-3">
                            <img src="{{ $post->image_url ?? '' }}" id="mainImagePreviewImg"
                                 class="w-full max-h-48 object-cover rounded-lg mx-auto">
                            <button type="button" onclick="removeMainImage()"
                                    class="mt-2 text-[10px] text-red-400 hover:text-red-300 transition-colors">
                                <i class="fas fa-times ml-1"></i> إزالة الصورة
                            </button>
                        </div>
                        <div id="mainImagePlaceholder" class="{{ !empty($post->image_url) ? 'hidden' : '' }}">
                            <i class="fas fa-camera text-2xl text-ink-dim mb-2" style="opacity:0.4;"></i>
                            <p class="text-xs text-ink-dim mb-1">اختر صورة رئيسية للمقال</p>
                            <p class="text-[10px] text-ink-dim">JPEG, PNG, WebP - أقصى حجم 5MB</p>
                            <label class="inline-block mt-3 px-4 py-2 bg-pink-500/80 text-white text-xs rounded-lg cursor-pointer hover:bg-pink-500 transition-all font-medium">
                                <i class="fas fa-upload ml-1"></i> اختيار صورة
                                <input type="file" name="image" accept="image/*" class="hidden" id="mainImageInput" onchange="previewMainImage(this)">
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Gallery Images --}}
                <div>
                    <label class="block text-ink-dim text-xs mb-1.5 font-medium">ألبوم الصور الإضافية</label>
                    <div class="grid grid-cols-3 gap-3" id="galleryGrid">
                        <div class="border-2 border-dashed border-white/10 rounded-xl p-4 text-center hover:border-pink-500/30 transition-all cursor-pointer flex flex-col items-center justify-center min-h-[120px]" onclick="document.getElementById('galleryInput').click()">
                            <i class="fas fa-plus text-lg text-ink-dim" style="opacity:0.4;"></i>
                            <p class="text-[10px] text-ink-dim mt-1">إضافة صورة</p>
                        </div>
                    </div>
                    <input type="file" id="galleryInput" accept="image/*" multiple class="hidden">
                    <p class="text-[10px] text-ink-dim mt-2">يمكن إضافة صور متعددة لعرضها في معرض المقال</p>
                </div>
            </div>
        </div>

        {{-- TAB 4: Preview --}}
        <div id="tab-preview" class="tab-panel-admin hidden">
            <div class="glass-panel rounded-2xl p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                        <i class="fas fa-eye text-cyan-400 text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-ink">معاينة المقال</h3>
                        <p class="text-[10px] text-ink-dim">شكل المقال النهائي على الموقع</p>
                    </div>
                </div>
                <div id="livePreview" class="bg-white rounded-xl p-6" style="font-family:'Tajawal',sans-serif;">
                    <div class="text-center text-ink-dim text-xs py-8">
                        <i class="fas fa-spinner fa-spin text-lg mb-2"></i>
                        <p>اضغط "تحديث المعاينة" لعرض المقال</p>
                    </div>
                </div>
                <button type="button" onclick="refreshPreview()"
                        class="mt-3 w-full py-2.5 bg-pink-500/10 text-pink-400 text-sm font-bold rounded-xl hover:bg-pink-500/20 transition-all">
                    <i class="fas fa-sync-alt ml-1.5"></i> تحديث المعاينة
                </button>
            </div>
        </div>
    </div>

    {{-- Sidebar Column --}}
    <div class="space-y-4">

        {{-- Save Card --}}
        <div class="glass-panel rounded-2xl p-5 sticky" style="top:5.5rem;">
            <h3 class="text-xs font-bold text-ink mb-3 flex items-center gap-2">
                <i class="fas fa-floppy-disk text-pink-400"></i> إجراءات
            </h3>

            <button type="submit" class="w-full py-3 bg-gradient-to-l from-pink-600 to-pink-500 text-white text-sm font-bold rounded-xl hover:from-pink-700 hover:to-pink-600 transition-all shadow-lg shadow-pink-500/20 mb-2">
                <i class="fas fa-save ml-1.5"></i> {{ isset($isEdit) && $isEdit ? 'حفظ التعديلات' : 'نشر المقال' }}
            </button>

            @if(isset($isEdit) && $isEdit)
            <a href="{{ route('admin.blog.create') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-white/5 text-ink-dim text-sm font-medium rounded-xl hover:bg-white/10 transition-all border border-white/10 mb-2">
                <i class="fas fa-plus"></i> مقال جديد
            </a>
            <a href="#" onclick="event.preventDefault(); if(confirm('متأكد من حذف هذا المقال؟')) document.getElementById('delete-form').submit();"
               class="flex items-center justify-center gap-2 w-full py-2.5 bg-red-500/5 text-red-400 text-sm font-medium rounded-xl hover:bg-red-500/10 transition-all border border-red-500/10">
                <i class="fas fa-trash-alt"></i> حذف المقال
            </a>
            @endif

            <div class="mt-4 pt-4 border-t border-white/5 space-y-2.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-ink-dim">الحالة</span>
                    <span class="font-bold text-ink">{{ ($post->is_published ?? true) ? 'منشور' : 'مسودة' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-ink-dim">تاريخ الإنشاء</span>
                    <span class="font-bold text-ink">{{ isset($post) && $post->created_at ? $post->created_at->format('Y-m-d') : '--' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-ink-dim">آخر تحديث</span>
                    <span class="font-bold text-ink">{{ isset($post) && $post->updated_at ? $post->updated_at->format('Y-m-d') : '--' }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Tips Card --}}
        <div class="glass-panel rounded-2xl p-5">
            <h3 class="text-xs font-bold text-ink mb-3 flex items-center gap-2">
                <i class="fas fa-lightbulb text-yellow-400"></i> نصائح سريعة
            </h3>
            <ul class="space-y-2">
                <li class="text-[11px] text-ink-dim flex items-start gap-2">
                    <i class="fas fa-check-circle text-green-400 mt-0.5 text-[8px]"></i>
                    استخدم عنواناً جذاباً وواضحاً
                </li>
                <li class="text-[11px] text-ink-dim flex items-start gap-2">
                    <i class="fas fa-check-circle text-green-400 mt-0.5 text-[8px]"></i>
                    أضف ملخصاً قصيراً للمقال
                </li>
                <li class="text-[11px] text-ink-dim flex items-start gap-2">
                    <i class="fas fa-check-circle text-green-400 mt-0.5 text-[8px]"></i>
                    استخدم صوراً عالية الجودة
                </li>
                <li class="text-[11px] text-ink-dim flex items-start gap-2">
                    <i class="fas fa-check-circle text-green-400 mt-0.5 text-[8px]"></i>
                    اختر القسم المناسب للمقال
                </li>
                <li class="text-[11px] text-ink-dim flex items-start gap-2">
                    <i class="fas fa-check-circle text-green-400 mt-0.5 text-[8px]"></i>
                    املأ حقول SEO لظهور أفضل
                </li>
            </ul>
        </div>

        {{-- Character Count Summary --}}
        <div class="glass-panel rounded-2xl p-5">
            <h3 class="text-xs font-bold text-ink mb-3 flex items-center gap-2">
                <i class="fas fa-chart-bar text-blue-400"></i> إحصائيات
            </h3>
            <div class="space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-ink-dim">حروف العنوان</span>
                    <span class="text-ink font-bold" id="statTitleChars">0</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-ink-dim">كلمات المحتوى</span>
                    <span class="text-ink font-bold" id="statContentWords">0</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-ink-dim">سطور المحتوى</span>
                    <span class="text-ink font-bold" id="statContentLines">0</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-ink-dim">حروف الملخص</span>
                    <span class="text-ink font-bold" id="statExcerptChars">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // ===== INIT =====
    const textarea = document.getElementById('blogContentArea');
    const titleInput = document.querySelector('input[name="title_ar"]');
    const excerptTextarea = document.querySelector('textarea[name="excerpt_ar"]');
    const metaTitleInput = document.getElementById('metaTitleInput');
    const metaDescInput = document.getElementById('metaDescInput');

    // ===== TAB SWITCHING =====
    window.switchTab = function(tabId, btn) {
        document.querySelectorAll('.tab-panel-admin').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tab-btn-admin').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.remove('hidden');
        btn.classList.add('active');
        if (tabId === 'tab-preview') refreshPreview();
    };

    // ===== TEXT EDITOR TOOLBAR =====
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

    // ===== INLINE IMAGE UPLOAD =====
    const inlineInput = document.getElementById('inlineImageInput');
    const inlineProgress = document.getElementById('inlineImageProgress');
    const inlineProgressBar = inlineProgress?.querySelector('div > div');
    const inlineResult = document.getElementById('inlineImageResult');

    if (inlineInput && textarea) {
        inlineInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            inlineProgress?.classList.remove('hidden');
            inlineResult?.classList.add('hidden');
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
                        inlineProgress?.classList.add('hidden');
                        inlineResult?.classList.remove('hidden');
                        setTimeout(() => inlineResult?.classList.add('hidden'), 3000);
                    }, 400);
                } else {
                    alert('حدث خطأ أثناء رفع الصورة');
                    inlineProgress?.classList.add('hidden');
                }
            } catch (err) {
                alert('حدث خطأ في الاتصال');
                inlineProgress?.classList.add('hidden');
            }
            inlineInput.value = '';
        });
    }

    // ===== MAIN IMAGE PREVIEW =====
    window.previewMainImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('mainImagePreview').classList.remove('hidden');
                document.getElementById('mainImagePlaceholder').classList.add('hidden');
                document.getElementById('mainImagePreviewImg').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.removeMainImage = function() {
        document.getElementById('mainImagePreview').classList.add('hidden');
        document.getElementById('mainImagePlaceholder').classList.remove('hidden');
        document.getElementById('mainImageInput').value = '';
    };

    // ===== LIVE PREVIEW =====
    window.refreshPreview = function() {
        const preview = document.getElementById('livePreview');
        const title = titleInput?.value || 'عنوان المقال';
        const content = textarea?.value || '';
        const excerpt = excerptTextarea?.value || '';

        let html = '';

        // Breadcrumb
        html += '<div style="margin-bottom:1.5rem;">';
        html += '<a href="#" style="color:#be185d;font-size:.8rem;font-weight:700;text-decoration:none;">&larr; العودة للمدونة</a>';
        html += '</div>';

        // Category Badge + Date
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

        // Content
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

    // ===== SEO PREVIEW =====
    window.updateSEOPreview = function() {
        const titleVal = metaTitleInput?.value || titleInput?.value || 'عنوان المقال';
        const descVal = metaDescInput?.value || excerptTextarea?.value || '';
        const titleSlug = titleInput?.value?.replace(/[^\w\s]/g, '').replace(/\s+/g, '-').substring(0, 50) || 'post-slug';

        document.getElementById('seoTitle').textContent = titleVal.substring(0, 60);
        document.getElementById('seoSlug').textContent = titleSlug;
        document.getElementById('seoDesc').textContent = descVal.substring(0, 160);
    };

    // ===== STATS =====
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

    // Char counts
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

    // Auto-update stats on input
    if (titleInput) titleInput.addEventListener('input', updateStats);
    if (textarea) textarea.addEventListener('input', updateStats);
    if (excerptTextarea) excerptTextarea.addEventListener('input', updateStats);

    // Update title chars on input
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            document.getElementById('statTitleChars').textContent = this.value.length;
            updateSEOPreview();
        });
    }

    // Initial stats
    updateStats();
    updateSEOPreview();

    // Sync excerpt to SEO preview
    if (excerptTextarea) {
        excerptTextarea.addEventListener('input', updateSEOPreview);
    }
})();
</script>

<style>
.tab-btn-admin {
    padding: 0.5rem 0.85rem;
    border-radius: 0.65rem;
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.5);
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.tab-btn-admin:hover { color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.05); }
.tab-btn-admin.active { background: rgba(236,72,153,0.15); color: #f9a8d4; box-shadow: 0 0 0 1px rgba(236,72,153,0.2); }

.toolbar-btn {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.5);
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.toolbar-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }

.glass-panel { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(12px); }

.text-ink { color: #f1f5f9; }
.text-ink-dim { color: rgba(255,255,255,0.4); }

.tab-panel-admin.hidden { display: none; }

/* Line clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Sticky sidebar */
@media (min-width: 1024px) {
    .sticky { position: sticky; top: 5.5rem; }
}

/* Custom scrollbar for textarea */
#blogContentArea::-webkit-scrollbar { width: 6px; }
#blogContentArea::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); border-radius: 3px; }
#blogContentArea::-webkit-scrollbar-thumb { background: rgba(236,72,153,0.3); border-radius: 3px; }

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
</style>
