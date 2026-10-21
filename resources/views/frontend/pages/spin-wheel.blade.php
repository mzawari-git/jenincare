<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>دولاب الحظ - {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Tajawal',sans-serif;background:#0a0a0f;min-height:100vh;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  overflow-x:hidden;position:relative;
}
body::before{
  content:'';position:fixed;inset:0;
  background:radial-gradient(ellipse 80% 50% at 50% -10%,rgba(255,215,0,0.08) 0%,transparent 70%),
             radial-gradient(ellipse 60% 40% at 30% 100%,rgba(255,215,0,0.05) 0%,transparent 60%),
             radial-gradient(ellipse 40% 30% at 70% 80%,rgba(255,200,50,0.04) 0%,transparent 50%);
  pointer-events:none;z-index:0;
}
.sparkle{
  position:fixed;width:3px;height:3px;background:#ffd700;border-radius:50%;
  pointer-events:none;animation:sparkleMove 4s linear infinite;opacity:0;z-index:0;
}
@keyframes sparkleMove{
  0%{opacity:0;transform:translateY(0) scale(0)}
  20%{opacity:.8;transform:translateY(-30px) scale(1)}
  80%{opacity:.6;transform:translateY(-60px) scale(.5)}
  100%{opacity:0;transform:translateY(-80px) scale(0)}
}
.container{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:18px;padding:20px;width:100%;max-width:900px}

