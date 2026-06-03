# SkinAnalyzer System Architecture

## High-Level Overview

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                              JENIN CARE — SKINANALYZER                               │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                      │
│  ┌─────────────────┐     ┌────────────────────┐     ┌─────────────────────────────┐ │
│  │   Android App   │────▶│   Laravel API       │────▶│   Admin Panel (Vue.js)     │ │
│  │   (Kotlin +     │     │   (PHP 8.2 / 10.x) │     │                             │ │
│  │   Jetpack       │     │                     │     │  ┌───────────────────────┐  │ │
│  │   Compose)      │◀────│   Hostinger Shared  │◀────│  │ Scan Monitor          │  │ │
│  │                 │     │   Hosting           │     │  │ AI Provider Settings  │  │ │
│  │  • CameraX      │     │                     │     │  │ LLM Prompt Manager    │  │ │
│  │  • TFLite       │     │  • REST API v1      │     │  │ White-Label Config    │  │ │
│  │  • WorkManager  │     │  • Sanctum Auth     │     │  │ Dashboard Analytics   │  │ │
│  │  • Hilt DI      │     │  • Redis Cache      │     │  └───────────────────────┘  │ │
│  │  • ViewModel/MVVM│    │  • MySQL 8.0        │     │                             │ │
│  └─────────────────┘     │  • Pusher WebSockets│     └─────────────────────────────┘ │
│                          └─────────┬──────────┘                                      │
│                                    │                                                  │
│                          ┌─────────▼──────────┐                                      │
│                          │   AI Provider Layer │                                      │
│                          │   (Strategy Pattern)│                                      │
│                          ├────────────────────┤                                      │
│                          │                    │                                      │
│                  ┌───────┼────────────────────┼──────────┐                          │
│                  │       │                    │          │                          │
│           ┌──────▼──┐ ┌──▼──────┐ ┌──────────▼──┐ ┌─────▼──────┐                   │
│           │ Native  │ │ Yimei AI │ │  OpenAI     │ │  Claude    │                   │
│           │ Engine  │ │(Structured│ │  GPT-4V    │ │  Opus      │                   │
│           │(Offline)│ │  Cloud)  │ │ (Generative)│ │(Generative)│                   │
│           └─────────┘ └─────────┘ └─────────────┘ └────────────┘                   │
│                                                                                      │
│           ┌──────────┐ ┌─────────┐ ┌─────────────┐ ┌─────────────┐                  │
│           │ Gemini   │ │ Haut.AI │ │ PerfectCorp │ │  Skinive    │                  │
│           │ (Hybrid) │ │(Struct.)│ │  (Hybrid)   │ │ (Structured)│                  │
│           └──────────┘ └─────────┘ └─────────────┘ └─────────────┘                  │
│                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Component Breakdown

### 1. Android Application (`android-app/`)

**Language:** Kotlin  
**Architecture:** MVVM with Clean Architecture  
**UI Framework:** Jetpack Compose (primary) + View Binding (legacy)  
**Min SDK:** 24 | **Target SDK:** 34

#### Key Modules

| Module | Package | Responsibility |
|--------|---------|---------------|
| Camera | `ui.camera` | CameraX-based face capture with TFLite pre-processing |
| Scan Flow | `ui.scan` | Upload workflow, chunked upload, progress tracking |
| Report | `ui.report` | Interactive report with radar chart, heatmap overlay |
| Timeline | `ui.timeline` | Historical scan comparison and progress tracking |
| Auth | `data.auth` | Sanctum token management, auto-refresh |
| Network | `data.network` | Retrofit2 + OkHttp, network state monitoring |
| DI | `di` | Hilt dependency injection modules |
| Domain | `domain` | Use cases, repository interfaces, domain models |

#### Data Flow

```
Camera Capture → TFLite Pre-check → AES Encryption → Chunked Upload (WorkManager)
    → Laravel API → AI Processing → Admin Approval → WebSocket Notification
    → App receives report → Jetpack Compose UI rendering
```

#### Key Dependencies

