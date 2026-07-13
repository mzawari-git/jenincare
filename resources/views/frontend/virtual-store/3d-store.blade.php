<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>المتجر ثلاثي الأبعاد — جنين كير</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<!-- Three.js CDN (classic UMD build — works everywhere) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:100%;height:100%;overflow:hidden;font-family:'Cairo',sans-serif;background:#0a0a0a}
canvas#c{display:block;width:100vw!important;height:100vh!important;position:fixed;top:0;left:0;z-index:1;cursor:crosshair}

/* START SCREEN */
#startScreen{
  position:fixed;inset:0;z-index:1000;
  background:linear-gradient(135deg,#1a0a2e 0%,#16213e 50%,#0f3460 100%);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:20px;
}
.logo{
  font-size:clamp(32px,5vw,56px);font-weight:800;
  background:linear-gradient(135deg,#e91e63,#ff6b9d,#c471ed);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;margin-bottom:10px;
}
.tagline{color:rgba(255,255,255,0.65);font-size:clamp(14px,2.5vw,19px);margin-bottom:36px}
.info-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;width:min(92vw,500px);margin-bottom:36px}
.info-box{
  background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);
  border-radius:14px;padding:16px 10px;text-align:center;
}
.info-box .ic{font-size:26px;margin-bottom:6px}
.info-box span{color:rgba(255,255,255,0.65);font-size:11px;display:block}
.info-box strong{color:#fff;font-size:13px}
#enterBtn{
  background:linear-gradient(135deg,#e91e63,#c471ed);
  color:#fff;border:none;padding:16px 48px;border-radius:50px;
  font-size:17px;font-weight:700;font-family:'Cairo',sans-serif;
  cursor:pointer;box-shadow:0 10px 40px rgba(233,30,99,.45);
  transition:all .25s;letter-spacing:.5px;
}
#enterBtn:hover{transform:translateY(-2px);box-shadow:0 16px 50px rgba(233,30,99,.6)}

/* LOADING */
#loadScreen{
  position:fixed;inset:0;z-index:999;
  background:#1a0a2e;display:none;
  flex-direction:column;align-items:center;justify-content:center;
}
.load-bar-bg{width:260px;height:4px;background:rgba(255,255,255,.1);border-radius:4px;margin:18px 0}
.load-bar{height:100%;width:0%;background:linear-gradient(90deg,#e91e63,#c471ed);border-radius:4px;transition:width .3s}
.load-txt{color:rgba(255,255,255,.65);font-size:14px}

/* HUD */
#hud{position:fixed;inset:0;z-index:10;pointer-events:none;display:none}

/* Top bar */
#topBar{
  position:absolute;top:0;left:0;right:0;padding:14px 18px;
  background:linear-gradient(180deg,rgba(0,0,0,.75),transparent);
  display:flex;align-items:center;justify-content:space-between;
  pointer-events:auto;gap:10px;
}
#backBtn{
  background:rgba(255,255,255,.13);backdrop-filter:blur(10px);
  border:1px solid rgba(255,255,255,.2);color:#fff;
  padding:8px 16px;border-radius:30px;cursor:pointer;
  font-family:'Cairo',sans-serif;font-size:13px;font-weight:600;
  transition:all .2s;text-decoration:none;display:flex;align-items:center;gap:6px;
}
#backBtn:hover{background:rgba(255,255,255,.22)}
.sname{
  color:#fff;font-size:16px;font-weight:700;
  background:linear-gradient(135deg,#e91e63,#c471ed);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
#cartBtn{
  background:rgba(233,30,99,.85);backdrop-filter:blur(10px);
  border:1px solid rgba(233,30,99,.4);color:#fff;
  padding:8px 16px;border-radius:30px;cursor:pointer;
  font-family:'Cairo',sans-serif;font-size:13px;font-weight:600;
  transition:all .2s;text-decoration:none;display:flex;align-items:center;gap:6px;
}
#cartBtn:hover{background:rgba(233,30,99,1)}
#cartCount{
  background:#fff;color:#e91e63;border-radius:50%;
  width:20px;height:20px;display:inline-flex;align-items:center;
  justify-content:center;font-size:11px;font-weight:800;
}

/* Zone label */
#zoneLabel{
  position:absolute;top:74px;left:50%;transform:translateX(-50%);
  background:rgba(0,0,0,.65);backdrop-filter:blur(10px);
  border:1px solid rgba(255,255,255,.15);color:#fff;
  padding:7px 22px;border-radius:30px;font-size:13px;font-weight:600;
  opacity:0;transition:opacity .5s;white-space:nowrap;
}

/* Crosshair */
#xhair{
  position:absolute;top:50%;left:50%;
  transform:translate(-50%,-50%);
  width:22px;height:22px;opacity:.55;pointer-events:none;
}
#xhair::before,#xhair::after{
  content:'';position:absolute;background:#fff;border-radius:2px;
}
#xhair::before{width:2px;height:22px;left:10px;top:0}
#xhair::after{width:22px;height:2px;left:0;top:10px}

/* Mini-map */
#mmap{
  position:absolute;top:74px;right:16px;
  width:116px;height:96px;
  background:rgba(0,0,0,.75);backdrop-filter:blur(8px);
  border:1px solid rgba(255,255,255,.2);border-radius:12px;overflow:hidden;
}
#mmap canvas{width:100%!important;height:100%!important;position:relative!important}
.mmap-lbl{
  position:absolute;bottom:3px;left:50%;transform:translateX(-50%);
  color:rgba(255,255,255,.4);font-size:9px;white-space:nowrap;
}

/* Controls hint */
#hint{
  position:absolute;bottom:18px;left:50%;transform:translateX(-50%);
  display:flex;gap:10px;align-items:center;
}
.hk{
  background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);
  color:#fff;padding:4px 9px;border-radius:6px;font-size:12px;font-weight:600;
}
.hs{color:rgba(255,255,255,.4);font-size:11px}

/* Proximity ring */
#proxRing{
  position:fixed;bottom:72px;left:50%;transform:translateX(-50%);
  display:flex;align-items:center;gap:8px;
  opacity:0;transition:opacity .3s;pointer-events:none;
}
.prx-dot{width:8px;height:8px;border-radius:50%;background:#e91e63;animation:pls .8s infinite}
.prx-txt{color:rgba(255,255,255,.75);font-size:13px;font-weight:600}
@keyframes pls{0%,100%{transform:scale(1)}50%{transform:scale(1.5);opacity:.6}}

/* PRODUCT CARD */
#prodCard{
  position:fixed;right:18px;top:50%;transform:translateY(-50%) translateX(30px);
  width:min(272px,90vw);z-index:20;
  background:rgba(8,8,18,.92);backdrop-filter:blur(22px);
  border:1px solid rgba(233,30,99,.35);border-radius:20px;
  padding:18px;pointer-events:auto;
  opacity:0;transition:all .4s cubic-bezier(.34,1.56,.64,1);
  visibility:hidden;
}
#prodCard.show{
  opacity:1;transform:translateY(-50%) translateX(0);visibility:visible;
}
.pimg{
  width:100%;height:130px;border-radius:12px;
  background:linear-gradient(135deg,rgba(233,30,99,.18),rgba(196,113,237,.18));
  display:flex;align-items:center;justify-content:center;
  font-size:54px;margin-bottom:14px;
}
.pbadge{
  display:inline-block;background:rgba(233,30,99,.18);
  color:#e91e63;padding:3px 10px;border-radius:20px;
  font-size:11px;margin-bottom:8px;border:1px solid rgba(233,30,99,.28);
}
.pname{color:#fff;font-size:14px;font-weight:700;margin-bottom:8px;line-height:1.5}
.prow{display:flex;align-items:center;gap:8px;margin-bottom:14px}
.pprice{color:#e91e63;font-size:22px;font-weight:800}
.pcur{color:rgba(255,255,255,.45);font-size:13px}
.pold{color:rgba(255,255,255,.3);font-size:12px;text-decoration:line-through}
.pactions{display:flex;gap:8px}
#addBtn{
  flex:1;background:linear-gradient(135deg,#e91e63,#c471ed);
  color:#fff;border:none;padding:11px;border-radius:12px;
  font-family:'Cairo',sans-serif;font-size:14px;font-weight:700;
  cursor:pointer;transition:all .2s;
}
#addBtn:hover{opacity:.9}
#addBtn.busy{opacity:.6;pointer-events:none}
#viewBtn{
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
  color:#fff;padding:11px 13px;border-radius:12px;
  cursor:pointer;text-decoration:none;font-size:18px;
  display:flex;align-items:center;justify-content:center;
  transition:all .2s;
}
#viewBtn:hover{background:rgba(255,255,255,.18)}

/* Success toast */
#toast{
  position:fixed;top:75px;left:50%;transform:translateX(-50%);
  background:linear-gradient(135deg,#4caf50,#2e7d32);
  color:#fff;padding:11px 26px;border-radius:30px;
  font-family:'Cairo',sans-serif;font-size:14px;font-weight:700;
  z-index:2000;opacity:0;transition:opacity .3s;pointer-events:none;
  box-shadow:0 8px 28px rgba(76,175,80,.4);
}
#toast.show{opacity:1}

/* MOBILE CONTROLS */
#mob{position:fixed;bottom:16px;left:0;right:0;display:none;z-index:15;pointer-events:none}
#dpad{position:absolute;left:16px;bottom:0;width:118px;height:118px;pointer-events:auto}
.db{
  position:absolute;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);
  border:1px solid rgba(255,255,255,.22);color:#fff;
  border-radius:10px;width:36px;height:36px;
  display:flex;align-items:center;justify-content:center;
  font-size:15px;cursor:pointer;user-select:none;
  -webkit-user-select:none;-webkit-tap-highlight-color:transparent;
}
.db:active,.db.on{background:rgba(233,30,99,.5)}
#dU{top:0;left:41px}#dD{bottom:0;left:41px}
#dL{left:0;top:41px}#dR{right:0;top:41px}
#rpad{position:absolute;right:16px;bottom:0;width:118px;height:118px;pointer-events:auto}
.rb{
  position:absolute;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);
  border:1px solid rgba(255,255,255,.22);color:#fff;
  border-radius:10px;width:52px;height:48px;
  display:flex;align-items:center;justify-content:center;
  font-size:20px;cursor:pointer;user-select:none;
  -webkit-user-select:none;-webkit-tap-highlight-color:transparent;
}
.rb:active,.rb.on{background:rgba(100,150,255,.5)}
#rL{left:0;top:35px}#rR{right:0;top:35px}
.rlbl{position:absolute;top:0;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.35);font-size:10px}

