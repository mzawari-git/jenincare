@php
    $scGifts = [
        'سيروم هيالورونيك أسيد',
        'ماسك ترطيب عميق',
        'كريم عين مجدد',
        'عينة عطر فاخر',
        'مرطب شفاه عضوي',
        'خصم 15% على الطلب القادم',
        'مقشر لطيف للوجه',
        'توصيل مجاني للطلب القادم',
    ];
    $scGiftDescs = [
        'لعناية فائقة بالبشرة الجافة',
        'لترطيب عميق وانتعاش فوري',
        'لعيون أكثر إشراقاً وشباباً',
        'لتفردي برائحة جذابة',
        'عناية طبيعية بشفتيك',
        'خصم حصري على مشترياتك القادمة',
        'لتجديد خلايا البشرة بلطف',
        'شحن مجاني لطلبك القادم دون حد أدنى',
    ];
    $scColors = ['#FF6B6B','#4ECDC4','#FFD93D','#6BCB77','#9B59B6','#E8A87C','#3498DB','#F06292'];
@endphp

{{-- Floating button --}}
<button id="spinCodeFloatingBtn"
    style="position:fixed;bottom:90px;left:20px;z-index:9998;width:56px;height:56px;border-radius:50%;border:none;background:linear-gradient(135deg,var(--brand-500),#c0266b);color:#fff;font-size:1.5rem;cursor:pointer;box-shadow:0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.4);transition:all .3s;display:flex;align-items:center;justify-content:center;"
    onclick="openSpinCodeEntry()"
    title="لديك كود دولب؟ أدخله هنا"
    onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 30px rgba(var(--brand-500-rgb,255,42,133),.5)'"
    onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.4)'"
    aria-label="فتح نافذة إدخال كود الدولب">
    🎡
</button>