| Library | Version | Purpose |
|---------|---------|---------|
| Jetpack Compose BOM | 2024.01 | Modern declarative UI |
| CameraX | 1.3.1 | Camera interface |
| TensorFlow Lite | 2.14.0 | On-device face detection |
| Hilt | 2.50 | Dependency injection |
| Retrofit2 | 2.9.0 | HTTP client |
| WorkManager | 2.9.0 | Background upload processing |
| Coil | 2.5.0 | Image loading and caching |
| Security Crypto | 1.1.0 | Local file encryption |
| Firebase | 32.7.0 | Crashlytics, Analytics, FCM |

---

### 2. Laravel API Backend (`backend/`)

**Language:** PHP 8.2  
**Framework:** Laravel 10.x  
**Architecture:** Monolithic REST API with Service Layer

#### Directory Structure

```
backend/
├── app/
│   ├── Console/         # Artisan commands (pin cleanup, quota reset)
│   ├── Enums/           # AnalysisStatus, EngineType
│   ├── Events/          # ScanApproved, ScanRejected, PinGenerated
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/   # Dashboard, ScanApproval, AIProvider, Prompt, WhiteLabel
│   │   │   └── Api/V1/  # Scan, Product, Cart, Profile, Device
│   │   ├── Middleware/   # AdminAuth, ForceJsonResponse, RateLimiting
│   │   ├── Requests/     # Form request validation
│   │   └── Resources/    # API resource transformers
│   ├── Jobs/            # ProcessScanJob, NotifyScanResultJob
│   ├── Listeners/        # Event listeners for notifications
│   ├── Models/           # User, SkinAnalysis, AIProvider, Product, etc.
│   ├── Notifications/    # Push notification channels
│   ├── Providers/        # AppServiceProvider, SkinAnalyzerServiceProvider
│   └── Services/
│       ├── AI/           # Strategy pattern AI provider layer
│       │   ├── AIProviderInterface.php
│       │   ├── BaseAIProvider.php
│       │   ├── UnifiedSkinData.php
│       │   └── Providers/  # Yimei, OpenAI, Claude, Gemini, etc.
│       └── RecommendationEngine.php  # Product recommendation engine
├── config/
│   ├── services.php      # All AI provider credentials
│   └── skinanalyzer.php  # Platform configuration
├── database/
│   ├── migrations/       # Schema definitions
│   └── seeders/          # DatabaseSeeder, AIProviderSeeder
└── routes/
    ├── api.php           # Client-facing API v1
    └── admin.php         # Admin panel routes
```

#### Core Database Schema

```
┌──────────────┐     ┌─────────────────────┐     ┌──────────────┐
│    users     │     │   skin_analyses     │     │ ai_providers │
├──────────────┤     ├─────────────────────┤     ├──────────────┤
│ id           │◀────│ user_id (FK)        │     │ id           │
│ name         │     │ ai_provider_id (FK) │────▶│ name         │
│ email        │     │ image_path          │     │ driver_key   │
│ password     │     │ status              │     │ engine_type  │
│ phone        │     │ overall_health_score│     │ api_creds    │
│ is_admin     │     │ radar_metrics (JSON)│     │ is_active    │
│ device_id    │     │ heatmap_coords(JSON)│     │ quota_limit  │
└──────────────┘     │ custom_arabic_analysis│   │ quota_used   │
                     │ expert_free_tips(JSON)│   └──────────────┘
                     │ raw_vendor_response   │
                     │ approved_at           │
                     └───────┬───────────────┘
                             │
              ┌──────────────┼───────────────┐
              │              │               │
     ┌────────▼──────┐ ┌────▼──────────────┐ ┌──────────────┐
     │skin_analysis_ │ │skin_analysis_     │ │  products    │
     │  pins         │ │  products         │ │              │
     ├───────────────┤ ├───────────────────┤ ├──────────────┤
     │skin_analysis_id││skin_analysis_id   │ │ id           │
     │pin_code (4)   │ │ product_id (FK)───│▶│ name         │
     │is_used        │ │ matching_reason   │ │ name_ar      │
     │expires_at     │ └───────────────────┘ │ description  │
     └───────────────┘                       │ price        │
                                             │ image_path   │
                                             │ stock        │
                                             │ category     │
                                             └──────────────┘
```