/* Error banner */
#errBanner{
  position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
  background:rgba(200,0,0,.9);color:#fff;padding:20px 30px;border-radius:14px;
  font-size:15px;font-weight:600;z-index:9999;display:none;text-align:center;
  max-width:90vw;
}

@media(max-width:768px){
  #mmap,#hint{display:none}
  #mob{display:block}
  #prodCard{right:50%;transform:translate(50%,-50%) translateX(30px)}
  #prodCard.show{transform:translate(50%,-50%)}
}
</style>
</head>
<body>

<!-- START SCREEN -->
<div id="startScreen">
  <div class="logo">🏪 جنين كير</div>
  <div class="tagline">تجوّل داخل المتجر واختر منتجاتك</div>
  <div class="info-grid">
    <div class="info-box"><div class="ic">📐</div><strong>4×5×4 م</strong><span>أبعاد المتجر الحقيقية</span></div>
    <div class="info-box"><div class="ic">🚶</div><strong>تجوّل حر</strong><span>W A S D + الفأرة</span></div>
    <div class="info-box"><div class="ic">🛒</div><strong>أضف للسلة</strong><span>منتجات حقيقية من المتجر</span></div>
    <div class="info-box"><div class="ic">🔄</div><strong>يتجدّد تلقائياً</strong><span>جميع المنتجات تظهر بالتناوب</span></div>
  </div>
  <button id="enterBtn">🚪 ادخل المتجر</button>
</div>

<!-- LOADING -->
<div id="loadScreen">
  <div style="font-size:40px;margin-bottom:14px">⏳</div>
  <div style="color:#fff;font-size:17px;font-weight:700">جاري تحضير المتجر...</div>
  <div class="load-bar-bg"><div class="load-bar" id="loadBar"></div></div>
  <div class="load-txt" id="loadTxt">تحضير الغرفة...</div>
</div>

<!-- HUD -->
<div id="hud">
  <div id="topBar">
    <a href="/virtual-store" id="backBtn">← الرجوع</a>
    <div class="sname">🏪 جنين كير</div>
    <a href="/cart" id="cartBtn">🛒 <span id="cartCount">0</span></a>
  </div>
  <div id="zoneLabel">مرحباً في متجر جنين كير</div>
  <div id="xhair"></div>
  <div id="mmap">
    <canvas id="mmapCanvas" width="116" height="96"></canvas>
    <div class="mmap-lbl">خريطة المتجر</div>
  </div>
  <div id="hint">
    <span class="hk">W/↑</span><span class="hs">أمام</span>
    <span class="hk">S/↓</span><span class="hs">خلف</span>
    <span class="hk">A D</span><span class="hs">يمين/يسار</span>
    <span class="hs">|</span>
    <span class="hk">🖱️ اسحب</span><span class="hs">للنظر</span>
  </div>
  <div id="proxRing">
    <div class="prx-dot"></div>
    <span class="prx-txt">اقترب من الرف لرؤية المنتجات</span>
  </div>
</div>

<!-- PRODUCT CARD -->
<div id="prodCard">
  <div class="pimg" id="pEmoji">🧴</div>
  <div class="pbadge" id="pZone">عناية</div>
  <div class="pname" id="pName">—</div>
  <div class="prow">
    <span class="pprice" id="pPrice">0</span>
    <span class="pcur">₪</span>
    <span class="pold" id="pOld"></span>
  </div>
  <div class="pactions">
    <button id="addBtn" onclick="doAddToCart()">🛒 أضف للسلة</button>
    <a href="#" id="viewBtn" target="_blank">👁️</a>
  </div>
</div>
<div id="toast">✅ تمت الإضافة للسلة!</div>

<!-- MOBILE -->
<div id="mob">
  <div id="dpad">
    <div class="db" id="dU">↑</div>
    <div class="db" id="dL">←</div>
    <div class="db" id="dR">→</div>
    <div class="db" id="dD">↓</div>
  </div>
  <div id="rpad">
    <span class="rlbl">النظر</span>
    <div class="rb" id="rL">↩</div>
    <div class="rb" id="rR">↪</div>
  </div>
</div>

<!-- ERROR BANNER -->
<div id="errBanner"></div>

<!-- 3D CANVAS -->
<canvas id="c"></canvas>

<script>
// ============================================================
//  CHECK THREE.JS LOADED
// ============================================================
window.addEventListener('load', function() {
  if (typeof THREE === 'undefined') {
    document.getElementById('errBanner').style.display = 'block';
    document.getElementById('errBanner').textContent = '❌ فشل تحميل مكتبة Three.js — تأكد من الاتصال بالإنترنت وأعد تحميل الصفحة';
    document.getElementById('startScreen').style.display = 'none';
    return;
  }
  initApp();
});