{{-- Spin Code Entry Modal + Wheel --}}
<div id="scEntryOverlay" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:16px;">
    <div id="scEntryCard" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:32px;border:1px solid rgba(255,255,255,.08);width:100%;max-width:480px;padding:32px 24px;text-align:center;position:relative;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.5);transform:scale(0.9);opacity:0;transition:all .5s cubic-bezier(.34,1.56,.64,1);">
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--brand-500),#FFD700,var(--brand-500),transparent);"></div>

        <button onclick="closeScEntry()" style="position:absolute;top:12px;left:12px;width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.5);font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .3s;z-index:5;" onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='rgba(255,255,255,.05)'">&times;</button>

        {{-- Entry Step --}}
        <div id="scEntryStep">
            <div style="margin-bottom:8px;">
                <span style="display:inline-block;padding:4px 14px;border-radius:50px;background:rgba(255,215,0,.12);color:#FFD700;font-size:.75rem;font-weight:700;">🎁 هدية مجانية</span>
            </div>
            <h2 style="color:#fff;font-size:1.3rem;font-weight:800;margin:0 0 4px;">لديك كود دولب؟</h2>
            <p style="color:rgba(255,255,255,.5);font-size:.85rem;margin:0 0 20px;">أدخل الكود الذي حصلت عليه من المتجر لتفعيل عجلة الحظ</p>

            <div style="position:relative;max-width:320px;margin:0 auto 16px;">
                <input id="scCodeInput" type="text" dir="ltr" placeholder="أدخل الكود (مثال: JEN-A3B8C9K2)"
                    style="width:100%;padding:14px 18px;border-radius:14px;border:2px solid rgba(255,255,255,.1);background:rgba(255,255,255,.06);color:#fff;font-size:1.1rem;font-weight:700;font-family:'Courier New',monospace;text-align:center;letter-spacing:3px;outline:none;box-sizing:border-box;transition:border-color .3s;"
                    onfocus="this.style.borderColor='var(--brand-500)'" onblur="this.style.borderColor='rgba(255,255,255,.1)'"
                    oninput="this.value=this.value.toUpperCase()"
                    autocomplete="off" />
            </div>
            <p id="scError" style="color:#ef4444;font-size:.8rem;margin:0 0 12px;display:none;"></p>
            <p id="scSuccess" style="color:#10b981;font-size:.8rem;margin:0 0 12px;display:none;"></p>

            <button id="scValidateBtn" onclick="validateScCode()"
                style="background:linear-gradient(135deg,var(--brand-500),#c0266b);color:#fff;border:none;padding:14px 40px;border-radius:50px;font-size:1rem;font-weight:800;cursor:pointer;transition:all .3s;box-shadow:0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.4);width:100%;max-width:280px;"
                onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                <i class="fas fa-gift" style="margin-left:8px;"></i> تفعيل الدولب
            </button>
        </div>

        {{-- Wheel Step --}}
        <div id="scWheelStep" style="display:none;">
            <div style="margin-bottom:8px;">
                <span style="display:inline-block;padding:4px 14px;border-radius:50px;background:rgba(255,215,0,.12);color:#FFD700;font-size:.75rem;font-weight:700;">🎡 عجلة الحظ</span>
            </div>
            <h2 style="color:#fff;font-size:1.3rem;font-weight:800;margin:0 0 4px;">تم تفعيل الكود! 🎉</h2>
            <p style="color:rgba(255,255,255,.5);font-size:.85rem;margin:0 0 20px;">دوري العجلة واربح هدية مجانية</p>

            <div id="scWheelBox" style="position:relative;width:260px;height:260px;margin:0 auto 16px;">
                <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);z-index:10;filter:drop-shadow(0 4px 6px rgba(0,0,0,.4));">
                    <svg width="32" height="28" viewBox="0 0 32 28"><polygon points="16,28 0,0 32,0" fill="var(--brand-500)"/><polygon points="16,24 4,4 28,4" fill="rgba(255,255,255,.3)"/></svg>
                </div>
                <canvas id="scCanvas" width="520" height="520" style="width:100%;height:100%;border-radius:50%;box-shadow:0 0 0 4px rgba(255,255,255,.08),0 12px 40px rgba(0,0,0,.4);"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#fff,#e0e0e0);border:3px solid var(--brand-500);box-shadow:0 2px 12px rgba(0,0,0,.3);z-index:5;display:flex;align-items:center;justify-content:center;">
                    <span style="color:var(--brand-500);font-size:1.1rem;">🎁</span>
                </div>
            </div>

            <div id="scPreSpin">
                <button id="scSpinBtn" onclick="spinScWheel()"
                    style="background:linear-gradient(135deg,var(--brand-500),#c0266b);color:#fff;border:none;padding:12px 36px;border-radius:50px;font-size:.95rem;font-weight:800;cursor:pointer;transition:all .3s;box-shadow:0 4px 20px rgba(var(--brand-500-rgb,255,42,133),.4);width:100%;max-width:260px;"
                    onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-sync-alt" style="margin-left:8px;"></i> لف العجلة
                </button>
            </div>

            <div id="scPostSpin" style="display:none;">
                <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(16,185,129,.05));border:2px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:1.6rem;">🎉</div>
                <h3 style="color:rgba(255,255,255,.6);font-size:.85rem;font-weight:600;margin:0 0 4px;">لقد ربحتِ:</h3>
                <p id="scWonGift" style="color:#fff;font-size:1.2rem;font-weight:800;margin:0 0 4px;"></p>
                <p id="scWonDesc" style="color:rgba(255,255,255,.4);font-size:.8rem;margin:0 0 16px;"></p>
                <button onclick="closeScEntry()"
                    style="background:linear-gradient(135deg,var(--brand-500),#c0266b);color:#fff;border:none;padding:10px 24px;border-radius:50px;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .3s;"
                    onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-check" style="margin-left:6px;"></i> تم
                </button>
            </div>

            <p style="color:rgba(255,255,255,.2);font-size:.65rem;margin-top:12px;">الهدية تخضع للشروط والأحكام · يمكنك الاستخدام مرّة واحدة فقط</p>
        </div>
    </div>
</div>