---

### 3. Admin Panel (`admin-panel/`)

**Language:** JavaScript (Vue 3 Composition API)  
**Build Tool:** Vite 5  
**State Management:** Pinia

#### Page Structure

```
/admin/
├── /login              # Admin authentication
├── /dashboard          # Analytics overview
├── /scans              # Scan monitoring (real-time)
│   ├── /pending         # Pending review queue
│   ├── /approved        # Approved scans
│   └── /:id             # Scan detail + approve/reject/PIN
├── /ai-providers       # AI provider management
├── /prompts            # LLM system prompt editor
├── /white-label        # Branding configuration
└── /settings           # General settings
```

#### Key Dependencies

| Package | Purpose |
|---------|---------|
| Vue 3.4 | Reactive UI framework |
| Vue Router 4 | SPA routing |
| Pinia 2.1 | State management |
| Axios 1.6 | HTTP client |
| ECharts 5.4 | Dashboard charts |
| Chart.js 4.4 | Statistical visualizations |
| Socket.io Client 4.7 | Real-time scan updates |
| SweetAlert2 11.10 | Alert dialogs |
| Day.js 1.11 | Date formatting |

---

### 4. AI Provider Layer

Implements the **Strategy Design Pattern** for vendor-agnostic AI integration.

#### Architecture

```
                    ┌───────────────────────┐
                    │  AIProviderInterface   │  ← Contract
                    │  + analyze(image): USD │
                    └──────────┬────────────┘
                               │
              ┌────────────────┼──────────────────┐
              │                │                  │
     ┌────────▼────┐  ┌───────▼──────┐  ┌────────▼─────┐
     │BaseAIProvider│  │YimeiProvider │  │OpenAIProvider│
     │ (abstract)   │  │ (structured) │  │ (generative) │
     └─────────────┘  └──────────────┘  └──────────────┘
```

#### Unified Data Model (`UnifiedSkinData`)

Normalizes disparate AI provider outputs into a consistent schema:

```php
UnifiedSkinData {
    overall_health_score: int (0-100)
    radar_metrics: { hydration, sebum, pigmentation, pores, elasticity }
    heatmap: [{ x, y, type, severity }]
    defects: [string]
    arabic_analysis: string|null
    tips: [string]
    raw_response: array
}
```

#### AI Provider Selection

```
1. Check admin panel active provider
2. Fall back to config(skinanalyzer.default_ai_provider)
3. Fall back to Native Engine (offline capable)
4. Emergency failover if quota exceeded
```

---

## Data Flow: Scan Processing