function initApp() {

// ============================================================
//  CONFIG -- 1 Three.js unit = 25cm
//  W=16  (16x25cm = 400cm = 4000mm)
//  D=20  (20x25cm = 500cm = 5000mm)
//  H=16  (16x25cm = 400cm = ارتفاع 4م)
// ============================================================
const W  = 16;    // عرض 4000سم
const D  = 20;    // عمق 5000سم
const H  = 16;    // ارتفاع 4م
const WT = 0.48;  // سماكة الجدار
const SD = 1.20;  // عمق الرف
const ST = 0.14;  // سماكة لوح الرف
const SH = 15.4;  // ارتفاع وحدة الرف (385سم)
const SU = 4.0;   // عرض وحدة الرف
const NS = 6;     // عدد طوابق كل وحدة
const SPEED = 0.22;   // سرعة مناسبة للمساحة
const LSPD  = 0.0020;
const CAMH  = 6.88;   // 172cm / 25 = 6.88 وحدة
const PROX  = 6.0;    // مسافة اكتشاف الرف

// ============================================================
//  PRODUCTS DATA
// ============================================================
const PRODS = {
  left: [
    {id:101,name:'كريم CeraVe للترطيب اليومي',     price:48, emoji:'🧴', zone:'عناية بالبشرة',    color:0x4fc3f7, slug:'cerave-moisturizer'},
    {id:102,name:'CeraVe غسول منظّف للوجه',         price:42, emoji:'🫧', zone:'عناية بالبشرة',    color:0x81d4fa, slug:'cerave-cleanser'},
    {id:103,name:'سيروم فيتامين C مُشرِّق',          price:65, emoji:'✨', zone:'فيتامينات',         color:0xfff176, slug:'vitamin-c-serum'},
    {id:104,name:'كريم الكركم المُبيِّض',             price:28, emoji:'🌿', zone:'عناية بالبشرة',    color:0xffd54f, slug:'turmeric-cream'},
    {id:105,name:'فيتامين D3 دعم المناعة',           price:35, emoji:'💊', zone:'فيتامينات',         color:0xffe0b2, slug:'vitamin-d3'},
    {id:106,name:'واقي شمس SPF 50+',                price:58, emoji:'☀️', zone:'حماية',            color:0x80cbc4, slug:'spf50-sunscreen'},
    {id:107,name:'سيروم الكولاجين المُضغوط',          price:72, emoji:'💆', zone:'مكافحة الشيخوخة',  color:0xf48fb1, slug:'collagen-serum'},
    {id:108,name:'كريم الليل بالبروتين',              price:38, emoji:'🌙', zone:'عناية بالبشرة',    color:0xce93d8, slug:'night-cream'},
    {id:109,name:'حبوب بيوتين للشعر والأظافر',       price:45, emoji:'💊', zone:'فيتامينات',         color:0xa5d6a7, slug:'biotin-capsules'},
    {id:110,name:'مقشّر الوجه بالسكر الطبيعي',       price:32, emoji:'🍯', zone:'عناية بالبشرة',    color:0xffcc80, slug:'sugar-scrub'},
    {id:111,name:'ماسك الطين المُنقِّي للمسام',       price:29, emoji:'🪴', zone:'عناية بالبشرة',    color:0xa5d6a7, slug:'clay-mask'},
    {id:112,name:'سيروم الريتينول الليلي',            price:89, emoji:'🌟', zone:'مكافحة الشيخوخة',  color:0xfff9c4, slug:'retinol-serum'},
    {id:113,name:'كريم العين المُرطِّب',              price:55, emoji:'👁️', zone:'عناية بالبشرة',   color:0xb3e5fc, slug:'eye-cream'},
    {id:114,name:'مرطب الجسم بزيت الأرغان',          price:42, emoji:'💧', zone:'عناية بالجسم',     color:0xd7ccc8, slug:'argan-body-cream'},
    {id:115,name:'فيتامين E مضاد للأكسدة',           price:30, emoji:'💊', zone:'فيتامينات',         color:0xdcedc8, slug:'vitamin-e'},
  ],
  right: [
    {id:201,name:'Colorist صبغة شقراء ذهبية',       price:28, emoji:'👱', zone:'صبغات الشعر',      color:0xffe082, slug:'colorist-golden'},
    {id:202,name:'Colorist صبغة بني داكن',          price:28, emoji:'💇', zone:'صبغات الشعر',      color:0x6d4c41, slug:'colorist-brown'},
    {id:203,name:'Colorist صبغة أسود فاحم',         price:25, emoji:'🖤', zone:'صبغات الشعر',      color:0x37474f, slug:'colorist-black'},
    {id:204,name:'Colorist صبغة بني رمادي',         price:30, emoji:'💇', zone:'صبغات الشعر',      color:0x90a4ae, slug:'colorist-ash'},
    {id:205,name:'Colorist صبغة أحمر نحاسي',        price:32, emoji:'🦰', zone:'صبغات الشعر',      color:0xef6c00, slug:'colorist-copper'},
    {id:206,name:'Colorist صبغة بيج فاتح',          price:30, emoji:'👱', zone:'صبغات الشعر',      color:0xffe0b2, slug:'colorist-beige'},
    {id:207,name:'Colorist صبغة أشقر بلاتيني',      price:35, emoji:'👸', zone:'صبغات الشعر',      color:0xf5f5f5, slug:'colorist-platinum'},
    {id:208,name:'كريم الكيراتين لتنعيم الشعر',      price:65, emoji:'💆', zone:'عناية بالشعر',     color:0xe1bee7, slug:'keratin-cream'},
    {id:209,name:'قناع الشعر بالزيت المغربي',        price:48, emoji:'🫙', zone:'عناية بالشعر',     color:0xffe0b2, slug:'hair-mask-argan'},
    {id:210,name:'سيروم ترطيب الشعر التالف',         price:38, emoji:'💧', zone:'عناية بالشعر',     color:0xb2dfdb, slug:'hair-serum'},
    {id:211,name:'مثبّت الصبغة — نتيجة ثابتة',      price:20, emoji:'🔒', zone:'صبغات الشعر',      color:0xb0bec5, slug:'color-fix'},
    {id:212,name:'زيت الشعر المغذّي بالبروتين',      price:44, emoji:'✨', zone:'عناية بالشعر',     color:0xfff9c4, slug:'protein-oil'},
    {id:213,name:'روبان شعر — كومبو ألوان',          price:15, emoji:'🎀', zone:'إكسسوار شعر',      color:0xf8bbd9, slug:'hair-ribbons'},
    {id:214,name:'غطاء حماية الشعر أثناء النوم',     price:22, emoji:'🧢', zone:'عناية بالشعر',     color:0xce93d8, slug:'sleep-cap'},
    {id:215,name:'Colorist بني طبيعي دافئ',         price:28, emoji:'🎨', zone:'صبغات الشعر',      color:0x8d6e63, slug:'colorist-natural'},
  ],
  back: [
    {id:301,name:'عطر ورود الطائف الأصيل',           price:95,  emoji:'🌹', zone:'عطور',             color:0xef5350, slug:'taif-rose'},
    {id:302,name:'عطر العود الإماراتي الفاخر',        price:120, emoji:'🪵', zone:'عطور',             color:0x6d4c41, slug:'oud-perfume'},
    {id:303,name:'سيت هدايا العناية الفاخر',          price:135, emoji:'🎁', zone:'هدايا',            color:0xe91e63, slug:'luxury-gift'},
    {id:304,name:'عطر الياسمين الطبيعي',             price:75,  emoji:'🌸', zone:'عطور',             color:0xf8bbd9, slug:'jasmine-perfume'},
    {id:305,name:'سيت هدايا عيد الأم',              price:98,  emoji:'💐', zone:'هدايا',            color:0xff80ab, slug:'mothers-set'},
    {id:306,name:'لوشن الجسم المُعطَّر بالورد',       price:42,  emoji:'🌷', zone:'عناية بالجسم',     color:0xf48fb1, slug:'rose-lotion'},
    {id:307,name:'ماء مسك العرائس',                  price:65,  emoji:'👰', zone:'عطور',             color:0xffe0b2, slug:'bride-musk'},
    {id:308,name:'كريم العناية بالأظافر',            price:25,  emoji:'💅', zone:'عناية',            color:0xb2ebf2, slug:'nail-cream'},
    {id:309,name:'سيت جمال الصباح الكامل',           price:115, emoji:'☀️', zone:'هدايا',            color:0xfff176, slug:'morning-set'},
    {id:310,name:'بخّاخ تثبيت المكياج 24 ساعة',     price:38,  emoji:'💨', zone:'مكياج',            color:0xb3e5fc, slug:'fixer-spray'},
    {id:311,name:'قطن طبي فاخر 200 قطعة',           price:15,  emoji:'🧸', zone:'مستلزمات',          color:0xf5f5f5, slug:'cotton-pads'},
    {id:312,name:'مزيل عيون ومكياج حساس',            price:32,  emoji:'👁️', zone:'مكياج',           color:0xe8eaf6, slug:'makeup-remover'},
  ],
  island: [
    {id:401,name:'أحمر شفاه Bribri ماتي',           price:22, emoji:'💄', zone:'أحمر شفاه',         color:0xc62828, slug:'bribri-matte'},
    {id:402,name:'Bribri غلوس شفاه ملوّن',          price:18, emoji:'👄', zone:'أحمر شفاه',         color:0xff8a80, slug:'bribri-gloss'},
    {id:403,name:'Mazmozil أحمر شفاه نود',          price:25, emoji:'💋', zone:'أحمر شفاه',         color:0xd4a574, slug:'mazmozil-nude'},
    {id:404,name:'بودرة ضغط شفافة Bribri',          price:35, emoji:'🌸', zone:'مكياج',            color:0xf8bbd9, slug:'bribri-powder'},
    {id:405,name:'Bribri كونتور وهايلايتر',         price:42, emoji:'✨', zone:'مكياج',            color:0xfff9c4, slug:'bribri-contour'},
    {id:406,name:'ريمل Bribri Exagger-Eyes',        price:28, emoji:'👁️', zone:'مكياج العيون',     color:0x1a1a2e, slug:'bribri-mascara'},
    {id:407,name:'محدد العيون Bribri أسود',          price:20, emoji:'✏️', zone:'مكياج العيون',     color:0x212121, slug:'bribri-eyeliner'},
    {id:408,name:'ظل عيون Mazmozil',                price:45, emoji:'🎨', zone:'مكياج العيون',     color:0x9c27b0, slug:'mazmozil-shadow'},
    {id:409,name:'كونسيلر Bribri خفيف',             price:30, emoji:'🖌️', zone:'مكياج',           color:0xefebe9, slug:'bribri-concealer'},
    {id:410,name:'فرشاة مكياج كاملة — سيت',         price:55, emoji:'🖌️', zone:'فرش مكياج',       color:0xab47bc, slug:'brush-set'},
    {id:411,name:'Bribri أحمر شفاه سائل',           price:24, emoji:'💄', zone:'أحمر شفاه',         color:0xe53935, slug:'bribri-liquid'},
    {id:412,name:'باليت ظل عيون Bribri',            price:38, emoji:'🎨', zone:'مكياج العيون',     color:0xff8f00, slug:'bribri-palette'},
  ],
};

// منتجات الموقع الحقيقية (تُمرَّر من الـ controller) — تستبدل بيانات العرض حيثما توفّرت
const SERVER_PRODS = @json($shelfProducts ?? []);
// جميع المنتجات بشكل مسطح — المصدر الرئيسي لعرض الجدار الدوّار
const ALL_PRODUCTS = @json($allProducts ?? []);
// هل لدينا منتجات حقيقية من قاعدة البيانات؟
const HAS_REAL_PRODUCTS = ALL_PRODUCTS.length > 0;
const ZONE_STYLE = {
  left:   { color: 0xEC4899, emoji: '🧴' },
  right:  { color: 0x8B5CF6, emoji: '💇' },
  back:   { color: 0xF59E0B, emoji: '🎁' },
  island: { color: 0xEF4444, emoji: '💄' },
};
const CAT_EMOJI = {
  'العناية بالشعر': '💇', 'صبغات الشعر': '🎨', 'العناية بالأظافر': '💅',
  'المكياج والتجميل': '💄', 'العناية بالبشرة': '🧴', 'أجهزة العناية بالبشرة': '🔬',
  'إزالة الشعر': '⚡', 'العطور': '🌹', 'منتجات مسك': '🌸',
  'تجهيز الصالونات': '👑', 'العناية بالجسم': '✨', 'العروض': '🎁',
  'الباديكير': '👣', 'الحجامة': '🩸', 'العناية باللحية': '🧔',
  'الرموش': '👀', 'البيرسينج': '💎',
};
// Replace PRODS with real products from database if available
if (HAS_REAL_PRODUCTS) {
  // Distribute all products across the 4 zones evenly (round-robin)
  const zoneKeys = ['left', 'right', 'back', 'island'];
  ALL_PRODUCTS.forEach((p, i) => {
    const z = zoneKeys[i % zoneKeys.length];
    if (!PRODS[z]) PRODS[z] = [];
    const zoneStyle = ZONE_STYLE[z] || { color: 0xdddddd, emoji: '🧴' };
    let emoji = zoneStyle.emoji;
    let color = zoneStyle.color;
    for (const [kw, e] of Object.entries(CAT_EMOJI)) {
      if ((p.zone || '').includes(kw) || (p.cat || '').includes(kw)) {
        emoji = e;
        color = (e === '💇' || e === '🎨') ? 0x8B5CF6 :
                (e === '💄' || e === '💅') ? 0xEC4899 :
                (e === '🌹' || e === '🌸') ? 0xF59E0B :
                (e === '👑' || e === '🔬') ? 0x3B82F6 :
                (e === '🎁' || e === '⚡') ? 0xEF4444 :
                (e === '🧔' || e === '👣') ? 0x10B981 : color;
        break;
      }
    }
    PRODS[z].push({
      id: p.id, name: p.name, price: p.price,
      old: p.old || null, slug: p.slug, image: p.image || '',
      zone: p.zone || '', emoji, color, cat: p.cat || '',
    });
  });
} else {
  // Fallback: use SERVER_PRODS zone grouping if available
  ['left', 'right', 'back', 'island'].forEach(z => {
    const list = SERVER_PRODS[z];
    if (Array.isArray(list) && list.length) {
      PRODS[z] = list.map(p => {
        const zoneStyle = ZONE_STYLE[z] || { color: 0xdddddd, emoji: '🧴' };
        let emoji = zoneStyle.emoji;
        let color = zoneStyle.color;
        for (const [kw, e] of Object.entries(CAT_EMOJI)) {
          if ((p.zone || '').includes(kw) || (p.cat || '').includes(kw)) {
            emoji = e;
            color = (e === '💇' || e === '🎨') ? 0x8B5CF6 :
                    (e === '💄' || e === '💅') ? 0xEC4899 :
                    (e === '🌹' || e === '🌸') ? 0xF59E0B :
                    (e === '👑' || e === '🔬') ? 0x3B82F6 :
                    (e === '🎁' || e === '⚡') ? 0xEF4444 :
                    (e === '🧔' || e === '👣') ? 0x10B981 : color;
            break;
          }
        }
        return {
          id: p.id, name: p.name, price: p.price,
          old: p.old || null, slug: p.slug, image: p.image || '',
          zone: p.zone || '', emoji, color, cat: p.cat || '',
        };
      });
    }
  });
}

// ============================================================
//  SCENE, RENDERER, CAMERA
// ============================================================
const canvas = document.getElementById('c');
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;

const scene = new THREE.Scene();
scene.background = new THREE.Color(0x0a0812);
// ضباب يتناسب مع مساحة 5000سم
scene.fog = new THREE.Fog(0x0a0812, 160, 480);

// FOV = 85 درجة لزاوية رؤية واسعة
const camera = new THREE.PerspectiveCamera(85, window.innerWidth / window.innerHeight, 0.4, 800);
// نبدأ عند المدخل (مقدمة الغرفة) متجهين نحو داخل المتجر (-Z)
camera.position.set(0, CAMH, D / 2 - 1.5);
camera.rotation.order = 'YXZ';

// ============================================================
//  LIGHTING
// ============================================================
scene.add(new THREE.AmbientLight(0xfff5ea, 0.35));

// ضوء سقفي اتجاهي واحد يُسقط الظلال على كامل الغرفة
// (نتجنّب عشرات المصابيح المُسقِطة للظل لئلا نتجاوز حد وحدات النسيج في WebGL)
const sun = new THREE.DirectionalLight(0xfff5ea, 0.55);
sun.position.set(W * 0.3, H + 8, D * 0.25);
sun.castShadow = true;
sun.shadow.mapSize.set(2048, 2048);
sun.shadow.camera.left = -W;  sun.shadow.camera.right = W;
sun.shadow.camera.top = D;    sun.shadow.camera.bottom = -D;
sun.shadow.camera.near = 1;   sun.shadow.camera.far = H * 3;
scene.add(sun);

// مصابيح سقف موزّعة داخل حدود الغرفة (بدون ظل) لإضاءة متساوية
function ceiling(x, z) {
  const pl = new THREE.PointLight(0xfff8ee, 0.85, 64, 1.6);
  pl.position.set(x, H - 2, z);
  scene.add(pl);
  const g = new THREE.Mesh(new THREE.SphereGeometry(0.72, 10, 10),
    new THREE.MeshBasicMaterial({ color: 0xfff8d0 }));
  g.position.copy(pl.position);
  scene.add(g);
}
const L_COLS = [-W / 4, W / 4];          // عمودان داخل العرض (±8)
const L_ROWS = [-8, -4, 0, 4, 8];        // خمسة صفوف على طول العمق (±10)
for (const lx of L_COLS) for (const lz of L_ROWS) ceiling(lx, lz);

// اضاءة جانبية ملونة
const pinkLight = new THREE.PointLight(0xff6b9d, 0.8, 56);
pinkLight.position.set(-W/2 + 12, H/2, 0);
scene.add(pinkLight);

const blueLight = new THREE.PointLight(0x6bb5ff, 0.8, 56);
blueLight.position.set(W/2 - 12, H/2, 0);
scene.add(blueLight);

// ============================================================
//  MATERIALS
// ============================================================
function mat(c, r = 0.8, m = 0) {
  return new THREE.MeshStandardMaterial({ color: c, roughness: r, metalness: m });
}
const M = {
  floor:  mat(0xd2c4ae, 0.88),
  wall:   mat(0xf3ede6, 0.85),
  ceil:   mat(0xfafafa, 0.9),
  frame:  mat(0x1c1c1c, 0.65, 0.1),
  board:  mat(0xfcfcfc, 0.78),
  door:   mat(0x2a2a2a, 0.6, 0.2),
  glass:  new THREE.MeshStandardMaterial({ color: 0x88ccff, transparent: true, opacity: 0.22, roughness: 0.05 }),
};

const USE_REAL_WALL_PHOTOS = false;
const BASE_PATH = window.location.pathname.replace(/\/virtual-store\/3d.*$/, '') + '/';
const PANEL_W = 3.55;
const PANEL_H = H - 0.48;
const WALL_PHOTOS = {
  left: [
    BASE_PATH + 'images/virtual-store/walls/wall-10.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-11.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-09.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-08.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-01.jpeg',
  ],
  right: [
    BASE_PATH + 'images/virtual-store/walls/wall-02.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-03.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-04.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-05.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-06.jpeg',
  ],
  back: [
    BASE_PATH + 'images/virtual-store/walls/wall-07.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-01.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-02.jpeg',
    BASE_PATH + 'images/virtual-store/walls/wall-03.jpeg',
  ],
};
const photoLoader = new THREE.TextureLoader();

function photoMat(url, targetAspect) {
  const tx = photoLoader.load(url);
  tx.encoding = THREE.sRGBEncoding;
  tx.minFilter = THREE.LinearFilter;
  tx.magFilter = THREE.LinearFilter;
  tx.wrapS = THREE.ClampToEdgeWrapping;
  tx.wrapT = THREE.ClampToEdgeWrapping;

  const sourceAspect = 0.75;
  if (targetAspect < sourceAspect) {
    tx.repeat.x = targetAspect / sourceAspect;
    tx.offset.x = (1 - tx.repeat.x) / 2;
  }

  return new THREE.MeshBasicMaterial({ map: tx, side: THREE.DoubleSide });
}

function photoPanel(url, x, y, z, w, h, rotY) {
  const geo = new THREE.PlaneGeometry(w, h);
  const mesh = new THREE.Mesh(geo, photoMat(url, w / h));
  mesh.position.set(x, y, z);
  mesh.rotation.y = rotY;
  mesh.renderOrder = 1;
  scene.add(mesh);
  return mesh;
}

// ============================================================
//  BOX HELPER
// ============================================================
function box(w, h, d, mat, x, y, z) {
  const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
  m.position.set(x, y, z);
  m.castShadow = true;
  m.receiveShadow = true;
  scene.add(m);
  return m;
}

// ============================================================
//  ROOM (عرض 4000سم، عمق 5000سم، ارتفاع 400سم)
// ============================================================
box(W + WT * 2, WT, D + WT * 2, M.floor, 0, -WT / 2, 0);
box(W + WT * 2, WT, D + WT * 2, M.ceil,  0, H + WT / 2, 0);
box(W + WT * 2, H, WT, M.wall, 0, H / 2, -D / 2 - WT / 2);
box(WT, H, D, M.wall, -W / 2 - WT / 2, H / 2, 0);
box(WT, H, D, M.wall,  W / 2 + WT / 2, H / 2, 0);
// ===== واجهة المدخل الزجاجية (Glass storefront + glass doors) =====
const ZF   = D / 2 + WT / 2;     // مستوى الجدار الأمامي
const OP_W = 15.4;               // عرض فتحة المدخل
const OP_H = H - 4.48;           // ارتفاع الفتحة حتى العتبة (≈11.52)

// عتبة علوية فوق المدخل
box(W + WT * 2, 4.48, WT, M.wall, 0, H - 2.24, ZF);

// لوح الزجاج الكبير للواجهة
const storeGlass = new THREE.MeshStandardMaterial({
  color: 0xbfe9ff, transparent: true, opacity: 0.30,
  roughness: 0.04, metalness: 0.15, side: THREE.DoubleSide,
});
const frontGlass = new THREE.Mesh(new THREE.PlaneGeometry(OP_W, OP_H), storeGlass);
frontGlass.position.set(0, OP_H / 2, ZF - 0.02);
scene.add(frontGlass);

// إطار ألمنيوم للواجهة وللباب الزجاجي المنزلق
const FR = new THREE.MeshStandardMaterial({ color: 0xd8dde2, roughness: 0.35, metalness: 0.85 });
function frameBar(w, h, x, y) {
  const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, 0.22), FR);
  m.position.set(x, y, ZF + 0.04);
  scene.add(m);
}
frameBar(OP_W + 0.3, 0.3, 0, 0.15);        // قضيب سفلي
frameBar(OP_W + 0.3, 0.3, 0, OP_H - 0.1);  // قضيب علوي
frameBar(0.3, OP_H, -OP_W / 2, OP_H / 2);  // قائم يسار
frameBar(0.3, OP_H,  OP_W / 2, OP_H / 2);  // قائم يمين
frameBar(0.28, OP_H, 0, OP_H / 2);         // فاصل وسط (بابان)
frameBar(OP_W / 2, 0.2, -OP_W / 4, 1.2);   // قاعدة الباب الأيسر
frameBar(OP_W / 2, 0.2,  OP_W / 4, 1.2);   // قاعدة الباب الأيمن