<script>
(function() {
    const gifts = @json($scGifts);
    const giftDescs = @json($scGiftDescs);
    const colors = @json($scColors);
    const sliceAngle = (2 * Math.PI) / gifts.length;
    let scCurrentCode = null;
    let scAngle = 0;
    let scSpinning = false;
    let scVelocity = 0;
    let scUsed = false;
    let canvas, ctx, size, center, radius;

    function initScCanvas() {
        canvas = document.getElementById('scCanvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');
        size = canvas.width;
        center = size / 2;
        radius = center - 12;
        drawScWheel(0);
    }

    function drawScWheel(angle) {
        if (!ctx) return;
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
            ctx.font = 'bold 12px "Tajawal", sans-serif';
            ctx.shadowColor = 'rgba(0,0,0,.3)';
            ctx.shadowBlur = 4;
            ctx.fillText(gifts[i], radius - 14, 4);
            ctx.restore();
        }
        ctx.beginPath();
        ctx.arc(center, center, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255,255,255,.15)';
        ctx.lineWidth = 4;
        ctx.stroke();
    }

    window.openSpinCodeEntry = function() {
        if (scUsed) { alert('لقد استخدمت الكود بالفعل!'); return; }
        document.getElementById('scEntryStep').style.display = 'block';
        document.getElementById('scWheelStep').style.display = 'none';
        document.getElementById('scError').style.display = 'none';
        document.getElementById('scSuccess').style.display = 'none';
        document.getElementById('scCodeInput').value = '';
        const overlay = document.getElementById('scEntryOverlay');
        const card = document.getElementById('scEntryCard');
        overlay.style.display = 'flex';
        overlay.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        card.style.opacity = '0';
        setTimeout(() => {
            overlay.style.opacity = '1';
            card.style.transform = 'scale(1)';
            card.style.opacity = '1';
        }, 50);
    };

    window.closeScEntry = function() {
        const overlay = document.getElementById('scEntryOverlay');
        const card = document.getElementById('scEntryCard');
        card.style.transform = 'scale(0.9)';
        card.style.opacity = '0';
        overlay.style.opacity = '0';
        setTimeout(() => { overlay.style.display = 'none'; }, 400);
    };

    window.validateScCode = function() {
        const code = document.getElementById('scCodeInput').value.trim().toUpperCase();
        const errEl = document.getElementById('scError');
        const sucEl = document.getElementById('scSuccess');
        const btn = document.getElementById('scValidateBtn');

        if (!code) {
            errEl.textContent = 'الرجاء إدخال الكود';
            errEl.style.display = 'block';
            sucEl.style.display = 'none';
            return;
        }

        errEl.style.display = 'none';
        sucEl.style.display = 'none';
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحقق...';

        fetch('{{ url("api/spin-codes/validate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ code })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                sucEl.textContent = '✅ تم تفعيل الكود بنجاح!';
                sucEl.style.display = 'block';
                scCurrentCode = code;
                setTimeout(showScWheel, 800);
            } else {
                errEl.textContent = res.message || 'كود غير صالح';
                errEl.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-gift" style="margin-left:8px;"></i> تفعيل الدولب';
            }
        })
        .catch(() => {
            errEl.textContent = 'حدث خطأ في الاتصال';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-gift" style="margin-left:8px;"></i> تفعيل الدولب';
        });
    };

    function showScWheel() {
        document.getElementById('scEntryStep').style.display = 'none';
        document.getElementById('scWheelStep').style.display = 'block';
        document.getElementById('scPreSpin').style.display = 'block';
        document.getElementById('scPostSpin').style.display = 'none';
        setTimeout(() => { initScCanvas(); }, 100);
    }

    window.spinScWheel = function() {
        if (scSpinning || scUsed) return;
        scSpinning = true;
        const btn = document.getElementById('scSpinBtn');
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-left:8px;"></i> جاري الدوران...';

        scVelocity = 0.3 + Math.random() * 0.25;

        function animate() {
            if (scVelocity > 0.002) {
                scAngle += scVelocity;
                scVelocity *= 0.985;
                drawScWheel(scAngle);
                requestAnimationFrame(animate);
            } else {
                scSpinning = false;
                scVelocity = 0;
                determineScWinner();
            }
        }
        animate();
    };

    function determineScWinner() {
        const top = 3 * Math.PI / 2;
        let norm = scAngle % (2 * Math.PI);
        if (norm < 0) norm += 2 * Math.PI;
        let fromStart = (top - norm) % (2 * Math.PI);
        if (fromStart < 0) fromStart += 2 * Math.PI;
        const idx = Math.floor(fromStart / sliceAngle);
        const won = gifts[idx] || 'هدية مفاجئة';

        document.getElementById('scWonGift').textContent = won;
        document.getElementById('scWonDesc').textContent = giftDescs[idx] || '';
        document.getElementById('scPreSpin').style.display = 'none';
        document.getElementById('scPostSpin').style.display = 'block';

        scUsed = true;
        launchScConfetti();

        if (scCurrentCode) {
            fetch('{{ url("api/spin-codes/mark-used") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ code: scCurrentCode, gift: won })
            }).catch(() => {});
        }
    }

    function launchScConfetti() {
        const clrs = ['#FF6B6B','#4ECDC4','#FFD93D','#6BCB77','#FFB6C1','#FFD700','var(--brand-500)','#9B59B6'];
        for (let i = 0; i < 50; i++) {
            const el = document.createElement('div');
            el.style.cssText = `position:fixed;width:${6+Math.random()*8}px;height:${6+Math.random()*8}px;background:${clrs[Math.floor(Math.random()*clrs.length)]};left:${Math.random()*100}vw;top:-10px;z-index:10000;border-radius:${Math.random()>.5?'50%':'2px'};pointer-events:none;`;
            document.body.appendChild(el);
            const dur = 2000 + Math.random() * 2000;
            el.animate([
                { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                { transform: `translateY(${window.innerHeight+50}px) rotate(${720+Math.random()*720}deg)`, opacity: 0 }
            ], { duration: dur, easing: 'cubic-bezier(.25,.46,.45,.94)', fill: 'forwards' });
            setTimeout(() => el.remove(), dur);
        }
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { initScCanvas(); });
    } else {
        setTimeout(initScCanvas, 500);
    }
})();
</script>