```
                     Android App                   Laravel Backend          AI Provider
                         │                             │                       │
   1. Capture Photo      │                             │                       │
         │               │                             │                       │
   2. TFLite Pre-check───│                             │                       │
         │               │                             │                       │
   3. AES-256 Encrypt    │                             │                       │
         │               │                             │                       │
   4. Chunk Upload───────│──── POST /api/v1/scans ────▶│                       │
         │               │   (multipart, 10MB max)     │                       │
         │               │                             │ 5. Save encrypted     │
         │               │                             │    image to disk      │
         │               │                             │                       │
         │               │                             │ 6. Create SkinAnalysis │
         │               │                             │    record (pending)   │
         │               │                             │                       │
         │               │                             │ 7. Dispatch            │
         │               │                             │    ProcessScanJob ────▶│
         │               │                             │                       │
         │               │         ◀──── Result ──────│ 8. AI analysis        │
         │               │                             │                       │
         │               │ 9. Normalize to             │                       │
         │               │    UnifiedSkinData          │                       │
         │               │                             │                       │
         │               │ 10. Update SkinAnalysis     │                       │
         │               │     (stores results,        │                       │
         │               │      status stays pending)  │                       │
         │               │                             │                       │
   11. Waiting screen────│─── GET /api/v1/scans/{id}──▶│                       │
         │               │    ◀── { status: "pending", │                       │
         │               │          locked: true }     │                       │
         │               │                             │                       │
                          ADMIN APPROVAL PATH:         │                       │
                                                    │                       │
                          Admin reviews scan in       │                       │
                          dashboard                   │                       │
                               │                     │                       │
                          ┌────┴────┐                 │                       │
                          │         │                 │                       │
                     Path 1       Path 2              │                       │
                     (Approve)    (Generate PIN)      │                       │
                          │         │                 │                       │
                          │    POST /admin/scans/     │                       │
                          │    {id}/generate-pin ────▶│                       │
                          │         │                 │                       │
                          │    ◀── { pin: "4829" }    │ 12. Generate 4-digit  │
                          │         │                 │     PIN, store in     │
                          │         │                 │     skin_analysis_pins│
                          │         │                 │                       │
                          │    Client enters PIN      │                       │
                          │         │                 │                       │
                          │    POST /api/v1/scans/    │                       │
                          │    {id}/unlock ──────────▶│ 13. Validate PIN      │
                          │         │                 │                       │
                          │         │                 │ 14. Mark as approved  │
                          │         │                 │     approved_at = now │
                          │         │                 │                       │
                          │         ◀─── Full Report──│ 15. Recommendation    │
                          │    POST /admin/scans/     │     Engine matches    │
                          │    {id}/approve ─────────▶│     products to       │
                          │         │                 │     detected defects  │
                          │         │                 │                       │
   17. Receive report─────│◀── WebSocket Notification─│ 16. Broadcast event   │
         │               │    scan.approved           │     via Pusher        │
   18. Render Radar       │                             │                       │
       Chart, Heatmap,    │                             │                       │
       Products, Tips     │                             │                       │
```

---

## Security Model

### Network Security

```
┌────────────────┐     HTTPS/TLS 1.3     ┌───────────────┐
│  Android App   │◀─────────────────────▶│ Laravel API   │
│                │   Certificate Pinning │               │
│  • AES-256     │                       │  • Sanctum    │
│    local enc.  │                       │    tokens     │
│  • Keystore    │                       │  • CSRF       │
│    for tokens  │                       │    protection │
└────────────────┘                       │  • Rate       │
                                          │    limiting   │
                                          └───────────────┘
```

### Authentication Flow

1. **App Authentication:** Username/password → receives Sanctum token
2. **Token Storage:** Android Keystore encrypted DataStore
3. **Token Refresh:** Auto-refresh before expiry (configurable TTL)
4. **Admin Authentication:** Separate guard, admin role check
5. **API Authentication:** Bearer token in Authorization header

### Data Security

| Layer | Method |
|-------|--------|
| Images at rest | AES-256-GCM encryption on disk |
| Images in transit | TLS 1.3 (HTTPS only) |
| API credentials | Laravel encrypted casting (`encrypted:array`) |
| Tokens | Hashed with SHA-256 in database |
| PIN codes | Hashed with Bcrypt before storage |
| APK signing | RSA 2048-bit release keystore |
| Backup | `android:allowBackup="false"` + data extraction rules |

### Network Policy

- `android:usesCleartextTraffic="false"` — no HTTP allowed
- `network_security_config.xml` — domain pinning for `jenincare.shop`
- Certificate Transparency enforced

---

## Deployment Architecture