/* === HEADER === */
.header{text-align:center;position:relative}
.header h1{
  font-size:2.2rem;font-weight:900;
  background:linear-gradient(135deg,#ffd700,#ffed4a,#ffd700);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  letter-spacing:1px;
}
.header p{color:#888;font-size:.95rem;margin-top:2px}

/* === WHEEL === */
.wheel-wrapper{position:relative}
.wheel-outer{
  width:560px;height:560px;border-radius:50%;padding:20px;
  background:conic-gradient(from 0deg,#1a1a2e,#16213e,#1a1a2e,#0f3460,#1a1a2e,#16213e,#1a1a2e,#0f3460,
    #1a1a2e,#16213e,#1a1a2e,#0f3460,#1a1a2e,#16213e,#1a1a2e,#0f3460);
  box-shadow:0 0 50px rgba(255,215,0,0.15),0 0 100px rgba(255,215,0,0.08),inset 0 0 30px rgba(0,0,0,.3);
  position:relative;
}
.wheel-border{
  width:100%;height:100%;border-radius:50%;padding:7px;
  background:conic-gradient(from 0deg,#ffd700,#ffed4a,#ffd700,#b8860b,#ffd700,#ffed4a,#ffd700,#b8860b,
    #ffd700,#ffed4a,#ffd700,#b8860b,#ffd700,#ffed4a,#ffd700,#b8860b);
}
.wheel-inner{width:100%;height:100%;border-radius:50%;position:relative;overflow:hidden;background:#1a1a2e}
#wheelCanvas{width:100%;height:100%;display:block}
.pointer{
  position:absolute;top:-22px;left:50%;transform:translateX(-50%);z-index:10;
  filter:drop-shadow(0 0 15px rgba(255,215,0,.6));
}
.pointer svg{width:60px;height:60px}
.center-dot{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  width:78px;height:78px;border-radius:50%;
  background:radial-gradient(circle at 35% 35%,#ffd700,#b8860b);
  box-shadow:0 0 25px rgba(255,215,0,.35);z-index:5;cursor:pointer;
  transition:transform .2s;border:4px solid #fff;
  display:flex;align-items:center;justify-content:center;
}
.center-dot:hover{transform:translate(-50%,-50%) scale(1.06)}
.center-dot i{color:#fff;font-size:1.8rem;filter:drop-shadow(0 1px 3px rgba(0,0,0,.4))}

@media(max-width:620px){
  .wheel-outer{width:360px;height:360px;padding:14px}
  .wheel-border{padding:5px}
  .pointer{top:-14px}.pointer svg{width:40px;height:40px}
  .center-dot{width:55px;height:55px}.center-dot i{font-size:1.3rem}
  .header h1{font-size:1.5rem}
}
@media(max-width:400px){
  .wheel-outer{width:300px;height:300px;padding:10px}
  .center-dot{width:45px;height:45px}.center-dot i{font-size:1rem}
}

/* === CONTROLS === */
.controls{display:flex;align-items:center;gap:14px;flex-wrap:wrap;justify-content:center}
.spin-btn{
  padding:16px 56px;font-size:1.2rem;font-weight:800;font-family:'Tajawal',sans-serif;
  border:none;border-radius:50px;
  background:linear-gradient(135deg,#ffd700,#ffed4a);color:#1a1a2e;
  cursor:pointer;transition:all .3s;
  box-shadow:0 4px 25px rgba(255,215,0,.3);letter-spacing:.5px;
}
.spin-btn:hover{transform:translateY(-2px);box-shadow:0 6px 35px rgba(255,215,0,.4)}
.spin-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.spin-btn i{margin-left:10px}

.settings-btn{
  width:50px;height:50px;border-radius:50%;border:2px solid rgba(255,215,0,.25);
  background:rgba(255,215,0,.06);color:#ffd700;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition:all .3s;font-size:1.3rem;
}
.settings-btn:hover{background:rgba(255,215,0,.15);border-color:rgba(255,215,0,.5);transform:rotate(60deg)}

/* === WINNER MODAL === */
.modal-overlay{
  display:none;position:fixed;inset:0;z-index:1000;
  background:rgba(0,0,0,.75);backdrop-filter:blur(10px);
  align-items:center;justify-content:center;
}
.modal-overlay.show{display:flex}
.modal-content{
  background:linear-gradient(145deg,#1a1a2e,#16213e);
  border:1px solid rgba(255,215,0,.2);border-radius:24px;
  padding:44px 40px 32px;max-width:440px;width:90%;text-align:center;
  position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5),0 0 40px rgba(255,215,0,.05);
  animation:modalIn .4s ease;
}
@keyframes modalIn{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-confetti{font-size:3.5rem;margin-bottom:6px}
.modal-title{color:#ffd700;font-size:1.4rem;font-weight:800;margin-bottom:2px}
.modal-prize-name{color:#fff;font-size:2rem;font-weight:900;margin:8px 0}
.modal-prize-image{
  width:130px;height:130px;border-radius:50%;object-fit:cover;
  border:4px solid #ffd700;margin:10px auto;box-shadow:0 0 35px rgba(255,215,0,.2);
}
.modal-prize-placeholder{
  width:130px;height:130px;border-radius:50%;margin:10px auto;
  display:flex;align-items:center;justify-content:center;font-size:3.5rem;
  border:4px solid rgba(255,215,0,.3);
}
.modal-prize-sub{color:#888;font-size:.9rem;margin-bottom:4px}
.modal-prize-code{
  background:rgba(255,215,0,.1);border:1px dashed rgba(255,215,0,.3);
  border-radius:12px;padding:10px 20px;margin:12px auto;max-width:240px;
  font-size:1.3rem;font-weight:800;color:#ffd700;direction:ltr;
}
.modal-close-btn{
  padding:12px 40px;font-size:1rem;font-weight:700;font-family:'Tajawal',sans-serif;
  border:2px solid #ffd700;border-radius:50px;background:transparent;
  color:#ffd700;cursor:pointer;transition:all .3s;margin-top:10px;
}
.modal-close-btn:hover{background:#ffd700;color:#1a1a2e}
.powered-by{margin-top:25px;color:rgba(255,255,255,.12);font-size:.75rem}
.powered-by a{color:rgba(255,215,0,.3);text-decoration:none}

/* === SETTINGS PANEL === */
.settings-panel{
  position:fixed;top:0;left:-420px;width:400px;height:100%;z-index:2000;
  background:linear-gradient(180deg,#12121a,#0d0d14);
  border-left:1px solid rgba(255,215,0,.12);
  box-shadow:-10px 0 40px rgba(0,0,0,.6);
  transition:left .35s cubic-bezier(.4,0,.2,1);
  overflow-y:auto;padding:20px;direction:rtl;
}
.settings-panel.open{left:0}
.settings-overlay{
  display:none;position:fixed;inset:0;z-index:1999;
  background:rgba(0,0,0,.5);backdrop-filter:blur(4px);
}
.settings-overlay.show{display:block}
.settings-panel::-webkit-scrollbar{width:4px}
.settings-panel::-webkit-scrollbar-thumb{background:rgba(255,215,0,.3);border-radius:4px}
.settings-header{
  display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:14px;
  border-bottom:1px solid rgba(255,215,0,.1);
}
.settings-header h2{color:#ffd700;font-size:1.3rem;font-weight:800}
.settings-close{
  width:36px;height:36px;border-radius:50%;border:1px solid rgba(255,215,0,.2);
  background:transparent;color:#ffd700;cursor:pointer;
  display:flex;align-items:center;justify-content:center;transition:all .2s;
}
.settings-close:hover{background:rgba(255,215,0,.1)}

.prize-card{
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
  border-radius:14px;padding:14px;margin-bottom:10px;
  display:flex;align-items:center;gap:12px;transition:all .2s;
}
.prize-card:hover{background:rgba(255,255,255,.06);border-color:rgba(255,215,0,.2)}
.prize-card .prize-dot{
  width:10px;height:10px;border-radius:50%;flex-shrink:0;
}
.prize-card .prize-info{flex:1;min-width:0}
.prize-card .prize-info .prize-name{color:#fff;font-weight:700;font-size:.9rem}
.prize-card .prize-info .prize-type{color:#888;font-size:.75rem}
.prize-card .prize-actions{display:flex;gap:6px}
.prize-card .prize-actions button{
  width:30px;height:30px;border-radius:8px;border:none;
  display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;font-size:.75rem;
}
.prize-card.inactive{opacity:.4}
.prize-card.inactive:hover{opacity:.7}
.btn-sm-edit{background:rgba(255,215,0,.12);color:#ffd700}
.btn-sm-edit:hover{background:rgba(255,215,0,.25)}
.btn-sm-del{background:rgba(239,68,68,.12);color:#ef4444}
.btn-sm-del:hover{background:rgba(239,68,68,.25)}
.btn-sm-toggle{background:rgba(255,255,255,.06);color:#888}
.btn-sm-toggle:hover{background:rgba(255,255,255,.12)}

.add-btn{
  width:100%;padding:12px;border-radius:12px;
  border:2px dashed rgba(255,215,0,.2);background:transparent;
  color:#ffd700;font-weight:700;font-size:.9rem;cursor:pointer;
  transition:all .2s;font-family:'Tajawal',sans-serif;margin-top:4px;
}
.add-btn:hover{background:rgba(255,215,0,.06);border-color:rgba(255,215,0,.4)}

/* Settings Form */
.s-form{display:none;background:rgba(255,255,255,.03);border-radius:14px;padding:18px;margin-bottom:12px;border:1px solid rgba(255,215,0,.1)}
.s-form.show{display:block}
.s-form label{display:block;color:#aaa;font-size:.8rem;font-weight:600;margin-bottom:4px}
.s-form input,.s-form select{
  width:100%;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.04);color:#fff;font-size:.9rem;
  font-family:'Tajawal',sans-serif;transition:border-color .2s;margin-bottom:12px;
}
.s-form input:focus,.s-form select:focus{outline:none;border-color:#ffd700}
.s-form input[type=color]{height:44px;padding:4px;cursor:pointer}
.s-form input[type=file]{padding:8px;cursor:pointer}
.s-form .s-row{display:flex;gap:12px}
.s-form .s-row > div{flex:1}
.s-form .s-actions{display:flex;gap:10px;margin-top:4px}
.s-form .s-actions button{
  flex:1;padding:10px;border-radius:10px;font-weight:700;font-size:.85rem;
  font-family:'Tajawal',sans-serif;cursor:pointer;transition:all .2s;border:none;
}
.s-btn-save{background:linear-gradient(135deg,#ffd700,#ffed4a);color:#1a1a2e}
.s-btn-save:hover{opacity:.9}
.s-btn-cancel{background:rgba(255,255,255,.06);color:#888}
.s-btn-cancel:hover{background:rgba(255,255,255,.1)}

.s-hint{color:#666;font-size:.75rem;margin-top:-8px;margin-bottom:12px}
</style>
</head>
<body>

@for($i = 0; $i < 20; $i++)
<div class="sparkle" style="left:{{ rand(2,98) }}%;top:{{ rand(2,98) }}%;animation-delay:{{ rand(0,40)/10 }}s;animation-duration:{{ 3 + rand(0,30)/10 }}s;"></div>
@endfor

<div class="container">
  <div class="header">
    <h1>🎡 دولاب الحظ</h1>
    <p>اديري الدولاب واربحی جوائز وخصومات حصرية!</p>
  </div>

  <div class="wheel-wrapper">
    <div class="wheel-outer">
      <div class="wheel-border">
        <div class="wheel-inner">
          <div class="pointer">
            <svg viewBox="0 0 60 60"><polygon points="30,55 6,6 54,6" fill="#ffd700" stroke="#b8860b" stroke-width="2"/></svg>
          </div>
          <canvas id="wheelCanvas" width="1000" height="1000"></canvas>
          <div class="center-dot" onclick="spin()">
            <i class="fas fa-gem"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="controls">
    <button class="spin-btn" id="spinBtn" onclick="spin()"><i class="fas fa-play"></i> اديري الدولاب!</button>
    @auth
    <button class="settings-btn" onclick="toggleSettings()" title="إعدادات الدولاب"><i class="fas fa-cog"></i></button>
    @endauth
  </div>
</div>

<!-- Winner Modal -->
<div class="modal-overlay" id="winnerModal">
  <div class="modal-content">
    <div class="modal-confetti">🎉</div>
    <div class="modal-title">تهانينا!</div>
    <div id="modalPrizeImage"></div>
    <div class="modal-prize-name" id="modalPrizeName">-</div>
    <div class="modal-prize-sub" id="modalPrizeSub"></div>
    <div id="modalPrizeCode" style="display:none;" class="modal-prize-code"></div>
    <button class="modal-close-btn" onclick="closeModal()"><i class="fas fa-check"></i> رائع!</button>
  </div>
</div>

<!-- Settings Panel -->
<div class="settings-overlay" id="settingsOverlay" onclick="toggleSettings()"></div>
<div class="settings-panel" id="settingsPanel">
  <div class="settings-header">
    <h2><i class="fas fa-sliders-h"></i> إعدادات الدولاب</h2>
    <button class="settings-close" onclick="toggleSettings()"><i class="fas fa-times"></i></button>
  </div>

  <p style="color:#888;font-size:.85rem;margin-bottom:16px;">
    أضف أو عدّل العناصر. الحد الأقصى 15 عنصراً (منتجات + نسب خصم).
  </p>

  <div id="settingsList">
    @foreach($prizes as $p)
    <div class="prize-card {{ $p->is_active ? '' : 'inactive' }}" data-id="{{ $p->id }}">
      <div class="prize-dot" style="background:{{ $p->color }}"></div>
      <div class="prize-info">
        <div class="prize-name">{{ $p->type === 'discount' ? 'خصم '.$p->discount_percent.'%' : $p->name }}</div>
        <div class="prize-type">{{ $p->type === 'discount' ? 'نسبة خصم' : 'منتج' }}</div>
      </div>
      <div class="prize-actions">
        <button class="btn-sm-toggle" onclick="togglePrize({{ $p->id }})" title="{{ $p->is_active ? 'تعطيل' : 'تفعيل' }}">
          <i class="fas {{ $p->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
        </button>
        <button class="btn-sm-edit" onclick="editPrize({{ $p->id }})" title="تعديل"><i class="fas fa-pen"></i></button>
        <button class="btn-sm-del" onclick="deletePrize({{ $p->id }})" title="حذف"><i class="fas fa-trash"></i></button>
      </div>
    </div>
    @endforeach
  </div>

  <button class="add-btn" onclick="showAddForm()"><i class="fas fa-plus"></i> إضافة عنصر جديد</button>

  <!-- Add/Edit Form -->
  <div class="s-form" id="prizeForm">
    <input type="hidden" id="formId" value="">
    <div class="s-row">
      <div>
        <label>النوع</label>
        <select id="formType" onchange="toggleFormType()">
          <option value="product">منتج / جائزة</option>
          <option value="discount">نسبة خصم</option>
        </select>
      </div>
      <div>
        <label>اللون</label>
        <input type="color" id="formColor" value="#6366f1">
      </div>
    </div>

    <div id="formProductFields">
      <label>اسم المنتج / النص</label>
      <input type="text" id="formName" placeholder="مثال: عطر مسك وايت">

      <label>نص إضافي (اختياري)</label>
      <input type="text" id="formValue" placeholder="وصف قصير يظهر بالفوز">

      <label>صورة المنتج</label>
      <input type="file" id="formImage" accept="image/jpeg,image/png,image/webp">
      <div id="currentImage" style="display:none;margin-bottom:10px;">
        <img id="currentImageSrc" src="" style="width:60px;height:60px;border-radius:10px;object-fit:cover;border:2px solid rgba(255,215,0,.2);">
        <span style="color:#888;font-size:.75rem;margin-right:8px;">الصورة الحالية</span>
        <button type="button" onclick="removeImage()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.8rem;margin-right:6px;"><i class="fas fa-times"></i></button>
      </div>
      <div class="s-hint">يُفضل صورة مربعة. أقصى حجم 2MB.</div>
    </div>

    <div id="formDiscountFields" style="display:none;">
      <label>نسبة الخصم</label>
      <select id="formDiscount">
        <option value="5">5%</option>
        <option value="10">10%</option>
        <option value="15">15%</option>
        <option value="20" selected>20%</option>
        <option value="25">25%</option>
        <option value="30">30%</option>
      </select>
      <div class="s-hint">خصم على أي منتج من المتجر</div>
    </div>

    <div class="s-row" style="margin-top:4px;">
      <div>
        <label>نسبة الفوز (وزن)</label>
        <input type="number" id="formWeight" value="1" min="1" max="10000">
        <div class="s-hint">كلما زاد الرقم، زادت فرصة الفوز بهذه الجائزة</div>
      </div>
    </div>

    <div class="s-actions">
      <button class="s-btn-cancel" onclick="hideForm()">إلغاء</button>
      <button class="s-btn-save" onclick="savePrize()"><i class="fas fa-check"></i> حفظ</button>
    </div>
  </div>

  <div class="powered-by" style="margin-top:auto;padding-top:20px;text-align:center;">
    {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }} &copy; {{ date('Y') }}
  </div>
</div>

<script>
const prizes = @json($prizes);
const PI = Math.PI, TAU = PI * 2;
const segmentCount = Math.min(prizes.length, 15);
const segmentAngle = TAU / segmentCount;
let currentRotation = 0, totalSpinAngle = 0, isSpinning = false;

const canvas = document.getElementById('wheelCanvas');
const ctx = canvas.getContext('2d');
const cx = canvas.width / 2, cy = canvas.height / 2;
const radius = canvas.width / 2 - 12;

const defaultColors = ['#FF6B6B','#FECA57','#48DBFB','#FF9FF3','#54A0FF','#5F27CD','#FF6348','#2ED573','#A29BFE','#FD79A8','#00CEC9','#E17055'];

function drawWheel(rotation) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  if (segmentCount === 0) {
    ctx.fillStyle = '#1a1a2e'; ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#555'; ctx.font = 'bold 40px Tajawal, sans-serif';
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText('أضف عناصر للدولاب', cx, cy);
    return;
  }
  for (let i = 0; i < segmentCount; i++) {
    const startA = rotation + i * segmentAngle;
    const endA = startA + segmentAngle;
    const prize = prizes[i];
    const color = prize?.color || defaultColors[i % defaultColors.length];

    ctx.beginPath(); ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, radius, startA, endA); ctx.closePath();
    ctx.fillStyle = color; ctx.fill();
    ctx.strokeStyle = 'rgba(255,255,255,0.15)'; ctx.lineWidth = 2; ctx.stroke();

    const midA = startA + segmentAngle / 2;
    const isSmall = segmentCount > 8;
    const imgR = radius * (isSmall ? 0.50 : 0.55);
    const txtR = radius * (isSmall ? 0.82 : 0.84);
    const iconSize = isSmall ? 32 : 46;
    const badgeSize = isSmall ? 32 : 48;
    const badgeFont = isSmall ? 26 : 38;

    // Icon / image for products, % badge for discounts
    if (prize?.type === 'discount') {
      const dx = cx + Math.cos(midA) * imgR;
      const dy = cy + Math.sin(midA) * imgR;
      ctx.save();
      ctx.beginPath(); ctx.arc(dx, dy, badgeSize, 0, TAU); ctx.closePath();
      ctx.fillStyle = 'rgba(0,0,0,0.3)'; ctx.fill();
      ctx.fillStyle = '#fff';
      ctx.font = 'bold ' + badgeFont + 'px Tajawal, sans-serif';
      ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
      ctx.fillText(prize.discount_percent + '%', dx, dy);
      ctx.restore();
    } else if (prize?.image_url) {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.src = prize.image_url;
      if (img.complete && img.naturalWidth > 0) {
        const isz = isSmall ? 32 : 44;
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx + Math.cos(midA) * imgR, cy + Math.sin(midA) * imgR, isz, 0, TAU);
        ctx.closePath(); ctx.clip();
        ctx.drawImage(img, cx + Math.cos(midA) * imgR - isz, cy + Math.sin(midA) * imgR - isz, isz * 2, isz * 2);
        ctx.restore();
      } else {
        drawIcon(midA, imgR, '#fff', iconSize);
      }
    } else {
      drawIcon(midA, imgR, '#fff', iconSize);
    }

    // Segment label text
    const displayName = prize?.type === 'discount' ? 'خصم ' + prize.discount_percent + '%' : (prize?.name || 'جائزة');
    drawText(midA, txtR, displayName, '#fff', isSmall ? 0.65 : 0.78);
  }
  // Center overlay
  const grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, radius * 0.14);
  grad.addColorStop(0, 'rgba(26,26,46,0)');
  grad.addColorStop(1, 'rgba(26,26,46,0.95)');
  ctx.beginPath(); ctx.arc(cx, cy, radius * 0.14, 0, TAU);
  ctx.fillStyle = grad; ctx.fill();
  ctx.beginPath(); ctx.arc(cx, cy, radius, 0, TAU);
  ctx.strokeStyle = 'rgba(255,215,0,0.12)'; ctx.lineWidth = 3; ctx.stroke();
}

function drawIcon(angle, r, color, size) {
  const x = cx + Math.cos(angle) * r;
  const y = cy + Math.sin(angle) * r;
  ctx.save();
  const sr = size * 0.5 + 4;
  ctx.beginPath(); ctx.arc(x, y, sr, 0, TAU); ctx.closePath();
  ctx.fillStyle = 'rgba(0,0,0,0.25)'; ctx.fill();
  ctx.fillStyle = color || '#fff';
  ctx.font = (size * 0.75) + 'px Tajawal, sans-serif';
  ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
  ctx.fillText('🎁', x, y + 1);
  ctx.restore();
}

function drawText(angle, r, text, color, s) {
  const x = cx + Math.cos(angle) * r;
  const y = cy + Math.sin(angle) * r;
  ctx.save();
  ctx.translate(x, y); ctx.rotate(angle + PI / 2);
  const fs = Math.min(32, Math.max(16, (radius * 0.12) * s));
  ctx.font = 'bold ' + fs + 'px Tajawal, sans-serif';
  ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
  let t = text || 'جائزة'; if (t.length > 14) t = t.substring(0, 13) + '…';
  ctx.fillStyle = 'rgba(0,0,0,0.5)'; ctx.fillText(t, 1, 1);
  ctx.fillStyle = color || '#fff'; ctx.fillText(t, 0, 0);
  ctx.restore();
}

function getWinnerIndex(rotation) {
  const norm = ((rotation % TAU) + TAU) % TAU;
  const pointerAngle = (PI * 1.5) % TAU;
  return Math.floor(((pointerAngle - norm + TAU) % TAU) / segmentAngle) % segmentCount;
}

function pickWeightedWinner() {
  const totalWeight = prizes.reduce((sum, p) => sum + (p.weight || 1), 0);
  let rand = Math.random() * totalWeight;
  for (let i = 0; i < segmentCount; i++) {
    rand -= (prizes[i].weight || 1);
    if (rand <= 0) return i;
  }
  return segmentCount - 1;
}

function spin() {
  if (isSpinning || segmentCount === 0) return;
  isSpinning = true;
  document.getElementById('spinBtn').disabled = true;

  const winnerIdx = pickWeightedWinner();
  const extraSpins = 5 + Math.floor(Math.random() * 5);
  const pointerAngle = (PI * 1.5) % TAU;
  const segCenter = winnerIdx * segmentAngle + segmentAngle / 2;
  const targetAngle = (pointerAngle - segCenter + TAU) % TAU;
  const spinFrom = currentRotation;
  const spinTo = currentRotation + extraSpins * TAU + ((targetAngle - currentRotation + TAU) % TAU);

  canvas.style.transition = 'transform 4.5s cubic-bezier(0.17, 0.67, 0.12, 0.99)';
  canvas.style.transform = 'rotate(' + spinTo + 'rad)';
  currentRotation = spinTo;

  setTimeout(() => {
    canvas.style.transition = 'none';
    currentRotation = targetAngle;
    canvas.style.transform = 'rotate(' + targetAngle + 'rad)';
    const winner = prizes[winnerIdx];
    showWinner(winner);
    isSpinning = false;
    document.getElementById('spinBtn').disabled = false;
  }, 4700);
}

function showWinner(prize) {
  const modal = document.getElementById('winnerModal');
  const nameEl = document.getElementById('modalPrizeName');
  const imgEl = document.getElementById('modalPrizeImage');
  const subEl = document.getElementById('modalPrizeSub');
  const codeEl = document.getElementById('modalPrizeCode');

  nameEl.textContent = prize?.display_name || prize?.name || 'جائزة';
  subEl.textContent = '';

  if (prize?.type === 'discount') {
    imgEl.innerHTML = '<div class="modal-prize-placeholder" style="background:' + (prize.color || '#6366f1') + '30;color:' + (prize.color || '#6366f1') + ';"><span style="font-size:2.5rem;font-weight:900;">' + prize.discount_percent + '%</span></div>';
    subEl.textContent = 'خصم ' + prize.discount_percent + '% على أي منتج';
    codeEl.style.display = 'block';
    const code = 'JENIN' + prize.discount_percent + '-' + Math.random().toString(36).substring(2, 8).toUpperCase();
    codeEl.textContent = code;
  } else if (prize?.image_url) {
    imgEl.innerHTML = '<img src="' + prize.image_url + '" class="modal-prize-image" alt="' + (prize.name || '') + '">';
    subEl.textContent = prize.value || '';
    codeEl.style.display = 'none';
  } else {
    const c = prize?.color || '#6366f1';
    imgEl.innerHTML = '<div class="modal-prize-placeholder" style="background:' + c + '30;color:' + c + ';"><i class="fas fa-' + (prize.value ? 'tag' : 'gift') + '"></i></div>';
    subEl.textContent = prize.value || '';
    codeEl.style.display = 'none';
  }
  modal.classList.add('show');
}

function closeModal() { document.getElementById('winnerModal').classList.remove('show'); }
document.getElementById('winnerModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

drawWheel(0);

/* === SETTINGS === */
function toggleSettings() {
  document.getElementById('settingsPanel').classList.toggle('open');
  document.getElementById('settingsOverlay').classList.toggle('show');
  hideForm();
}

function showAddForm() {
  document.getElementById('formId').value = '';
  document.getElementById('formType').value = 'product';
  document.getElementById('formName').value = '';
  document.getElementById('formValue').value = '';
  document.getElementById('formColor').value = '#6366f1';
  document.getElementById('formDiscount').value = '20';
  document.getElementById('formWeight').value = '1';
  document.getElementById('formImage').value = '';
  document.getElementById('currentImage').style.display = 'none';
  window._removeImage = false;
  toggleFormType();
  document.getElementById('prizeForm').classList.add('show');
  document.getElementById('prizeForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideForm() {
  document.getElementById('prizeForm').classList.remove('show');
  window._removeImage = false;
}

function toggleFormType() {
  const t = document.getElementById('formType').value;
  document.getElementById('formProductFields').style.display = t === 'product' ? 'block' : 'none';
  document.getElementById('formDiscountFields').style.display = t === 'discount' ? 'block' : 'none';
}

function savePrize() {
  const formData = new FormData();
  const id = document.getElementById('formId').value;
  if (id) formData.append('id', id);
  formData.append('type', document.getElementById('formType').value);
  formData.append('color', document.getElementById('formColor').value);
  formData.append('weight', document.getElementById('formWeight').value || '1');

  const type = document.getElementById('formType').value;
  if (type === 'product') {
    formData.append('name', document.getElementById('formName').value);
    formData.append('value', document.getElementById('formValue').value);
    const img = document.getElementById('formImage').files[0];
    if (img) formData.append('image', img);
    if (window._removeImage) formData.append('remove_image', '1');
  } else {
    formData.append('discount_percent', document.getElementById('formDiscount').value);
  }

  fetch('{{ route('wheel.inline.save') }}', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' },
    body: formData,
  }).then(r => r.json()).then(d => {
    if (d.success) { location.reload(); }
    else { alert('حدث خطأ'); }
  }).catch(() => { alert('حدث خطأ في الاتصال'); });
}

function editPrize(id) {
  const prize = prizes.find(p => p.id === id);
  if (!prize) return;
  document.getElementById('formId').value = prize.id;
  document.getElementById('formType').value = prize.type || 'product';
  document.getElementById('formColor').value = prize.color || '#6366f1';
  document.getElementById('formWeight').value = prize.weight || '1';
  if (prize.type === 'product') {
    document.getElementById('formName').value = prize.name || '';
    document.getElementById('formValue').value = prize.value || '';
    const imgDiv = document.getElementById('currentImage');
    if (prize.image_url) {
      document.getElementById('currentImageSrc').src = prize.image_url;
      imgDiv.style.display = 'block';
    } else {
      imgDiv.style.display = 'none';
    }
  } else {
    document.getElementById('formDiscount').value = prize.discount_percent || '20';
  }
  document.getElementById('formImage').value = '';
  toggleFormType();
  document.getElementById('prizeForm').classList.add('show');
  document.getElementById('prizeForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function removeImage() {
  document.getElementById('currentImage').style.display = 'none';
  document.getElementById('formImage').value = '';
  window._removeImage = true;
}

function deletePrize(id) {
  if (!confirm('حذف هذا العنصر؟')) return;
  fetch('{{ route('wheel.inline.delete') }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
    },
    body: JSON.stringify({ id }),
  }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}

function togglePrize(id) {
  fetch('{{ url('/') }}/skin-admin/wheel-prizes/' + id + '/toggle', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' },
  }).then(() => { setTimeout(() => location.reload(), 300); });
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault();
    if (document.getElementById('winnerModal').classList.contains('show')) { closeModal(); }
    else if (!document.getElementById('settingsPanel').classList.contains('open')) { spin(); }
  }
  if (e.key === 'Escape') { closeModal(); if (document.getElementById('settingsPanel').classList.contains('open')) toggleSettings(); }
});
</script>
</body>
</html>