// مقابض البابين الزجاجيين
const HANDLE = new THREE.MeshStandardMaterial({ color: 0x9aa3ab, roughness: 0.3, metalness: 0.9 });
[-0.55, 0.55].forEach(hx => {
  const hnd = new THREE.Mesh(new THREE.BoxGeometry(0.08, 1.7, 0.14), HANDLE);
  hnd.position.set(hx, 3.4, ZF + 0.12);
  scene.add(hnd);
});

if (USE_REAL_WALL_PHOTOS) {
  for (let i = 0; i < 5; i++) {
    const z = -D / 2 + 2.0 + i * 4.0;
    photoPanel(WALL_PHOTOS.left[i], -W / 2 + 0.08, PANEL_H / 2 + 0.25, z, PANEL_W, PANEL_H, Math.PI / 2);
    photoPanel(WALL_PHOTOS.right[i], W / 2 - 0.08, PANEL_H / 2 + 0.25, z, PANEL_W, PANEL_H, -Math.PI / 2);
  }

  for (let i = 0; i < 4; i++) {
    const x = -W / 2 + 2.0 + i * 4.0;
    photoPanel(WALL_PHOTOS.back[i], x, PANEL_H / 2 + 0.25, -D / 2 + 0.08, PANEL_W, PANEL_H, 0);
  }
}