```
┌───────────────────────────────────────────────────────────────────┐
│                        Hostinger Hosting                           │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────────┐ │
│  │  jenincare.shop (Shared Hosting — Business Plan)              │ │
│  │                                                               │ │
│  │  ┌─────────────┐  ┌──────────────┐  ┌─────────────────────┐ │ │
│  │  │ Laravel API │  │ Admin Panel  │  │ MySQL 8.0 Database  │ │ │
│  │  │ /api/v1/*   │  │ /admin/*     │  │                     │ │ │
│  │  │             │  │ (Vue.js SPA) │  │  • ai_providers     │ │ │
│  │  │  PHP 8.2    │  │              │  │  • skin_analyses    │ │ │
│  │  │  Composer   │  │  Static Files│  │  • products         │ │ │
│  │  │  Redis      │  │  Nginx       │  │  • users            │ │ │
│  │  └──────┬──────┘  └──────────────┘  └─────────────────────┘ │ │
│  │         │                                                    │ │
│  │  ┌──────▼──────────────────────────────────────────────┐    │ │
│  │  │  File Storage (public/storage/)                      │    │ │
│  │  │  • Encrypted scan images                             │    │ │
│  │  │  • Heatmap overlays                                  │    │ │
│  │  │  • Product images                                    │    │ │
│  │  └─────────────────────────────────────────────────────┘    │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌──────────────────────────┐    ┌─────────────────────────────┐ │
│  │  Let's Encrypt SSL       │    │  Cron Jobs                   │ │
│  │  • Auto-renewal          │    │  • Queue worker              │ │
│  │  • HTTPS enforcement     │    │  • PIN cleanup               │ │
│  │  • HSTS headers          │    │  • Quota reset (monthly)     │ │
│  └──────────────────────────┘    └─────────────────────────────┘ │
└───────────────────────────────────────────────────────────────────┘

                        ┌───────────────────┐
                        │   Google Play     │
                        │   Console         │
                        │                   │
                        │  • APK hosting    │
                        │  • Auto-updates   │
                        │  • Crash reports  │
                        │  • Beta testing   │
                        └───────────────────┘

┌───────────────────────────────────────────────────────────────────┐
│                        External Services                           │
│                                                                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐        │
│  │ Yimei AI │  │ OpenAI   │  │ Claude   │  │ Gemini   │        │
│  │ (Cloud)  │  │ (Cloud)  │  │ (Cloud)  │  │ (Cloud)  │        │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘        │
│                                                                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐                       │
│  │ Haut.AI  │  │PerfectCorp│  │ Skinive  │                       │
│  │ (Cloud)  │  │ (Cloud)  │  │ (Cloud)  │                       │
│  └──────────┘  └──────────┘  └──────────┘                       │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │  Pusher Channels (WebSocket notifications)               │    │
│  │  Firebase (Crashlytics, Cloud Messaging)                  │    │
│  └──────────────────────────────────────────────────────────┘    │
└───────────────────────────────────────────────────────────────────┘
```

---

## CI/CD Pipeline

```
┌──────────┐    ┌─────────────────┐    ┌──────────────────┐
│  Push to │    │  GitHub Actions  │    │  Production      │
│  main    │───▶│                  │───▶│                  │
└──────────┘    │  ┌────────────┐  │    │  Hostinger (API) │
                │  │Lint + Test │  │    │  Google Play (APK)│
┌──────────┐    │  └─────┬──────┘  │    │  FTP (Admin)     │
│  Pull    │───▶│        │         │    └──────────────────┘
│  Request │    │  ┌─────▼──────┐  │
└──────────┘    │  │ Build APK  │  │
                │  │ Deploy PHP │  │
                │  │ Audit Sec. │  │
                │  └────────────┘  │
                └─────────────────┘
```

Three workflows automate the pipeline:
1. **android-build.yml** — Builds, signs, and artifacts APK
2. **laravel-deploy.yml** — Tests and deploys to Hostinger
3. **code-quality.yml** — Linting, static analysis, security audit

---

## Technology Stack Summary

| Layer | Technology | Version |
|-------|-----------|---------|
| Mobile App | Kotlin + Jetpack Compose | 1.9.21 / BOM 2024.01 |
| Backend | PHP / Laravel | 8.2 / 10.x |
| Database | MySQL | 8.0 |
| Cache | Redis | 7.x (via Predis) |
| WebSocket | Pusher Channels | 7.x |
| Admin Panel | Vue 3 + Vite | 3.4 / 5.0 |
| AI/ML | TensorFlow Lite | 2.14 |
| CI/CD | GitHub Actions | — |
| Hosting | Hostinger Shared | Business Plan |
| App Store | Google Play Console | — |
| Monitoring | Firebase Crashlytics | 32.7 BOM |
