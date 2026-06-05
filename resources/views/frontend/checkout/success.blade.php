@extends($layoutPath)

@section('title', 'تم تأكيد الطلب - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@php
    $wheelGifts = [
        'سيروم هيالورونيك أسيد',
        'ماسك ترطيب عميق',
        'كريم عين مجدد',
        'عينة عطر فاخر',
        'مرطب شفاه عضوي',
        'خصم 15% على الطلب القادم',
        'مقشر لطيف للوجه',
        'توصيل مجاني للطلب القادم',
    ];
@endphp

@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:140px 16px 40px;">
    <div style="text-align:center;max-width:560px;width:100%;">
        <div style="width:100px;height:100px;background:linear-gradient(135deg,#10B981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2.8rem;color:#fff;box-shadow:0 12px 40px rgba(16,185,129,.3);">
            <i class="fas fa-check"></i>
        </div>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--ink);margin-bottom:8px;">تم تأكيد طلبك بنجاح!</h1>
        <p style="color:var(--ink-muted);margin-bottom:24px;line-height:1.7;">شكراً لتسوقك من شركة جنين للتجميل سيتم التواصل معك قريباً لتأكيد الطلب وترتيب التوصيل.</p>

        <div class="glass-panel" style="border-radius:16px;border:1px solid var(--glass-border);padding:24px;margin-bottom:20px;text-align:right;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:12px;margin-bottom:12px;border-bottom:1px solid var(--glass-border);">
                <span style="font-weight:700;font-size:.95rem;color:var(--ink);">تفاصيل الطلب</span>
                <span style="background:var(--brand-500);color:#fff;padding:4px 12px;border-radius:50px;font-size:.8rem;font-weight:700;">{{ $order->status === 'pending' ? 'قيد المراجعة' : $order->status }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:var(--ink-dim);font-size:.85rem;">رقم الطلب</span>
                <span style="font-weight:800;color:var(--brand-500);font-size:1.1rem;">#{{ $order->order_number }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:var(--ink-dim);font-size:.85rem;">الإجمالي</span>
                <span style="font-weight:700;color:var(--ink);">{{ number_format($order->total_amount, 2) }} ₪</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:var(--ink-dim);font-size:.85rem;">طريقة الدفع</span>
                <span style="font-weight:600;color:var(--ink);font-size:.85rem;">
                    @if($order->payment_method === 'cod') الدفع عند الاستلام
                    @elseif($order->payment_method === 'bank_transfer') تحويل بنكي
                    @elseif($order->payment_method === 'jawwal_pay') جوال باي
                    @elseif($order->payment_method === 'reflect') Reflect
                    @else {{ $order->payment_method }}
                    @endif
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;">
                <span style="color:var(--ink-dim);font-size:.85rem;">المدينة</span>
                <span style="font-weight:600;color:var(--ink);font-size:.85rem;">{{ $order->shipping_city }}</span>
            </div>
        </div>

        @php $pm = $order->payment_method; @endphp
        @if(in_array($pm, ['bank_transfer','jawwal_pay','reflect']))
        <div style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);border-radius:12px;padding:16px;margin-bottom:20px;text-align:right;">
            <p style="font-weight:700;color:#FBBF24;font-size:.85rem;margin-bottom:8px;"><i class="fas fa-info-circle"></i> تعليمات الدفع:</p>
            @if($pm === 'bank_transfer')
            @php $bankSettings = \App\Models\Setting::whereIn('key',['payment_bank_name','payment_bank_holder','payment_bank_account','payment_bank_iban'])->pluck('value','key')->toArray(); @endphp
            <p style="font-size:.8rem;color:#FBBF24;margin:2px 0;">يرجى تحويل مبلغ <strong>{{ number_format($order->total_amount, 2) }} ₪</strong> إلى الحساب التالي:</p>
            @if($bankSettings['payment_bank_name'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;"><strong>البنك:</strong> {{ $bankSettings['payment_bank_name'] }}</p>@endif
            @if($bankSettings['payment_bank_holder'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;"><strong>المستفيد:</strong> {{ $bankSettings['payment_bank_holder'] }}</p>@endif
            @if($bankSettings['payment_bank_account'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;" dir="ltr"><strong>رقم الحساب:</strong> {{ $bankSettings['payment_bank_account'] }}</p>@endif
            @if($bankSettings['payment_bank_iban'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;" dir="ltr"><strong>IBAN:</strong> {{ $bankSettings['payment_bank_iban'] }}</p>@endif
            @elseif($pm === 'jawwal_pay')
            @php $jwSettings = \App\Models\Setting::whereIn('key',['payment_jawwal_phone','payment_jawwal_holder'])->pluck('value','key')->toArray(); @endphp
            <p style="font-size:.8rem;color:#FBBF24;margin:2px 0;">يرجى إرسال مبلغ <strong>{{ number_format($order->total_amount, 2) }} ₪</strong> عبر جوال باي إلى:</p>
            @if($jwSettings['payment_jawwal_holder'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;"><strong>المستفيد:</strong> {{ $jwSettings['payment_jawwal_holder'] }}</p>@endif
            @if($jwSettings['payment_jawwal_phone'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;" dir="ltr"><strong>رقم جوال باي:</strong> {{ $jwSettings['payment_jawwal_phone'] }}</p>@endif
            @elseif($pm === 'reflect')
            @php $rfSettings = \App\Models\Setting::whereIn('key',['payment_reflect_holder','payment_reflect_phone'])->pluck('value','key')->toArray(); @endphp
            <p style="font-size:.8rem;color:#FBBF24;margin:2px 0;">يرجى إرسال مبلغ <strong>{{ number_format($order->total_amount, 2) }} ₪</strong> عبر تطبيق Reflect إلى:</p>
            @if($rfSettings['payment_reflect_holder'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;"><strong>المستفيد:</strong> {{ $rfSettings['payment_reflect_holder'] }}</p>@endif
            @if($rfSettings['payment_reflect_phone'] ?? false)<p style="font-size:.8rem;color:#FBBF24;margin:2px 0;" dir="ltr"><strong>رقم هاتف Reflect:</strong> {{ $rfSettings['payment_reflect_phone'] }}</p>@endif
            @endif
            <p style="font-size:.75rem;color:#D97706;margin-top:8px;">بعد إتمام الدفع، يرجى التواصل معنا عبر واتساب على <strong>{{ $siteSettings['site_whatsapp'] ?? '970591234567' }}</strong> مع إرفاق رقم الطلب <strong>#{{ $order->order_number }}</strong></p>
        </div>
        @endif

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            @auth
            <a href="{{ route('orders.show', $order->id) }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;transition:all .3s;background:var(--gradient-primary);color:#fff;box-shadow:var(--neon-glow);" onmouseover="this.style.boxShadow='var(--neon-glow-strong)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='var(--neon-glow)';this.style.transform='none'">
                <i class="fas fa-eye"></i> عرض تفاصيل الطلب
            </a>
            @endif
            <a href="{{ route('shop') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:transparent;color:var(--ink);border:1px solid var(--glass-border);border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;transition:all .3s;" onmouseover="this.style.borderColor='var(--brand-500)';this.style.background='var(--brand-500)'" onmouseout="this.style.borderColor='var(--glass-border)';this.style.background='transparent'">
                <i class="fas fa-store"></i> متابعة التسوق
            </a>
        </div>

        <p style="margin-top:20px;font-size:.8rem;color:var(--ink-dim);">لديك استفسار؟ <a href="https://wa.me/{{ $siteSettings['site_whatsapp'] ?? '970591234567' }}" style="color:#25D366;font-weight:600;">تواصل معنا عبر واتساب</a></p>
    </div>
</div>

{{-- Spin Code Display --}}
@if($spinCode)
<div id="spinCodeSection" style="max-width:560px;margin:-60px auto 40px;padding:0 16px;position:relative;z-index:2;">
    <div style="background:linear-gradient(135deg,var(--brand-500),#c0266b);border-radius:20px;padding:24px;text-align:center;box-shadow:0 12px 40px rgba(var(--brand-500-rgb,255,42,133),.35);position:relative;overflow:hidden;">
        <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
        <div style="position:absolute;bottom:-40px;left:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        <span style="display:inline-block;padding:4px 14px;border-radius:50px;background:rgba(255,255,255,.15);color:#fff;font-size:.75rem;font-weight:700;margin-bottom:10px;">🎁 كود الهدية المجانية</span>
        <p style="color:rgba(255,255,255,.8);font-size:.85rem;margin:0 0 12px;">استخدمي كود الدولب أدناه للدوران وربح هدية مجانية مع طلبك!</p>
        <div style="background:rgba(0,0,0,.2);border-radius:12px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;border:1px dashed rgba(255,255,255,.25);">
            <span id="spinCodeDisplay" dir="ltr" style="font-family:'Courier New',monospace;font-size:1.4rem;font-weight:900;color:#fff;letter-spacing:3px;text-shadow:0 2px 8px rgba(0,0,0,.2);">{{ $spinCode->code }}</span>
            <button onclick="copySpinCode()" style="background:rgba(255,255,255,.15);border:none;color:#fff;padding:8px 16px;border-radius:8px;cursor:pointer;font-size:.8rem;font-weight:600;transition:all .3s;flex-shrink:0;" onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <i class="fas fa-copy"></i> نسخ
            </button>
        </div>
    </div>
</div>

<script>
function copySpinCode() {
    const code = document.getElementById('spinCodeDisplay').textContent.trim();
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.currentTarget;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
</script>
@endif

{{-- Wheel of Fortune Modal --}}
<div id="wheelModalOverlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:16px;">
    <div id="wheelModalCard" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:32px;border:1px solid rgba(255,255,255,.08);width:100%;max-width:480px;padding:32px 24px;text-align:center;position:relative;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.5);transform:scale(0.9);opacity:0;transition:all .5s cubic-bezier(.34,1.56,.64,1);">
        {{-- Premium accent line --}}
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--brand-500),#FFD700,var(--brand-500),transparent);"></div>

        {{-- Close button --}}
        <button onclick="closeWheelModal(false)" style="position:absolute;top:12px;left:12px;width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.5);font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .3s;z-index:5;" onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='rgba(255,255,255,.05)'">&times;</button>

        {{-- Header --}}
        <div style="margin-bottom:8px;">
            <span style="display:inline-block;padding:4px 14px;border-radius:50px;background:rgba(255,215,0,.12);color:#FFD700;font-size:.75rem;font-weight:700;letter-spacing:0.5px;">هدية مجانية 🎁</span>
        </div>
        <h2 style="color:#fff;font-size:1.5rem;font-weight:800;margin:0 0 4px;">تهانينا على طلبك! 🎉</h2>
        <p style="color:rgba(255,255,255,.5);font-size:.85rem;margin:0 0 20px;">دوري العجلة واربح هدية مجانية مع طلبك</p>

        {{-- Wheel container --}}
        <div id="wheelBox" style="position:relative;width:280px;height:280px;margin:0 auto 20px;">
            {{-- Pointer --}}
            <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);z-index:10;filter:drop-shadow(0 4px 6px rgba(0,0,0,.4));">
                <svg width="32" height="28" viewBox="0 0 32 28"><polygon points="16,28 0,0 32,0" fill="var(--brand-500)"/><polygon points="16,24 4,4 28,4" fill="rgba(255,255,255,.3)"/></svg>
            </div>
            {{-- Canvas wheel --}}
            <canvas id="wheelCanvas" width="560" height="560" style="width:100%;height:100%;border-radius:50%;box-shadow:0 0 0 4px rgba(255,255,255,.08),0 12px 40px rgba(0,0,0,.4);"></canvas>
            {{-- Center hub --}}
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#fff,#e0e0e0);border:3px solid var(--brand-500);box-shadow:0 2px 12px rgba(0,0,0,.3);z-index:5;display:flex;align-items:center;justify-content:center;">
                <span style="color:var(--brand-500);font-size:1.2rem;">🎁</span>
            </div>
        </div>

        {{-- Pre-spin content --}}
        <div id="preSpinSection">
            <button id="spinButton" onclick="spinWheel()" style="background:linear-gradient(135deg,var(--brand-500),#c0266b);color:#fff;border:none;padding:14px 40px;border-radius:50px;font-size:1rem;font-weight:800;cursor:pointer;transition:all .3s;box-shadow:0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.4);width:100%;max-width:280px;" onmouseover="this.style.transform='scale(1.03)';this.style.boxShadow='0 6px 30px rgba(var(--brand-500-rgb,255,42,133),.5)'" onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.4)'">
                <i class="fas fa-sync-alt" style="margin-left:8px;"></i> لف العجلة
            </button>
        </div>

        {{-- Post-spin content --}}
        <div id="postSpinSection" style="display:none;">
            <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(16,185,129,.05));border:2px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.8rem;">🎉</div>
            <h3 style="color:rgba(255,255,255,.6);font-size:.9rem;font-weight:600;margin:0 0 4px;">لقد ربحتِ:</h3>
            <p id="wonGiftDisplay" style="color:#fff;font-size:1.3rem;font-weight:800;margin:0 0 6px;"></p>
            <p id="wonGiftDesc" style="color:rgba(255,255,255,.4);font-size:.8rem;margin:0 0 20px;"></p>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <button onclick="closeWheelModal(true)" style="background:linear-gradient(135deg,var(--brand-500),#c0266b);color:#fff;border:none;padding:12px 28px;border-radius:50px;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .3s;box-shadow:0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.3);" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-gift" style="margin-left:6px;"></i> استلام الهدية
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <p style="color:rgba(255,255,255,.2);font-size:.7rem;margin-top:16px;">الهدية تخضع للشروط والأحكام · يمكنكِ الرفع مرّة واحدة فقط</p>
    </div>
</div>

<script>
(function() {
    const gifts = @json($wheelGifts);
    const giftDescriptions = [
        'لعناية فائقة بالبشرة الجافة',
        'لترطيب عميق وانتعاش فوري',
        'لعيون أكثر إشراقاً وشباباً',
        'لتفردي برائحة جذابة',
        'عناية طبيعية بشفتيك',
        'خصم حصري على مشترياتك القادمة',
        'لتجديد خلايا البشرة بلطف',
        'شحن مجاني لطلبك القادم دون حد أدنى',
    ];
    const colors = [
        '#FF6B6B', '#4ECDC4', '#FFD93D', '#6BCB77',
        '#9B59B6', '#E8A87C', '#3498DB', '#F06292'
    ];

    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');
    const size = canvas.width;
    const center = size / 2;
    const radius = center - 12;
    const sliceAngle = (2 * Math.PI) / gifts.length;

    let currentAngle = 0;
    let isSpinning = false;
    let velocity = 0;
    let spinCodeUsed = false;
    const spinCode = @json($spinCode->code ?? null);

    function drawWheel(angle) {
        ctx.clearRect(0, 0, size, size);
        for (let i = 0; i < gifts.length; i++) {
            const start = angle + i * sliceAngle;
            const end = start + sliceAngle;
            ctx.beginPath();
            ctx.moveTo(center, center);
            ctx.arc(center, center, radius, start, end);
            ctx.closePath();
            ctx.fillStyle = colors[i];
            ctx.fill();
            ctx.strokeStyle = 'rgba(255,255,255,.15)';
            ctx.lineWidth = 2;
            ctx.stroke();

            ctx.save();
            ctx.translate(center, center);
            ctx.rotate(start + sliceAngle / 2);
            ctx.textAlign = 'right';
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 13px "Tajawal", sans-serif';
            ctx.shadowColor = 'rgba(0,0,0,.3)';
            ctx.shadowBlur = 4;
            ctx.fillText(gifts[i], radius - 16, 5);
            ctx.restore();
        }
        ctx.beginPath();
        ctx.arc(center, center, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255,255,255,.15)';
        ctx.lineWidth = 4;
        ctx.stroke();
    }

    function spinWheel() {
        if (isSpinning) return;
        if (spinCodeUsed) {
            alert('لقد استخدمت الكود بالفعل!');
            return;
        }

        isSpinning = true;
        const btn = document.getElementById('spinButton');
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-left:8px;"></i> جاري الدوران...';

        velocity = 0.3 + Math.random() * 0.25;

        function animate() {
            if (velocity > 0.002) {
                currentAngle += velocity;
                velocity *= 0.985;
                drawWheel(currentAngle);
                requestAnimationFrame(animate);
            } else {
                isSpinning = false;
                velocity = 0;
                determineWinner();
            }
        }
        animate();
    }

    function determineWinner() {
        const top = 3 * Math.PI / 2;
        let norm = currentAngle % (2 * Math.PI);
        if (norm < 0) norm += 2 * Math.PI;
        let fromStart = (top - norm) % (2 * Math.PI);
        if (fromStart < 0) fromStart += 2 * Math.PI;
        const idx = Math.floor(fromStart / sliceAngle);
        const won = gifts[idx];

        document.getElementById('wonGiftDisplay').textContent = won;
        document.getElementById('wonGiftDesc').textContent = giftDescriptions[idx] || '';
        document.getElementById('preSpinSection').style.display = 'none';
        document.getElementById('postSpinSection').style.display = 'block';

        launchConfetti();

        // Mark spin code as used via API
        if (spinCode) {
            spinCodeUsed = true;
            fetch('{{ url("api/spin-codes/mark-used") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ code: spinCode, gift: won })
            }).catch(() => {});
        }
    }

    function launchConfetti() {
        const clrs = ['#FF6B6B','#4ECDC4','#FFD93D','#6BCB77','#FFB6C1','#FFD700','var(--brand-500)','#9B59B6'];
        for (let i = 0; i < 60; i++) {
            const el = document.createElement('div');
            el.style.cssText = `
                position:fixed;width:${6 + Math.random()*8}px;height:${6 + Math.random()*8}px;
                background:${clrs[Math.floor(Math.random()*clrs.length)]};
                left:${Math.random()*100}vw;top:-10px;z-index:10000;
                border-radius:${Math.random()>.5?'50%':'2px'};
                pointer-events:none;
            `;
            document.body.appendChild(el);
            const dur = 2000 + Math.random() * 2000;
            el.animate([
                { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                { transform: `translateY(${window.innerHeight + 50}px) rotate(${720 + Math.random()*720}deg)`, opacity: 0 }
            ], { duration: dur, easing: 'cubic-bezier(.25,.46,.45,.94)', fill: 'forwards' });
            setTimeout(() => el.remove(), dur);
        }
    }

    function openWheelModal() {
        document.getElementById('preSpinSection').style.display = 'block';
        document.getElementById('postSpinSection').style.display = 'none';
        const overlay = document.getElementById('wheelModalOverlay');
        const card = document.getElementById('wheelModalCard');
        overlay.style.display = 'flex';
        overlay.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        card.style.opacity = '0';
        setTimeout(() => {
            overlay.style.opacity = '1';
            card.style.transform = 'scale(1)';
            card.style.opacity = '1';
        }, 50);
    }

    window.closeWheelModal = function(claimed) {
        const overlay = document.getElementById('wheelModalOverlay');
        const card = document.getElementById('wheelModalCard');
        card.style.transform = 'scale(0.9)';
        card.style.opacity = '0';
        overlay.style.opacity = '0';
        setTimeout(() => { overlay.style.display = 'none'; }, 400);
        if (claimed && typeof showNotification === 'function') {
            showNotification('success', 'تهانينا! تم إضافة هديتك إلى طلبك 🎉');
        }
    };

    window.spinWheel = spinWheel;

    // Initialize
    drawWheel(currentAngle);
    setTimeout(openWheelModal, 1200);
})();
</script>
@endsection