// ============================================================
//  SHELF UNIT BUILDER
// ============================================================
const shelfZones = [];

function buildUnit(cx, cy, cz, uw, uh, ud, ns, side) {
  if (USE_REAL_WALL_PHOTOS && side !== 'island') return;

  const f = M.frame, b = M.board;
  // الجانبان
  if (side !== 'back') {
    box(ST, uh + ST, ud, f, cx - uw / 2 + ST / 2, cy + uh / 2, cz);
    box(ST, uh + ST, ud, f, cx + uw / 2 - ST / 2, cy + uh / 2, cz);
  } else {
    box(uw, uh + ST, ST, f, cx, cy + uh / 2, cz - ud / 2 + ST / 2);
    box(uw, uh + ST, ST, f, cx, cy + uh / 2, cz + ud / 2 - ST / 2);
  }
  // لوح القاع
  box(uw, ST, ud, f, cx, cy + ST / 2, cz);
  // لوح الخلفية الداكنة
  if (!USE_REAL_WALL_PHOTOS || side === 'island') {
    if (side === 'left')  box(ST * 0.4, uh, ud * 0.85, mat(0x111111, 0.95), cx + uw / 2 - ST / 2, cy + uh / 2, cz);
    if (side === 'right') box(ST * 0.4, uh, ud * 0.85, mat(0x111111, 0.95), cx - uw / 2 + ST / 2, cy + uh / 2, cz);
    if (side === 'back')  box(uw * 0.95, uh, ST * 0.4, mat(0x111111, 0.95), cx, cy + uh / 2, cz + ud / 2 - ST / 2);
    if (side === 'island')box(uw, uh, ST * 0.4, mat(0x111111, 0.95), cx, cy + uh / 2, cz);
  }
  // طوابق الرف
  const sp = uh / ns;
  for (let i = 0; i <= ns; i++) {
    box(uw, ST, ud, b, cx, cy + i * sp + ST / 2, cz);
  }
}

// ============================================================
//  BUILD ALL SHELVES
// ============================================================
// الجدار الأيسر  (X≈-1.83) — عمق 5م = 5 وحدات موزعة على كامل العمق
const LX = -(W / 2 - SD / 2 - WT);
for (let i = 0; i < 5; i++) {
  const z = -D / 2 + 2.0 + i * 4.0;
  buildUnit(LX, 0, z, 1.0, SH, PANEL_W, NS, 'left');
  shelfZones.push({ x: LX, z, side: 'left', prods: PRODS.left, label: '💊 عناية بالبشرة والفيتامينات' });
}

// الجدار الأيمن  (X≈+1.83) — عمق 5م = 5 وحدات موزعة على كامل العمق
const RX = W / 2 - SD / 2 - WT;
for (let i = 0; i < 5; i++) {
  const z = -D / 2 + 2.0 + i * 4.0;
  buildUnit(RX, 0, z, 1.0, SH, PANEL_W, NS, 'right');
  shelfZones.push({ x: RX, z, side: 'right', prods: PRODS.right, label: '💇 صبغات الشعر والعناية' });
}

