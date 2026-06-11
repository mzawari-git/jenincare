@if(isset($results) && $results['success'] && count($results['ads']))
    @foreach($results['ads'] as $ad)
    <div class="border rounded p-3 mb-2 hover-bg-light">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">{{ $ad['creative_title'] ?? 'بدون عنوان' }}</h6>
                <p class="small text-muted mb-1">{{ Str::limit($ad['creative_body'] ?? '', 150) }}</p>
                <div class="d-flex gap-2 small text-muted">
                    @if(!empty($ad['page_name']))
                        <span><i class="fas fa-building"></i> {{ $ad['page_name'] }}</span>
                    @endif
                    @if(!empty($ad['platform']))
                        <span><i class="fas fa-mobile-alt"></i> {{ $ad['platform'] }}</span>
                    @endif
                    @if(!empty($ad['estimated_spend']))
                        <span><i class="fas fa-dollar-sign"></i> {{ $ad['estimated_spend'] }}</span>
                    @endif
                </div>
            </div>
            @if(!empty($ad['cta_text']))
                <span class="badge bg-primary ms-2">{{ $ad['cta_text'] }}</span>
            @endif
        </div>
        @if(!empty($ad['cta_link']) || !empty($ad['ad_url']))
            <div class="mt-2">
                <a href="{{ $ad['cta_link'] ?? $ad['ad_url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> عرض الإعلان
                </a>
            </div>
        @endif
    </div>
    @endforeach
    @if(!empty($results['total']))
        <div class="text-center text-muted small py-2">
            إجمالي النتائج: {{ $results['total'] }}
        </div>
    @endif
@elseif(isset($results) && $results['success'] && empty($results['ads']))
    <div class="text-center py-4 text-muted">
        <i class="fas fa-search fa-3x mb-2"></i>
        <p>لا توجد نتائج للبحث</p>
    </div>
@endif