// الجدار الخلفي  (Z≈-2.33) — عرض 4م = 4 وحدات موزعة على كامل العرض
const BZ = -(D / 2 - SD / 2 - WT);
for (let i = 0; i < 4; i++) {
  const x = -W / 2 + 2.0 + i * 4.0;
  buildUnit(x, 0, BZ, PANEL_W, SH, SD, NS, 'back');
  shelfZones.push({ x, z: BZ, side: 'back', prods: PRODS.back, label: '🎁 عطور وهدايا وكوزماتيك' });
}

// الجزيرة المركزية (2م × 1م) في منتصف العمق
const ISO_Z = -0.5;
buildUnit(0, 0, ISO_Z, 2.0, 1.45, 1.0, 3, 'island');
shelfZones.push({ x: 0, z: ISO_Z, side: 'island', prods: PRODS.island, label: '💄 أحمر شفاه ومكياج' });

// ============================================================
//  PRODUCT BOXES ON SHELVES
// ============================================================
const sp = SH / NS;

function placeRow(side, baseX, baseZ, prods, unitIdx, shelfIdx) {
  if (USE_REAL_WALL_PHOTOS) return;

  const y0 = shelfIdx * sp + ST + 0.07;
  const p  = prods[(unitIdx * NS + shelfIdx) % prods.length];
  const c  = new THREE.Color(p.color);
  const isBottle  = p.zone.includes('شعر') || p.zone.includes('غسول');
  const isGift    = p.zone.includes('هدايا') || p.zone.includes('سيت');
  const isPerfume = p.zone.includes('عطر') || p.zone.includes('مسك');
  const isLip     = p.zone.includes('شفاه');

  let pw = 0.075, ph = 0.10, pd = 0.055;
  if (isBottle)  { pw = 0.055; ph = 0.18; pd = 0.055; }
  if (isGift)    { pw = 0.11;  ph = 0.11; pd = 0.08; }
  if (isPerfume) { pw = 0.048; ph = 0.13; pd = 0.038; }
  if (isLip)     { pw = 0.022; ph = 0.11; pd = 0.022; }

  const pmat = new THREE.MeshStandardMaterial({
    color: c, roughness: 0.45, metalness: 0.12,
    emissive: c, emissiveIntensity: 0.06,
  });

  const count = Math.min(6, Math.floor(0.9 / (pw + 0.018)));
  for (let k = 0; k < count; k++) {
    const off = (k - (count - 1) / 2) * (pw + 0.018);
    let px = 0, py = y0 + ph / 2, pz = 0;
    if (side === 'left')  { px = baseX + SD / 2 - pd / 2 - 0.02; pz = baseZ + off; }
    if (side === 'right') { px = baseX - SD / 2 + pd / 2 + 0.02; pz = baseZ + off; }
    if (side === 'back')  { pz = baseZ + SD / 2 - pd / 2 - 0.02; px = baseX + off; }
    if (side === 'island'){ px = off * 1.8; pz = ISO_Z; }

    const m = new THREE.Mesh(new THREE.BoxGeometry(pw, ph, pd), pmat);
    m.position.set(px, py, pz);
    m.castShadow = true;
    m.userData = { product: p };
    scene.add(m);
  }
}

// ============================================================
//  WALL PRODUCT PANELS — real product images floating on all walls
//  ALL products from DB cycle across the walls. Click any panel
//  to see product card and add to cart.
// ============================================================
const wallTexLoader = new THREE.TextureLoader();
const wallPanelMeshes = [];
// Raycaster for clicking wall panels
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
let wallClickHandler = null;

// Create a text sprite (name label) for a product panel
function makeTextSprite(name, color = '#ffffff') {
  const canvas = document.createElement('canvas');
  canvas.width = 256;
  canvas.height = 64;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = 'rgba(0,0,0,0.55)';
  ctx.roundRect(0, 0, 256, 64, 8);
  ctx.fill();
  ctx.fillStyle = color;
  ctx.font = 'bold 20px Cairo, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  // Truncate long names
  const displayName = name.length > 18 ? name.slice(0, 16) + '..' : name;
  ctx.fillText(displayName, 128, 34);
  const tex = new THREE.CanvasTexture(canvas);
  tex.needsUpdate = true;
  const spriteMat = new THREE.SpriteMaterial({ map: tex, transparent: true, depthTest: false });
  const sprite = new THREE.Sprite(spriteMat);
  sprite.scale.set(1.0, 0.25, 1);
  sprite.userData = { isSprite: true };
  sprite.isSprite = true;
  return sprite;
}

function wallPanel(p, x, y, z, rotY, scaleW, scaleH) {
  const w = scaleW || 0.42, h = scaleH || 0.56;
  let mat;
  if (p.image) {
    const tx = wallTexLoader.load(p.image);
    tx.encoding = THREE.sRGBEncoding;
    tx.minFilter = THREE.LinearFilter;
    tx.magFilter = THREE.LinearFilter;
    mat = new THREE.MeshBasicMaterial({ map: tx, transparent: true, side: THREE.DoubleSide, depthWrite: false });
  } else {
    mat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(p.color || 0x8B5CF6),
      roughness: 0.5, metalness: 0.1,
      emissive: new THREE.Color(p.color || 0x8B5CF6),
      emissiveIntensity: 0.15,
    });
  }
  const mesh = new THREE.Mesh(new THREE.PlaneGeometry(w, h), mat);
  mesh.position.set(x, y + h / 2, z);
  mesh.rotation.y = rotY;
  mesh.userData = { product: p, isWallPanel: true };
  scene.add(mesh);
  wallPanelMeshes.push(mesh);

  // Add name label below the panel
  const label = makeTextSprite(p.name || 'منتج', '#ffffff');
  label.position.set(x, y - 0.05, z);
  label.renderOrder = 999;
  scene.add(label);
  wallPanelMeshes.push(label);

  return mesh;
}

// Carousel offset — increments every few seconds to show different products
let carouselOffset = 0;
const CAROUSEL_INTERVAL = 6000; // 6 seconds

function cycleWallDisplay() {
  carouselOffset++;
  rebuildWallPanels();
}

function clearWallPanels() {
  for (const m of wallPanelMeshes) {
    scene.remove(m);
    if (m.material.map) m.material.map.dispose();
    m.material.dispose();
  }
  wallPanelMeshes.length = 0;
}

function rebuildWallPanels() {
  clearWallPanels();
  // Show more panels per zone: 2 vertical rows × 3 horizontal = 6 per zone
  const PANELS_PER_ZONE = 3;
  const ROWS = 2;
  const ROW_HEIGHTS = [0.8, 2.2]; // low and mid row

  // Build a single flat array of all products across all zones for cycling
  const allProducts = Object.values(PRODS).flat().filter(Boolean);
  if (!allProducts.length) return;

  let globalIdx = 0;

  // Left wall — 5 zones
  for (let u = 0; u < 5; u++) {
    const z = -D / 2 + 2.0 + u * 4.0;
    for (let r = 0; r < ROWS; r++) {
      for (let s = 0; s < PANELS_PER_ZONE; s++) {
        const idx = (globalIdx + carouselOffset) % allProducts.length;
        const p = allProducts[idx];
        if (!p) continue;
        globalIdx++;
        const yy = ROW_HEIGHTS[r];
        const rot = Math.PI / 2;
        wallPanel(p, LX + 0.25, yy, z + (s - 1) * 0.35, rot);
      }
    }
  }

  // Right wall — 5 zones
  for (let u = 0; u < 5; u++) {
    const z = -D / 2 + 2.0 + u * 4.0;
    for (let r = 0; r < ROWS; r++) {
      for (let s = 0; s < PANELS_PER_ZONE; s++) {
        const idx = (globalIdx + carouselOffset) % allProducts.length;
        const p = allProducts[idx];
        if (!p) continue;
        globalIdx++;
        const yy = ROW_HEIGHTS[r];
        const rot = -Math.PI / 2;
        wallPanel(p, RX - 0.25, yy, z + (s - 1) * 0.35, rot);
      }
    }
  }

  // Back wall — 4 zones × 2 rows × 3 cols = 24 panels
  for (let u = 0; u < 4; u++) {
    const x = -W / 2 + 2.0 + u * 4.0;
    for (let r = 0; r < ROWS; r++) {
      for (let s = 0; s < PANELS_PER_ZONE; s++) {
        const idx = (globalIdx + carouselOffset) % allProducts.length;
        const p = allProducts[idx];
        if (!p) continue;
        globalIdx++;
        const yy = ROW_HEIGHTS[r];
        wallPanel(p, x + (s - 1) * 0.35, yy, BZ + 0.25, 0);
      }
    }
  }

  // Island
  placeIslandProducts();
}

// ——— Click handler for wall panels via raycaster ———
function handleWallClick(event) {
  if (!gameOn) return;
  const rect = renderer.domElement.getBoundingClientRect();
  mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
  mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
  raycaster.setFromCamera(mouse, camera);
  const clickTargets = wallPanelMeshes.filter(m => !m.isSprite).concat(islandMeshes);
  const intersects = raycaster.intersectObjects(clickTargets);
  if (intersects.length > 0) {
    const hit = intersects[0].object;
    const p = hit.userData?.product;
    if (p) {
      showCardForProduct(p);
    }
  }
}

// Show card for a specific product (not zone-based)


// جزيرة — ألواح صور المنتجات الحقيقية واقفة على الطاولة المركزية
function islandPanel(p, x, y, z, rotY) {
  const w = 0.42, h = 0.56;
  let mat;
  if (p.image) {
    const tx = wallTexLoader.load(p.image);
    tx.encoding = THREE.sRGBEncoding;
    tx.minFilter = THREE.LinearFilter;
    tx.magFilter = THREE.LinearFilter;
    mat = new THREE.MeshBasicMaterial({ map: tx, transparent: true, side: THREE.DoubleSide });
  } else {
    mat = new THREE.MeshStandardMaterial({ color: new THREE.Color(p.color || 0xdddddd), roughness: 0.5, metalness: 0.1 });
  }
  const mesh = new THREE.Mesh(new THREE.PlaneGeometry(w, h), mat);
  mesh.position.set(x, y + h / 2, z);
  mesh.rotation.y = rotY;
  mesh.userData = { product: p };
  scene.add(mesh);

  const label = makeTextSprite(p.name || 'منتج', '#ffffff');
  label.position.set(x, y - 0.05, z);
  label.renderOrder = 999;
  scene.add(label);
  islandMeshes.push(label);

  return mesh;
}

let islandMeshes = [];
function placeIslandProducts() {
  for (const m of islandMeshes) {
    scene.remove(m);
    if (m.material.map) m.material.map.dispose();
    m.material.dispose();
  }
  islandMeshes = [];

  const allItems = Object.values(PRODS).flat().filter(Boolean);
  if (!allItems.length) return;
  const topY = 1.45 + 0.02;
  const visible = Math.min(10, allItems.length);
  const selected = [];
  for (let i = 0; i < visible; i++) {
    selected.push(allItems[(i + carouselOffset) % allItems.length]);
  }
  const half = Math.ceil(selected.length / 2);
  const front = selected.slice(0, half);
  const back = selected.slice(half);
  const spanFor = n => (i) => n > 1 ? (i - (n - 1) / 2) * (1.7 / (n - 1)) : 0;
  const fx = spanFor(front.length);
  front.forEach((p, i) => {
    const m = islandPanel(p, fx(i), topY, ISO_Z + 0.42, 0);
    if (m) islandMeshes.push(m);
  });
  const bx = spanFor(back.length);
  back.forEach((p, i) => {
    const m = islandPanel(p, bx(i), topY, ISO_Z - 0.42, Math.PI);
    if (m) islandMeshes.push(m);
  });
}

// Initial build
rebuildWallPanels();

// Start carousel cycling
setInterval(cycleWallDisplay, CAROUSEL_INTERVAL);

// ============================================================
//  FPS CONTROLS
// ============================================================
const keys = {};
let yaw = 0, pitch = 0;
let locked = false, drag = false, mx0 = 0, my0 = 0;

canvas.addEventListener('click', (event) => {
  // First try wall panel click (only when not locked)
  if (!document.pointerLockElement) {
    handleWallClick(event);
  }
  // Then request pointer lock
  if (gameOn) canvas.requestPointerLock();
});
document.addEventListener('pointerlockchange', () => { locked = !!document.pointerLockElement; });
document.addEventListener('mousemove', e => {
  if (locked)        { yaw -= e.movementX * LSPD; pitch -= e.movementY * LSPD; }
  else if (drag && gameOn) {
    yaw -= (e.clientX - mx0) * LSPD * 1.6;
    pitch -= (e.clientY - my0) * LSPD * 1.6;
    mx0 = e.clientX; my0 = e.clientY;
  }
  pitch = Math.max(-1.18, Math.min(1.18, pitch));
});
canvas.addEventListener('mousedown', e => { if (!locked && gameOn) { drag = true; mx0 = e.clientX; my0 = e.clientY; } });
document.addEventListener('mouseup', () => { drag = false; });
document.addEventListener('keydown', e => { keys[e.code] = true; });
document.addEventListener('keyup',   e => { keys[e.code] = false; });

// Mobile
const mk = { u:false, d:false, l:false, r:false, rl:false, rr:false };
function mob(id, k) {
  const el = document.getElementById(id);
  ['touchstart','mousedown'].forEach(ev => el.addEventListener(ev, e => { e.preventDefault(); mk[k] = true; el.classList.add('on'); }, { passive: false }));
  ['touchend','mouseup'].forEach(ev => el.addEventListener(ev, () => { mk[k] = false; el.classList.remove('on'); }));
}
mob('dU','u'); mob('dD','d'); mob('dL','l'); mob('dR','r');
mob('rL','rl'); mob('rR','rr');

// Touch look
let tx0 = 0, ty0 = 0;
canvas.addEventListener('touchstart', e => { tx0 = e.touches[0].clientX; ty0 = e.touches[0].clientY; }, { passive: true });
canvas.addEventListener('touchmove', e => {
  if (gameOn) {
    yaw   -= (e.touches[0].clientX - tx0) * 0.003;
    pitch -= (e.touches[0].clientY - ty0) * 0.003;
    pitch = Math.max(-1.18, Math.min(1.18, pitch));
    tx0 = e.touches[0].clientX; ty0 = e.touches[0].clientY;
  }
}, { passive: true });

function isMobile() { return window.innerWidth <= 768 || 'ontouchstart' in window; }

// ============================================================
//  COLLISION
// ============================================================
//  عرض 4م = ±8 وحدة على X  |  عمق 5م = ±10 وحدة على Z  (الوحدة = 25سم)
const CM = 0.38;
const hW = W / 2 - CM;  // 7.62
const hD = D / 2 - CM;  // 9.62

const colliders = [
  // يسار (X ≈ -1.83 → الممر عند X=-1.52)
  { xn: LX + SD / 2 + CM * 0.3, xx: W, zn: -D / 2, zx: D / 2 },
  // يمين (X ≈ +1.83 → الممر عند X=+1.52)
  { xn: -W, xx: RX - SD / 2 - CM * 0.3, zn: -D / 2, zx: D / 2 },
  // خلف (Z ≈ -2.33)
  { xn: -W / 2, xx: W / 2, zn: BZ - SD / 2 - CM * 0.3, zx: D / 2 },
  // جزيرة
  { xn: -1.08, xx: 1.08, zn: -1.08, zx: 0.08 },
].map(c => ({ xMin: -c.xx, xMax: -c.xn, zMin: c.zn, zMax: c.zx }));

// تبسيط: استخدم حدود مباشرة
const WALLS = [
  { xMin: -Infinity, xMax: -(W/2 - SD - CM * 0.6), zMin: -D/2, zMax: D/2 },  // يسار
  { xMin:  (W/2 - SD - CM * 0.6), xMax: Infinity,  zMin: -D/2, zMax: D/2 },  // يمين
  { xMin: -W/2, xMax: W/2, zMin: -Infinity, zMax: -(D/2 - SD - CM * 0.6) }, // خلف
  { xMin: -1.1, xMax:  1.1, zMin: -1.1, zMax: 0.1 },  // جزيرة
];

function clamp(pos) {
  // حدود الجدران الخارجية
  pos.x = Math.max(-hW, Math.min(hW, pos.x));
  pos.z = Math.max(-hD, Math.min(hD, pos.z));
  // الرفوف والجزيرة
  for (const w of WALLS) {
    if (pos.x > w.xMin && pos.x < w.xMax && pos.z > w.zMin && pos.z < w.zMax) {
      const dl = Math.abs(pos.x - w.xMin), dr = Math.abs(pos.x - w.xMax);
      const dt = Math.abs(pos.z - w.zMin), db = Math.abs(pos.z - w.zMax);
      const mn = Math.min(dl, dr, dt, db);
      if (mn === dl) pos.x = w.xMin - 0.01;
      else if (mn === dr) pos.x = w.xMax + 0.01;
      else if (mn === dt) pos.z = w.zMin - 0.01;
      else pos.z = w.zMax + 0.01;
    }
  }
}

// ============================================================
//  PROXIMITY & PRODUCT CARD
// ============================================================
let curProd = null, cardShowing = false;
const card = document.getElementById('prodCard');
const zoneLbl = document.getElementById('zoneLabel');

function checkProx() {
  const cx = camera.position.x, cz = camera.position.z;
  let nearMesh = null, nearD = PROX;
  // Check proximity to each wall panel mesh (skip sprites)
  for (const m of wallPanelMeshes) {
    if (!m.userData?.product || m.isSprite) continue;
    const d = Math.hypot(cx - m.position.x, cz - m.position.z);
    if (d < nearD) { nearD = d; nearMesh = m; }
  }
  const ring = document.getElementById('proxRing');
  if (nearMesh && nearD < PROX * 0.65) {
    ring.style.opacity = '0';
    const p = nearMesh.userData.product;
    if (p) showCardForProduct(p);
    showZone('🛒 اقتربت من منتج — اضغط لإضافة للسلة');
  } else if (nearMesh && nearD < PROX) {
    ring.style.opacity = '1';
    hideCard();
  } else {
    ring.style.opacity = '0';
    hideCard();
    zoneLbl.style.opacity = '0';
  }
}

function showCardForProduct(p) {
  if (!p) return;
  if (p === curProd && cardShowing) return;
  curProd = p; cardShowing = true;
  const imgEl = document.getElementById('pEmoji');
  if (p.image) {
    imgEl.textContent = '';
    imgEl.style.backgroundImage = `url('${p.image}')`;
    imgEl.style.backgroundSize = 'contain';
    imgEl.style.backgroundRepeat = 'no-repeat';
    imgEl.style.backgroundPosition = 'center';
  } else {
    imgEl.style.backgroundImage = '';
    imgEl.textContent = p.emoji || (p.name ? p.name[0] : '🛒');
  }
  document.getElementById('pZone').textContent  = p.zone || 'منتج';
  document.getElementById('pName').textContent  = p.name;
  document.getElementById('pPrice').textContent = p.price;
  document.getElementById('pOld').textContent   = p.old ? `${p.old}₪` : '';
  document.getElementById('viewBtn').href = `/product/${p.slug}`;
  card.dataset.pid = p.id;
  card.style.display = 'block';
  setTimeout(() => card.classList.add('show'), 10);
}

function hideCard() {
  if (!cardShowing) return;
  cardShowing = false; curProd = null;
  card.classList.remove('show');
  setTimeout(() => { if (!cardShowing) card.style.display = 'none'; }, 420);
}

function showZone(txt) {
  zoneLbl.textContent = txt;
  zoneLbl.style.opacity = '1';
}

// ============================================================
//  CART
// ============================================================
window.doAddToCart = function () {
  const pid = card.dataset.pid;
  if (!pid) return;
  const btn = document.getElementById('addBtn');
  btn.classList.add('busy');
  btn.textContent = '⏳ جاري...';
  const csrf = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
  fetch('/cart/add', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ product_id: pid, quantity: 1 }),
  })
  .then(r => r.json())
  .then(d => {
    btn.classList.remove('busy');
    btn.textContent = '✅ تمت الإضافة!';
    setTimeout(() => { btn.textContent = '🛒 أضف للسلة'; }, 2200);
    if (d.cart_count !== undefined)
      document.getElementById('cartCount').textContent = d.cart_count;
    const t = document.getElementById('toast');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
    // نبضة على أيقونة السلة
    const cb = document.getElementById('cartBtn');
    cb.style.transform = 'scale(1.28)';
    setTimeout(() => cb.style.transform = '', 300);
  })
  .catch(() => {
    btn.classList.remove('busy');
    btn.textContent = '🛒 أضف للسلة';
  });
};

// ============================================================
//  MINI-MAP
// ============================================================
const mc = document.getElementById('mmapCanvas');
const mx = mc.getContext('2d');
// نسبة: عرض 4م → 116px | عمق 5م → 96px
const scX = mc.width  / W;  // 29
const scZ = mc.height / D;  // 19.2

function drawMap() {
  mx.clearRect(0, 0, mc.width, mc.height);
  const cw = mc.width, ch = mc.height;
  // خلفية
  mx.fillStyle = 'rgba(0,0,0,0.82)'; mx.fillRect(0, 0, cw, ch);
  // حدود الغرفة
  mx.strokeStyle = 'rgba(255,255,255,0.45)'; mx.lineWidth = 1.5;
  mx.strokeRect(3, 3, cw - 6, ch - 6);
  // رفوف
  mx.fillStyle = 'rgba(110,110,140,0.75)';
  mx.fillRect(3, 3, 7, ch - 6);          // يسار
  mx.fillRect(cw-10, 3, 7, ch - 6);      // يمين
  mx.fillRect(3, 3, cw - 6, 7);          // خلف
  // جزيرة
  const ix = cw/2 - 30, iz = ch/2 - 8;
  mx.fillRect(ix, iz, 60, 16);
  // الكاميرا
  const px = (camera.position.x + W/2) * scX;
  const pz = (camera.position.z + D/2) * scZ;
  // مخروط الرؤية
  const a = yaw + Math.PI / 2;
  mx.beginPath();
  mx.moveTo(px, pz);
  mx.arc(px, pz, 12, a - 0.45, a + 0.45);
  mx.fillStyle = 'rgba(233,30,99,0.25)'; mx.fill();
  // النقطة
  mx.beginPath();
  mx.arc(px, pz, 4, 0, Math.PI * 2);
  mx.fillStyle = '#e91e63'; mx.fill();
  mx.strokeStyle = '#fff'; mx.lineWidth = 1; mx.stroke();
}

// ============================================================
//  ANIMATION LOOP
// ============================================================
let gameOn = false;

function animate() {
  requestAnimationFrame(animate);
  if (gameOn) {
    const fwd   = new THREE.Vector3(-Math.sin(yaw), 0, -Math.cos(yaw));
    const right = new THREE.Vector3(-Math.cos(yaw), 0,  Math.sin(yaw));
    const vel   = new THREE.Vector3();
    const sp    = SPEED * (isMobile() ? 1.35 : 1);

    if (keys['KeyW'] || keys['ArrowUp']    || mk.u)  vel.addScaledVector(fwd,   sp);
    if (keys['KeyS'] || keys['ArrowDown']  || mk.d)  vel.addScaledVector(fwd,  -sp);
    if (keys['KeyA'] || keys['ArrowLeft']  || mk.l)  vel.addScaledVector(right, -sp);
    if (keys['KeyD'] || keys['ArrowRight'] || mk.r)  vel.addScaledVector(right,  sp);
    if (mk.rl) yaw += 0.036;
    if (mk.rr) yaw -= 0.036;

    const np = camera.position.clone().add(vel);
    clamp(np);
    camera.position.x = np.x;
    camera.position.z = np.z;
    camera.position.y = CAMH;
    camera.rotation.y = yaw;
    camera.rotation.x = pitch;

    checkProx();
    drawMap();
  }
  renderer.render(scene, camera);
}
animate();

// ============================================================
//  START & LOAD SEQUENCE
// ============================================================
document.getElementById('enterBtn').addEventListener('click', () => {
  const ss = document.getElementById('startScreen');
  const ls = document.getElementById('loadScreen');
  ss.style.transition = 'opacity .45s';
  ss.style.opacity = '0';
  setTimeout(() => { ss.style.display = 'none'; ls.style.display = 'flex'; }, 450);

  const bar = document.getElementById('loadBar');
  const txt = document.getElementById('loadTxt');
  const steps = [
    [20, 'تحضير الغرفة...'],
    [40, 'بناء الرفوف...'],
    [65, 'وضع المنتجات...'],
    [85, 'تجهيز الإضاءة...'],
    [100, 'جاهز!'],
  ];
  let i = 0;
  const iv = setInterval(() => {
    if (i < steps.length) {
      bar.style.width = steps[i][0] + '%';
      txt.textContent = steps[i][1];
      i++;
    } else {
      clearInterval(iv);
      setTimeout(() => {
        ls.style.display = 'none';
        document.getElementById('hud').style.display = 'block';
        card.style.display = 'none';
        document.getElementById('mob').style.display = isMobile() ? 'block' : 'none';
        if (!isMobile()) document.getElementById('hint').style.display = 'flex';
        gameOn = true;
        showZone('🚪 مرحباً! تجوّل بحرية — اقترب من الرفوف لمشاهدة المنتجات');
        setTimeout(() => { zoneLbl.style.opacity = '0'; }, 3500);
        // عداد السلة
        fetch('/cart/count', { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
          .then(r => r.json())
          .then(d => { if (d.cart_count !== undefined) document.getElementById('cartCount').textContent = d.cart_count; })
          .catch(() => {});
      }, 350);
    }
  }, 280);
});

// ESC
document.addEventListener('keydown', e => {
  if (e.code === 'Escape' && document.pointerLockElement) document.exitPointerLock();
});

// Resize
window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});

} // end initApp
</script>
</body>
</html>
