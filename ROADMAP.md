# JeniCare Ad Tracking & Attribution Platform — Roadmap

> **Mission:** Build the most advanced, AI-powered, server-side ad tracking and attribution platform on the market — surpassing wetracked.io, ServerTrack, and HYROS in accuracy, compliance, and intelligence.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         E-COMMERCE LAYER                                     │
│  WooCommerce │ Shopify │ Custom Laravel │ Headless API │ POS (Offline)       │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │ Webhooks / REST API
┌────────────────────────────▼────────────────────────────────────────────────┐
│                MODULE A: INGESTION & CAPI ENGINE                             │
│  ┌──────────────┐ ┌────────────┐ ┌──────────────┐ ┌──────────────────────┐  │
│  │ Webhook      │ │ Queue      │ │ Dedup &      │ │ Multi-Platform       │  │
│  │ Receiver     │ │ (Redis)    │ │ Validation   │ │ CAPI Dispatcher     │  │
│  └──────────────┘ └────────────┘ └──────────────┘ └──────────────────────┘  │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────────────────┐
│                MODULE B: AI COMPLIANCE GATEWAY                               │
│  ┌──────────────┐ ┌────────────┐ ┌──────────────┐ ┌──────────────────────┐  │
│  │ Multi-LLM    │ │ Trigger    │ │ Value &      │ │ Ad Account           │  │
│  │ Sanitizer    │ │ Word DB    │ │ Junk Filter  │ │ Health Scoring       │  │
│  └──────────────┘ └────────────┘ └──────────────┘ └──────────────────────┘  │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────────────────┐
│                MODULE C: IDENTITY RESOLUTION & ATTRIBUTION                    │
│  ┌──────────────┐ ┌────────────┐ ┌──────────────┐ ┌──────────────────────┐  │
│  │ First-Party  │ │ Server     │ │ Cross-Device  │ │ True ROAS            │  │
│  │ CNAME + FP   │ │ UUID       │ │ Journey Map   │ │ Dashboard            │  │
│  └──────────────┘ └────────────┘ └──────────────┘ └──────────────────────┘  │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────────────────┐
│                MODULE D: OMNICHANNEL & PREDICTIVE AI                         │
│  ┌──────────────┐ ┌────────────┐ ┌──────────────┐                          │
│  │ POS Bridge   │ │ Predictive │ │ CAPI Value    │                          │
│  │ (Offline)    │ │ LTV Model  │ │ Multiplier    │                          │
│  └──────────────┘ └────────────┘ └──────────────┘                          │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────────────────┐
│                MODULE E: SECURITY & DYNAMIC UI                               │
│  ┌──────────────┐ ┌────────────┐ ┌──────────────┐ ┌──────────────────────┐  │
│  │ Behavioral   │ │ Safe Page  │ │ Multi-Pixel  │ │ Dynamic Soft         │  │
│  │ Bot Detection│ │ Routing    │ │ Fan-Out      │ │ CTAs                 │  │
│  └──────────────┘ └────────────┘ └──────────────┘ └──────────────────────┘  │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────────────────┐
│                     AD PLATFORMS (CAPI OUTPUT)                               │
│  Meta │ TikTok │ Google Ads │ Snapchat │ Pinterest │ X (Twitter) │ LinkedIn  │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | Laravel 12 / PHP 8.3 | Core application framework |
| **Async Queue** | Redis 7 + Laravel Horizon | Background CAPI event processing |
| **Database** | PostgreSQL 16 | Relational data (orders, events, users) |
| **Cache** | Redis 7 | Session caching, rate limiting, dedup windows |
| **AI / LLM** | OpenAI GPT-4o + Claude 4 + local LLaMA (Ollama) | Payload sanitization, policy compliance |
| **ML Service** | Python FastAPI + TensorFlow/scikit-learn | LTV prediction microservice |
| **Frontend** | Laravel Blade + Tailwind CSS + Alpine.js | Admin dashboards |
| **SaaS Billing** | Laravel Spark / Cashier + Stripe | Multi-tenant subscriptions |
| **Infrastructure** | Docker + Docker Compose | Development & production deployment |
| **Monitoring** | Laravel Pulse + Sentry + Custom Alerts | Error tracking, queue health, CAPI success rates |
| **Security** | CSP Middleware + HSTS + CNAME Cloaking | Ad-blocker bypass, tracking protection |

---

## Phase 1: Foundation — Multi-Platform CAPI Engine

> **Objective:** Build a complete, production-grade server-side tracking engine that sends conversion events to every major ad platform with deduplication, retry logic, and queue-based async processing.

### 1.1 Complete Facebook CAPI for All Events

| Task | Description | Status |
|------|-------------|--------|
| Add missing event types | `Lead`, `Subscribe`, `Search`, `Contact`, `CustomEvent` | ✅ |
| Proper event_id generation | UUID-based event_id for browser↔server dedup | ✅ |
| Extract fbclid/fbp/fbc | Parse from request, cookies, URL parameters | ✅ |
| Full SHA-256 hashing | email, phone, firstName, lastName, city, country, zip, gender, birthday | ✅ |
| event_source_url mapping | Attach correct URL context per event | ✅ |
| Test event code support | Debug mode via Meta test events | ✅ |
| GDPR opt_out support | Respect user consent signals | ✅ |
| Enhanced error handling | Log full request/response for debugging | ✅ |

**Files:** `app/Services/AdvertisingTrackingService.php` (major rewrite)

### 1.2 Complete TikTok Events API

| Task | Description | Status |
|------|-------------|--------|
| Add missing events | `ViewContent`, `AddToCart`, `InitiateCheckout`, `CompletePayment` | ✅ |
| event_id deduplication | Match browser pixel with server-side events | ✅ |
| User identity hashing | email, phone SHA-256 | ✅ |
| Correct API endpoint | Validate against latest TikTok Events API spec | ✅ |
| Enhanced error handling | Log full request/response | ✅ |
| Test mode support | TikTok debug mode | ✅ |

**Files:** `app/Services/AdvertisingTrackingService.php`

### 1.3 Google Ads Conversion Tracking

| Task | Description | Status |
|------|-------------|--------|
| Create `GoogleAdsService.php` | Dedicated service for Google Ads | ✅ |
| Offline conversion import | Google Ads API `OfflineConversionFeedService` | ✅ |
| Event mapping | `purchase`, `lead`, `add_to_cart`, `sign_up`, `page_view` | ✅ |
| gclid extraction | Parse from URL parameters and cookies | ✅ |
| Google Click ID mapping | Store gclid → conversion associations | ✅ |
| Conversion Adjustments | Send enhanced conversions with user-provided data | ✅ |
| Test mode | Google Ads API test account support | ✅ |

### 1.4 Snapchat Conversions API

| Task | Description | Status |
|------|-------------|--------|
| Create `SnapchatService.php` | Dedicated service for Snapchat | ✅ |
| Event mapping | `PURCHASE`, `ADD_CART`, `VIEW_CONTENT`, `START_CHECKOUT`, `SIGN_UP` | ✅ |
| sc_cid tracking | Snapchat click ID from URL | ✅ |
| User data hashing | SHA-256 email, phone | ✅ |
| Test mode | Snapchat test event support | ✅ |

### 1.5 Pinterest Conversions API

| Task | Description | Status |
|------|-------------|--------|
| Create `PinterestService.php` | Dedicated service for Pinterest | ✅ |
| Event mapping | `checkout`, `add_to_cart`, `page_visit`, `signup`, `watch_video`, `lead` | ✅ |
| Pinterest click ID | Tracking via cookie or URL param | ✅ |
| User data | Email hash, privacy-compliant | ✅ |
| Test mode | Pinterest tag testing | ✅ |

### 1.6 Twitter (X) Conversions API

| Task | Description | Status |
|------|-------------|--------|
| Create `TwitterService.php` | Dedicated service for Twitter/X | ✅ |
| Event mapping | `Purchase`, `AddToCart`, `ViewContent`, `SignUp`, `Lead` | ✅ |
| twclid tracking | Twitter click ID from URL | ✅ |
| User data | Device ID, email hash | ✅ |
| Test mode | Twitter Pixel test mode | ✅ |

### 1.7 LinkedIn Conversions API

| Task | Description | Status |
|------|-------------|--------|
| Create `LinkedInService.php` | Dedicated service for LinkedIn | ✅ |
| Event mapping | `Purchase`, `Lead`, `SignUp`, `AddToCart`, `PageVisit` | ✅ |
| LinkedIn click ID | Tracking via URL params | ✅ |
| User data | Email hash, LinkedIn insight tag | ✅ |
| Test mode | LinkedIn test events | ✅ |

### 1.8 Redis Queue Architecture

| Task | Description | Status |
|------|-------------|--------|
| Configure Laravel Horizon | Queue monitoring dashboard | ✅ |
| Create `capi-events` queue | High-priority queue for tracking events | ✅ |
| Retry logic | 10 attempts with exponential backoff (1s → 2s → 4s → ... → 512s) | ✅ |
| Dead letter queue | Permanently failed events stored for review | ✅ |
| Queue health monitoring | Alert on backlog > 1,000 events | ✅ |
| Rate limiting | Per-platform API rate limit handling | ✅ |
| Batch processing | Group events per platform for batch CAPI sends | ✅ |

### 1.9 Event Deduplication Engine

| Task | Description | Status |
|------|-------------|--------|
| Create `DeduplicationService.php` | Core dedup logic | ✅ |
| Browser↔server matching | Match event_id from pixel + CAPI | ✅ |
| Configurable dedup window | Default: 5 minutes, configurable per platform | ✅ |
| Order-level dedup | Purchase events matched by order_id | ✅ |
| Session-level dedup | ViewContent/AddToCart matched by session hash | ✅ |
| Multi-key dedup | event_id + order_id + session_id + timestamp | ✅ |
| Dedup dashboard | View deduplication statistics | ✅ |

### 1.10 Shopify Connector

| Task | Description | Status |
|------|-------------|--------|
| Create webhook endpoint | `/webhooks/shopify/{event}` | ✅ |
| HMAC verification | Validate Shopify webhook signatures | ✅ |
| Event mapping | `orders/create` → Purchase, `carts/create` → AddToCart | ✅ |
| Customer data mapping | Extract email, phone, name from Shopify order | ✅ |
| Product data mapping | Line items → CAPI contents array | ✅ |
| Fulfillment events | `fulfillments/create` for post-purchase events | ✅ |

### 1.11 WooCommerce Connector (Upgrade)

| Task | Description | Status |
|------|-------------|--------|
| Signature verification | WooCommerce webhook secret validation | ✅ |
| Event expansion | Support more WooCommerce hooks | ✅ |
| Customer data | Improved email/phone extraction | ✅ |
| Product categories | Map to CAPI content_category | ✅ |

### 1.12 Custom API / Headless Connector

| Task | Description | Status |
|------|-------------|--------|
| Public REST endpoint | `POST /api/v1/track` | ✅ |
| API key authentication | Per-store API keys | ✅ |
| Standardized event format | JSON schema for event data | ✅ |
| Rate limiting | 1,000 req/min per key | ✅ |
| Response validation | Return validation errors for malformed data | ✅ |

### 1.13 Admin Marketing UI — All Platforms

| Task | Description | Status |
|------|-------------|--------|
| Google Ads panel | Conversion ID, label, Google Ads CID | ✅ |
| Snapchat panel | Pixel ID, API token | ✅ |
| Pinterest panel | Tag ID, access token | ✅ |
| Twitter panel | Pixel ID, API key | ✅ |
| LinkedIn panel | Insight Tag ID | ✅ |
| Per-platform toggles | Enable/disable each platform individually | ✅ |
| Connection tests | Test button per platform | ✅ |
| Status indicators | Connected/Disconnected/Error per platform | ✅ |

### 1.14 CSP Updates

| Task | Description | Status |
|------|-------------|--------|
| Add Google Ads domains | `www.googleadservices.com`, `googleads.g.doubleclick.net` | ✅ |
| Add Snapchat domains | `tr.snapchat.com`, `sc-static.net` | ✅ |
| Add Pinterest domains | `ct.pinterest.com`, `s.pinimg.com` | ✅ |
| Add Twitter domains | `static.ads-twitter.com`, `analytics.twitter.com` | ✅ |
| Add LinkedIn domains | `snap.licdn.com`, `px.ads.linkedin.com` | ✅ |

---

## Phase 2: Identity Resolution & Attribution

> **Objective:** Build a first-party, privacy-compliant identity system that survives ad blockers, iOS restrictions, and cookie deprecation — providing True ROAS data independent of platform modeling.

### 2.1 First-Party CNAME Tracking Domain

| Task | Description | Status |
|------|-------------|--------|
| DNS setup guide | CNAME `track.jenincare.com` → origin server | ✅ |
| Subdomain routing | Nginx/Apache virtual host for tracking subdomain | ✅ |
| SSL certificate | Let's Encrypt for `track.jenincare.com` | ✅ |
| Cookie domain | Set cookies on `.jenincare.com` for cross-subdomain access | ✅ |
| Proxy configuration | Route `/pixel/*` and `/collect/*` to tracking handlers | ✅ |

### 2.2 Server-Side UUID with HttpOnly Cookie

| Task | Description | Status |
|------|-------------|--------|
| Create `IdentityService.php` | UUID generation and management | ✅ |
| UUID v4 generation | Generate on first visit, persist in HttpOnly cookie | ✅ |
| Cookie configuration | `_juuid`: HttpOnly, Secure, SameSite=Strict, 400-day expiry | ✅ |
| Identity merge | On login, merge anonymous UUID with authenticated user | ✅ |
| Middleware | `UuidMiddleware.php` — attach UUID to every request | ✅ |

**New files:** `app/Services/IdentityService.php`, `app/Http/Middleware/UuidMiddleware.php`

### 2.3 Browser Fingerprinting

| Task | Description | Status |
|------|-------------|--------|
| Create fingerprint JS | Canvas, WebGL, AudioContext, screen, timezone, fonts, plugins | ✅ |
| Beacon API send | `navigator.sendBeacon('/api/track/fingerprint')` on page load | ✅ |
| Fingerprint hash | SHA-256 of collected signals | ✅ |
| Store in DB | Linked to UUID, IP, User-Agent | ✅ |
| Privacy compliance | GDPR consent check before fingerprinting | ✅ |

**New JS:** `public/js/fingerprint.js`
**New endpoint:** `POST /api/track/fingerprint`

### 2.4 Touchpoint Event Sourcing

| Task | Description | Status |
|------|-------------|--------|
| Create `meta_event_sources` table | All user touchpoints with timestamps | ✅ |
| Record events | page_view, view_content, add_to_cart, checkout, purchase, signup, lead | ✅ |
| Store metadata | UUID, event_type, URL, referrer, UTM params, timestamp, IP, User-Agent | ✅ |
| Create query service | `EventSourcingService.php` for analytics queries | ✅ |

**New migration:** `create_meta_event_sources_table`
**New files:** `app/Services/EventSourcingService.php`

### 2.5 Cross-Device Journey Mapping

| Task | Description | Status |
|------|-------------|--------|
| UTM parameter extraction | source, medium, campaign, term, content from all URL visits | ✅ |
| Click ID mapping | fbclid → Facebook, gclid → Google, ttclid → TikTok, twclid → Twitter | ✅ |
| Store attribution data | Linked to UUID for lifetime of cookie | ✅ |
| Anonymous→identified merge | When user provides email, link sessions | ✅ |
| First-touch attribution | Record first visit source for each user | ✅ |

**New files:** `app/Services/AttributionService.php`

### 2.6 True ROAS Dashboard

| Task | Description | Status |
|------|-------------|--------|
| Create dashboard view | Admin panel: "True ROAS" | ✅ |
| Data source | ONLY server-recorded sales (no Meta modeled data) | ✅ |
| Key metrics | Orders, Revenue, ROAS, CPA, AOV, Conversion Rate | ✅ |
| Breakdown by | Platform (Meta/TikTok/Google), Campaign, Ad Set, Ad | ✅ |
| Comparison view | True ROAS vs Meta-reported ROAS (side by side) | ✅ |
| Date range filter | Custom date picker | ✅ |
| Export | CSV, PDF, scheduled email reports | ✅ |
| Real-time updates | Poll every 30 seconds | ✅ |

**New files:** `app/Http/Controllers/Admin/RoasDashboardController.php`, `resources/views/admin/roas/index.blade.php`
**New routes:** `GET /admin/roas`, `GET /admin/roas/data`

### 2.7 Attribution Models

| Task | Description | Status |
|------|-------------|--------|
| Model implementations | Last Click, First Click, Linear, Time Decay, Position Based | ✅ |
| Dashboard toggle | Switch attribution model live | ✅ |
| Revenue attribution | See attributed revenue per model | ✅ |
| Model comparison | Side-by-side bar chart | ✅ |

**New files:** `app/Services/AttributionModels/LastClick.php`, `app/Services/AttributionModels/FirstClick.php`, `app/Services/AttributionModels/Linear.php`, `app/Services/AttributionModels/TimeDecay.php`, `app/Services/AttributionModels/PositionBased.php`

### 2.8 Multi-Session Stitching

| Task | Description | Status |
|------|-------------|--------|
| Email-hash matching | Link sessions by SHA-256(email) | ✅ |
| Phone-hash matching | Link sessions by SHA-256(phone) | ✅ |
| Customer ID matching | Link sessions to authenticated user_id | ✅ |
| Re-attribution | Recalculate attribution for merged identities | ✅ |
| Privacy controls | Data retention policy, auto-anonymize after 365 days | ✅ |

**Files:** `app/Services/IdentityService.php`, `app/Services/AttributionService.php`

---

## Phase 3: AI Compliance & Filtering Gateway

> **Objective:** Build the first-to-market AI-powered compliance engine that protects ad accounts from policy violations, suppresses trigger words, filters low-value conversions, and predicts account bans before they happen.

### 3.1 Multi-LLM Engine

| Task | Description | Status |
|------|-------------|--------|
| Create provider interface | `LLMProviderInterface` with `sanitize(string $text): string` | ✅ |
| OpenAI GPT-4o integration | API client with rate limiting | ✅ |
| Claude 4 integration | API client with fallback | ✅ |
| Local LLaMA (Ollama) | Fallback when APIs are unreachable | ✅ |
| Fallback chain | GPT-4o → Claude → LLaMA → return original (safe) | ✅ |
| Cost controls | Configurable provider selection per event type, monthly budget caps | ✅ |
| Caching | Cache sanitization results by text hash (TTL: 1 hour) | ✅ |

**New files:** `app/Services/AI/LLMProviderInterface.php`, `app/Services/AI/OpenAIProvider.php`, `app/Services/AI/ClaudeProvider.php`, `app/Services/AI/LlamaProvider.php`, `app/Services/AISanitizerService.php`

### 3.2 Trigger Word Database

| Task | Description | Status |
|------|-------------|--------|
| Create `trigger_words` table | Columns: word, category, severity, platform, action, created_at | ✅ |
| Seed data | 200+ known Meta/TikTok trigger words from ad policy docs | ✅ |
| Categories | Medical claims, before/after, weight loss, financial services, etc. | ✅ |
| Actions | `remove` (delete word), `replace` (swap with safe term), `block` (stop event) | ✅ |
| Admin UI | CRUD management for trigger words | ✅ |
| Auto-update scheduler | Weekly check of Meta/TikTok policy updates | ✅ |

**New migration:** `create_trigger_words_table`
**New model:** `app/Models/TriggerWord.php`
**New files:** `app/Http/Controllers/Admin/TriggerWordController.php`, `resources/views/admin/trigger-words/index.blade.php`

### 3.3 Payload Sanitization Pipeline

| Task | Description | Status |
|------|-------------|--------|
| Pipeline architecture | Chain of Responsibility pattern | ✅ |
| Step 1: Trigger Word Filter | Check product name, description, category against trigger word DB | ✅ |
| Step 2: LLM Sanitizer | Send to AI for semantic analysis if trigger words found | ✅ |
| Step 3: Policy Checker | Validate against platform-specific ad policies | ✅ |
| Step 4: Log & Report | Record all sanitization actions with before/after | ✅ |
| Configurable actions | Per-platform: block, warn, replace, or allow | ✅ |
| Performance | Pipeline timeout: 2 seconds max, with circuit breaker | ✅ |

**New files:** `app/Services/Sanitization/SanitizationPipeline.php`, `app/Services/Sanitization/TriggerWordFilter.php`, `app/Services/Sanitization/LLMFilter.php`, `app/Services/Sanitization/PolicyChecker.php`

### 3.4 Value & Margin Filtering

| Task | Description | Status |
|------|-------------|--------|
| Per-platform minimum value | Settings: minimum order value per platform | ✅ |
| Net margin calculation | (revenue - COGS) / revenue from product data | ✅ |
| Margin threshold | Configurable % (e.g., only send orders with >20% margin) | ✅ |
| High-ticket threshold | Configurable min (e.g., only orders > $100) | ✅ |
| Category filters | Block specific product categories from CAPI | ✅ |
| Admin UI | Value/margin filter configuration panel | ✅ |

**New files:** `app/Services/Sanitization/ValueFilter.php`

### 3.5 Junk & Duplicate Filtering

| Task | Description | Status |
|------|-------------|--------|
| COD cancellation detection | Track COD created vs delivered ratio per customer | ✅ |
| Auto-block rules | Block COD orders from users with >60% cancellation rate | ✅ |
| Test order detection | Block: @test.com, @yopmail.com, @mailinator.com, test in name | ✅ |
| Duplicate event blocking | Same event_id within dedup window → silently discard | ✅ |
| Duplicate order blocking | Same order_id within 24 hours → silently discard | ✅ |
| Dropshipping detection | Low price + suspicious supplier patterns | ✅ |
| Fraud scoring | Basic risk score per order based on multiple signals | ✅ |

**New files:** `app/Services/Sanitization/JunkFilter.php`, `app/Services/Sanitization/DuplicateFilter.php`

### 3.6 Ad Account Health Scoring

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Track rejection rate | % of CAPI events rejected by platform | ✅ |
| Track policy violations | Count of sanitization alerts per ad account | ✅ |
| Track duplicate rates | % of events flagged as duplicates | ✅ |
| Track error rates | CAPI HTTP errors (4xx, 5xx) per account | ✅ |
| Health score | Algorithm: 100 - weighted sum of negative signals | ✅ |
| Alert thresholds | Email/notification when score drops below 50 | ✅ |
| Dashboard | Health score cards, trend charts, drill-down | ✅ |

**New files:** `app/Services/AdAccountHealthService.php`
**New migration:** `create_ad_account_health_logs_table`

### 3.7 Admin UI for AI Compliance

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Overview dashboard | Sanitization stats, trigger word count, health scores | ✅ |
| Sanitization log viewer | Searchable, filterable log with before/after | ✅ |
| Trigger word table | CRUD with import/export | ✅ |
| Value/margin config | Per-platform settings panel | ✅ |
| Health score cards | Visual health indicators per ad account | ✅ |
| Alert history | Timeline of alerts and resolutions | ✅ |

**New files:** `resources/views/admin/ai-compliance/index.blade.php`, `app/Http/Controllers/Admin/AiComplianceController.php`
**New routes:** `GET /admin/ai-compliance`, `POST /admin/ai-compliance/*`

---

## Phase 4: Omnichannel & Predictive AI

> **Objective:** Connect offline sales to online attribution and predict customer lifetime value to optimize ad platform lookalike audiences.

### 4.1 POS Bridge REST API

| Task | Description | Status |
|------|-------------|--------|
| Secure API endpoint | `POST /api/v1/pos/sale` with HMAC authentication | ✅ |
| Accept fields | phone, email, order_total, items, store_id, timestamp, currency | ✅ |
| Match customer | Find existing UUID by phone/email hash | ✅ |
| Offline→online attribution | Link offline sale to original ad click if matched | ✅ |
| Meta Offline Conversions | Upload via Meta Offline Conversions API | ✅ |
| TikTok Offline Events | Upload via TikTok Events API (offline) | ✅ |
| Dashboard | POS sales overview, match rate, offline revenue | ✅ |

**New files:** `app/Http/Controllers/Api/PosBridgeController.php`, `app/Services/OfflineConversionService.php`
**New migration:** `create_pos_sales_table`

### 4.2 Predictive LTV ML Model

| Task | Description | Status |
|------|-------------|--------|
| Python FastAPI microservice | `POST /api/predict-ltv` accepts features → returns prediction | ✅ |
| Model training | Train on: AOV, product category, COGS, location, device, channel, day_of_week, month | ✅ |
| Predictions | 30-day LTV, 90-day LTV, 365-day LTV | ✅ |
| Segments | B2B (high LTV, repeat purchases), B2C (medium), One-time (low) | ✅ |
| Feature engineering | Automated feature extraction from order data | ✅ |
| Monthly retraining | Scheduled retrain with new data | ✅ |
| Docker container | `ml-service/Dockerfile` with FastAPI + uvicorn | ✅ |
| Laravel client | HTTP client to call microservice | ✅ |

**New directory:** `ml-service/`
**New files:** `ml-service/app.py`, `ml-service/model.py`, `ml-service/train.py`, `ml-service/requirements.txt`, `ml-service/Dockerfile`

### 4.3 CAPI Value Multiplier

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Multiplier logic | When LTV predicted as B2B → multiply purchase value in CAPI by 1.5x | ✅ |
| Configurable ratios | Per-segment multiplier in admin settings | ✅ |
| Platform-specific | Different multipliers for Meta vs TikTok | ✅ |
| Dashboard | View current multipliers, segment distribution | ✅ |
| Algorithm training | Higher-value signals → better lookalike audiences | ✅ |

**New files:** `app/Services/LtvMultiplierService.php`

### 4.4 Predictive Dashboard

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Customer LTV view | Predicted LTV per customer table | ✅ |
| Segment distribution | Pie chart: B2B vs B2C vs One-time | ✅ |
| Multiplier config | Admin settings panel per segment and platform | ✅ |
| Model accuracy | RMSE, MAE, prediction interval graphs | ✅ |
| Feature importance | Top 10 features driving LTV predictions | ✅ |

**New files:** `resources/views/admin/predictive/index.blade.php`, `app/Http/Controllers/Admin/PredictiveController.php`

---

## Phase 5: Security, Routing & Dynamic UI

> **Objective:** Protect ad accounts from policy review bots, ensure redundancy with multi-pixel fan-out, and dynamically adjust UI for compliance.

### 5.1 Behavioral Bot Detection

| Task | Description | Status |
|------|-------------|--------|
| Lightweight JS tracker | Capture: mouse speed, acceleration, scroll depth, click intervals, keypress timing | ✅ |
| Compute bot score | 0 (human) to 100 (bot) based on behavioral patterns | ✅ |
| Beacon send | `POST /api/track/behavior` with bot_score | ✅ |
| Server-side validation | Cross-check with IP reputation, user-agent, headers | ✅ |
| Privacy-first | No cookies, no fingerprinting, just ephemeral behavioral data | ✅ |

**New JS:** `public/js/behavioral-analysis.js`

### 5.2 Safe Page Routing

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Create `TrafficRouter` middleware | Inspect bot_score, IP, user-agent | ✅ |
| Bot criteria | score > 70 OR known reviewer IP OR known reviewer UA | ✅ |
| Safe page route | Show policy-compliant version of the page | ✅ |
| Normal page route | Show fully optimized conversion page | ✅ |
| Configurable threshold | Admin setting for bot_score threshold | ✅ |

**New files:** `app/Http/Middleware/TrafficRouter.php`

### 5.3 Ad Reviewer Database

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Table `ad_reviewer_ips` | IP ranges, user-agents, ISP, source, notes | ✅ |
| Seed data | Known Meta/TikTok reviewer IPs, datacenter ranges | ✅ |
| Auto-block logic | Match inbound requests against reviewer database | ✅ |
| Admin UI | View and manage reviewer IPs | ✅ |

**New migration:** `create_ad_reviewer_ips_table`

### 5.4 Multi-Pixel Fan-Out

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Multiple pixel IDs per platform | Primary + up to 3 backup pixels | ✅ |
| Fan-out logic | Send same event to all configured pixels simultaneously | ✅ |
| Redundancy | If primary account banned, backup has complete data | ✅ |
| Config per event type | Different pixel sets for different event types | ✅ |
| Dashboard | Pixel status, last sent, error counts | ✅ |

**New files:** `app/Services/MultiPixelService.php`

### 5.5 Dynamic UI / Soft CTAs

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| UUID retargeting flow check | Identify user's current flow from attribution data | ✅ |
| Soft CTA mapping | "Learn More" instead of "Buy Now", "Explore" instead of "Purchase" | ✅ |
| Hard CTA mapping | "Add to Cart", "Buy Now", "Subscribe" for new users | ✅ |
| Configurable per flow | Admin panel for CTA text mapping | ✅ |
| A/B test ready | Compatible with experiment framework | ✅ |

**New files:** `app/Http/Middleware/DynamicCtaMiddleware.php`, `app/Helpers/CtaHelper.php`

---

## Phase 6: Meta Ads Management

> **Objective:** Replace all stubbed Meta Ads controllers with real Facebook Graph API integration, enabling full campaign management from the admin panel.

### 6.1 Facebook Graph API Client

| Task | Description | Status |
|------|-------------|--------|
| Create `FacebookGraphService.php` | Full Graph API client | ✅ |
| OAuth 2.0 tokens | Long-lived token generation and refresh | ✅ |
| API versioning | Target v22.0+ | ✅ |
| Rate limiting | Respect Facebook call limits per ad account | ✅ |
| Error handling | Structured error parsing, retry on 5xx | ✅ |
| Batch requests | Graph API batch endpoint for efficiency | ✅ |

**New files:** `app/Services/Meta/FacebookGraphService.php`

### 6.2 Campaign Management

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| List campaigns | GET from Facebook, cache locally | ✅ |
| Create campaign | POST with objective, status, name, bid strategy | ✅ |
| Toggle status | Active ↔ PAUSED via API | ✅ |
| Delete campaign | API deletion | ✅ |
| Sync | One-click sync from Facebook → local DB | ✅ |
| Validation | Check required fields before API call | ✅ |

**Files:** `modules/CustomAdmin/Http/Controllers/MetaAdsController.php` (rewrite)

### 6.3 Ad Set Management

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| List ad sets | GET from Facebook, filter by campaign | ✅ |
| Create ad set | Targeting, budget, schedule, bid strategy, optimization goal | ✅ |
| Update budget | Change daily/lifetime budget via API | ✅ |
| Toggle status | Active ↔ PAUSED | ✅ |
| Targeting preview | Show estimated reach | ✅ |

**Files:** `MetaAdsController.php` (rewrite)

### 6.4 Ad & Creative Management

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| List ads | GET from Facebook per ad set | ✅ |
| Create ad | Link creative + ad set + name + status | ✅ |
| Upload creative image | POST to Facebook, get image hash | ✅ |
| Upload creative video | POST to Facebook, get video ID | ✅ |
| Create ad creative | Specify: image/video, headline, body, CTA, link, page_id | ✅ |
| Instagram actor ID | Link Instagram account for cross-posting | ✅ |

**Files:** `MetaAdsController.php` (rewrite)
**New migration fields:** `instagram_actor_id` (exists)

### 6.5 Real Insights & Analytics

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Fetch insights | GET from Facebook with date_preset, time_range | ✅ |
| Metrics | impressions, clicks, spend, CTR, CPC, CPM, conversions, ROAS | ✅ |
| Cache | 15-minute TTL, clear on manual refresh | ✅ |
| Dashboard | Charts, tables, export | ✅ |
| Scheduled sync | Hourly insights pull via cron | ✅ |

**Files:** `MetaAdsController.php` (rewrite)

### 6.6 Ad Account Connection Wizard

| Task | Description | Status |
|------|-------------|--------|
| OAuth flow UI | "Connect Facebook Ad Account" button | ✅ |
| Permissions scope | `ads_management`, `ads_read`, `business_management` | ✅ |
| Token storage | Encrypted in database | ✅ |
| Connection test | Validate token with `GET /me/adaccounts` | ✅ |
| Multi-account | Support multiple ad accounts | ✅ |
| Token refresh | Automated before expiry | ✅ |

**New view:** `resources/views/admin/ads/connect.blade.php`

---

## Phase 7: Leads Hub & Messenger

> **Objective:** Replace stubbed lead/messenger controllers with real Facebook Graph API integration for lead capture, scoring, Messenger conversations, and Instagram DMs.

### 7.1 Facebook Leads Capture

| Task | Description | Status |
|------|-------------|--------|
| Graph API leads fetch | `GET /{page_id}/leads` with pagination | ✅ |
| Webhook verification | Handle `hub.challenge` verification request | ✅ |
| Lead storage | Save to `meta_leads` table | ✅ |
| Duplicate prevention | Skip leads with duplicate `event_id` | ✅ |
| Real-time notifications | Dashboard toast on new lead | ✅ |
| Auto-assign | Round-robin assignment to team members | ✅ |

**Files:** `modules/Meta/Services/LeadSyncService.php` (rewrite), `modules/CustomAdmin/Http/Controllers/MetaLeadHubController.php` (rewrite)

### 7.2 Lead Scoring

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Score factors | Source (Instagram > Facebook), time since created, form fields, city | ✅ |
| Hot lead | Contacted within 1 hour, high-value inquiry | ✅ |
| Warm lead | Engaged, middle of funnel, responded to message | ✅ |
| Cold lead | No response > 48 hours, low-value form | ✅ |
| Auto-score | On lead creation and after each interaction | ✅ |

**New files:** `app/Services/LeadScoringService.php`

### 7.3 Messenger Integration

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Webhook handler | Receive messages from Facebook Messenger | ✅ |
| Send reply | `POST /{page_id}/messages` with recipient + message | ✅ |
| Conversation history | Store in `meta_conversations` and `meta_messages` | ✅ |
| Read receipts | Track delivered/read status | ✅ |
| Typing indicator | Show "typing..." in Messenger | ✅ |
| Quick replies | Send structured messages with buttons | ✅ |

**Files:** `modules/CustomAdmin/Http/Controllers/MetaWebhookController.php` (rewrite)

### 7.4 Bulk Messaging Campaigns

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Create campaign | Name, message text, quick replies | ✅ |
| Select audience | Filter by city, lead score, source, age | ✅ |
| Send via API | Facebook Messenger API batch send | ✅ |
| Track metrics | Sent, delivered, read, replied, failed | ✅ |
| Rate limiting | Respect Facebook messaging limits | ✅ |
| Opt-out | Include unsubscribe option per law | ✅ |

**Files:** Existing `meta_bulk_campaigns` migration ready, controller to rewrite

### 7.5 Instagram Integration

| Task | Description |
|------|-------------|
| Task | Description | Status |
|------|-------------|--------|
| Connect Instagram Business | Link Instagram account via Meta Business | ✅ |
| Receive DMs | Webhook handler for Instagram messages | ✅ |
| Reply to DMs | Send via Instagram Messaging API | ✅ |
| Unified inbox | Instagram + Facebook messages in one view | ✅ |
| Instagram lead ads | Capture leads from Instagram Lead Forms | ✅ |

**Files:** Webhook handler rewrite, `MetaLeadHubController.php`

---

## Phase 8: SaaS Platform & Multi-Tenancy

> **Objective:** Productize the entire system as a multi-tenant SaaS platform with subscription billing, public API, and embeddable tracking script.

> **Status:** ⚠️ Not yet implemented — requires multi-tenant DB redesign

### 8.1 Multi-Tenant Architecture

| Task | Description |
|------|-------------|
| Create `tenants` table | name, domain, email, subscription_tier, settings (JSON), active |
| Add `tenant_id` | Foreign key to all trackable tables |
| Tenant middleware | `TenantMiddleware.php` — auto-scope queries |
| Tenant onboarding | Registration wizard: domain, platform, pixel IDs |
| Tenant isolation | Data completely separated by tenant_id |
| Subdomain routing | `{tenant}.jenincare.com` or custom domain |

**New migration:** `create_tenants_table`
**New middleware:** `app/Http/Middleware/TenantMiddleware.php`

### 8.2 Subscription & Billing

| Task | Description |
|------|-------------|
| Pricing tiers | Starter (10k events/month), Growth (100k), Scale (1M), Enterprise (unlimited) |
| Stripe integration | Checkout, subscription management, invoices |
| Usage tracking | Event counter per tenant, monthly reset |
| Overages | Automatic billing for excess events |
| Free trial | 14-day free trial with Starter features |
| Admin portal | Manage all subscriptions |

**Install:** Laravel Spark or Cashier

### 8.3 Public REST API

| Task | Description |
|------|-------------|
| Versioned API | `/api/v1/*` |
| Auth | API key in header `X-API-Key` |
| Endpoints | `POST /track`, `GET /stats`, `GET /events`, `POST /events/verify` |
| Rate limiting | Per-tier: Starter 100/min, Growth 500/min, Scale 2000/min |
| Response format | JSON: `{ success: bool, data: {}, errors: [] }` |
| Documentation | OpenAPI / Swagger UI |

**New files:** `app/Http/Controllers/Api/V1/TrackingController.php`, `app/Http/Middleware/ApiKeyMiddleware.php`

### 8.4 Embeddable Tracking Script

| Task | Description |
|------|-------------|
| Dynamic pixel.js | `<script src="https://track.jenincare.com/pixel.js" data-tenant="xxx"></script>` |
| Auto-configuration | Script reads tenant config from server |
| Multi-pixel injection | Loads all configured platform pixels dynamically |
| First-party CNAME | Serves from tracking subdomain |
| Async loading | Non-blocking, defer by default |
| Error handling | Silent fail — no console errors on adblock |

**New files:** `public/js/pixel.js` (dynamic), `app/Http/Controllers/PixelScriptController.php`

### 8.5 SaaS Admin Dashboard

| Task | Description |
|------|-------------|
| Overview | Total tenants, active, events processed (last 24h/7d/30d), MRR |
| Tenant list | Search, filter by tier, status, date |
| Tenant detail | Events, usage, settings, subscription |
| Revenue reports | MRR, ARR, churn rate, LTV by cohort |
| System health | Queue size, error rates, API latency |

**New files:** `app/Http/Controllers/Admin/SaaS/SaaSDashboardController.php`, `resources/views/admin/saas/index.blade.php`

### 8.6 Onboarding & Documentation

| Task | Description |
|------|-------------|
| Setup wizard | Step-by-step: connect platform → add pixels → verify events |
| API docs | Interactive Swagger UI at `/api/docs` |
| Integration guides | WooCommerce, Shopify, Custom PHP, Headless |
| Troubleshooting | FAQ, common errors, debug mode instructions |
| Video tutorials | Links to Loom/YouTube walkthroughs |

**New directory:** `resources/docs/`

---

## Phase 9: Frontend Social Media Display — ✅ COMPLETE

> **Objective:** Ensure all configured social media platforms are properly displayed across all frontend views.

### 9.1 Add Missing Social Icons (LinkedIn, YouTube)

| Task | Description | Status |
|------|-------------|--------|
| LinkedIn icon to floating sidebar | Add to all 4 theme layouts | ✅ |
| YouTube icon to floating sidebar | Add to all 4 theme layouts | ✅ |
| LinkedIn icon to footer | Add hover effect matching footer style | ✅ |
| YouTube icon to footer | Add hover effect matching footer style | ✅ |
| LinkedIn icon to contact page | Add with proper styling | ✅ |
| YouTube icon to contact page | Add with proper styling | ✅ |
| Twitter & TikTok to all themes | Added to editorial, luxury-boutique, organic-spa floating sidebar + footers | ✅ |

### 9.2 Social Media Settings — Complete All Fields

| Task | Description | Status |
|------|-------------|--------|
| Add `snapchat_url` default | Added to `SettingController.php` defaults | ✅ |
| Add `pinterest_url` default | Added to `SettingController.php` defaults | ✅ |
| Add fields to admin settings | Social media tab in settings page | ✅ |
| Add to `siteSettings` view composer | Pass to all frontend views | ✅ |

---

## Phase 10: Testing, Monitoring & Deployment

> **Objective:** Ensure production readiness with comprehensive testing, monitoring, alerting, and Docker-based deployment.

### 10.1 Testing Strategy

| Task | Description | Status |
|------|-------------|--------|
| Unit tests | Each service class | ✅ 85 tests passing |
| Feature tests | Each API endpoint | ✅ |
| CAPI payload validation | Compare payload structure against Meta's expected format | ✅ |
| Mock platform APIs | Use Laravel HTTP fake for CAPI tests | ✅ |
| Queue worker tests | Test job processing, retries, dead letter | ✅ |
| Performance tests | Events per second, queue throughput | ⚠️ |
| CI pipeline | GitHub Actions: test → lint → build | ⚠️ |

**New files:** `tests/Feature/Tracking/`, `tests/Unit/Services/`

### 10.2 Monitoring & Alerting

| Task | Description | Status |
|------|-------------|--------|
| Laravel Pulse | Queue health, slow requests, errors | ✅ |
| Sentry | PHP exception tracking, performance monitoring | ❌ Not installed |
| CAPI success rate | Custom metric: % of successful CAPI sends | ✅ |
| Queue backlog alert | Email/Slack when >1,000 events in queue | ⚠️ |
| Error rate alert | Email/Slack when error rate > 10% in 5 minutes | ⚠️ |
| Ad account health alert | Email when health score drops below 50 | ✅ |
| Uptime monitoring | External ping service | ❌ |

**Files:** `config/pulse.php`, `.env` Sentry config

### 10.3 Docker Deployment

| Task | Description | Status |
|------|-------------|--------|
| Laravel Dockerfile | PHP 8.3 + FPM + Composer | ✅ |
| Nginx config | Main site + tracking subdomain | ✅ |
| Queue worker container | `php artisan horizon` | ✅ |
| Scheduler container | `php artisan schedule:work` | ✅ |
| ML service container | Python FastAPI + uvicorn | ✅ |
| `docker-compose.yml` | App + queue + scheduler + Redis + PostgreSQL + ML | ✅ |
| CI/CD | Deploy on push to main branch | ⚠️ |

**New files:** `Dockerfile`, `docker-compose.yml`, `nginx/default.conf`, `.github/workflows/deploy.yml`

---

## Implementation Dependencies

```
Phase 1 ──────┬── 1.1 Facebook CAPI ──┬── 1.8 Queue ──┬── 1.9 Dedup
               ├── 1.2 TikTok API ─────┤               │
               ├── 1.3-1.7 Others ─────┘               │
               ├── 1.10-1.12 Connectors ───────────────┘
               ├── 1.13 Admin UI (depends on 1.1-1.7)
               └── 1.14 CSP (depends on 1.3-1.7)
                         │
Phase 2 ──────┬── 2.1 CNAME (independent)
               ├── 2.2-2.3 UUID + FP (independent)
               ├── 2.4-2.5 Journey + Events (depends on 2.2)
               ├── 2.6 ROAS Dashboard (depends on 2.4 + Phase 1)
               └── 2.7-2.8 Attribution (depends on 2.5)
                         │
Phase 3 ──────┬── 3.1-3.2 LLM + Trigger DB (parallel)
               ├── 3.3-3.5 Sanitization (depends on 3.1, 3.2)
               ├── 3.6 Health Scoring (depends on Phase 1)
               └── 3.7 UI (depends on 3.3, 3.6)
                         │
Phase 4 ──────┬── 4.1 POS Bridge (independent)
               ├── 4.2 LTV Model (independent)
               ├── 4.3 Multiplier (depends on 4.2)
               └── 4.4 Dashboard (depends on 4.2, 4.3)
                         │
Phase 5 ──────┬── 5.1-5.3 Bot Detection (parallel)
               ├── 5.4 Multi-Pixel (depends on Phase 1)
               └── 5.5 Dynamic UI (depends on Phase 2)
                         │
Phase 6 ──────┬── 6.1 Graph API Client
               ├── 6.2-6.4 Campaign/Ad/AdSet (sequential)
               ├── 6.5 Insights (depends on 6.2)
               └── 6.6 Wizard (depends on 6.1)
                         │
Phase 7 ──────┬── 7.1-7.3 Leads + Messenger (parallel)
               ├── 7.4 Bulk Campaigns (depends on 7.3)
               └── 7.5 Instagram (depends on 7.3)
                         │
Phase 8 ──────┬── 8.1 Multi-Tenant (requires DB redesign)
               ├── 8.2 Billing (depends on 8.1)
               ├── 8.3-8.4 API + Script (depends on Phase 1)
               └── 8.5-8.6 Dashboard + Docs (depends on 8.1-8.4)
                         │
Phase 9 ──────┘  (independent, can be done anytime)
                         │
Phase 10 ─────┬── 10.1 Tests (continuous)
               ├── 10.2 Monitoring (after Phase 1)
               └── 10.3 Docker (after Phase 1)
```

---

## Phase 1 Execution Order

The recommended execution order within Phase 1 for maximum velocity:

```
Week 1-2:  ── 1.1 Facebook CAPI (complete)
              ├── 1.8 Redis Queue
              ├── 1.9 Dedup Engine
              └── 1.13 Facebook UI
                    │
Week 3-4:  ── 1.2 TikTok API (complete)
              ├── 1.13 TikTok UI
              └── 1.14 CSP Updates
                    │
Week 5-6:  ── 1.3 Google Ads
              ├── 1.4 Snapchat
              ├── 1.5 Pinterest
              ├── 1.6 Twitter/X
              ├── 1.7 LinkedIn
              └── 1.13 All Platform UIs
                    │
Week 7-8:  ── 1.10 Shopify Connector
              ├── 1.11 WooCommerce Upgrade
              └── 1.12 Custom API Connector
```

---

## Competitive Comparison

| Feature | wetracked.io | ServerTrack | HYROS | **JeniCare (Our System)** |
|---------|:-----------:|:-----------:|:-----:|:------------------------:|
| **Platforms** | 7 | 3 | 5 | **10+** |
| **AI Sanitization** | ❌ | ❌ | ❌ | **✅ Multi-LLM** |
| **Predictive LTV** | ❌ | ❌ | ❌ | **✅ ML Model** |
| **Bot Detection** | ❌ | ❌ | ❌ | **✅ Behavioral** |
| **Safe Page Routing** | ❌ | ❌ | ❌ | **✅** |
| **Self-Hosted Option** | ❌ | ❌ | ❌ | **✅ Docker** |
| **Multi-Tenant SaaS** | ❌ | ✅ | ❌ | **✅** |
| **True ROAS Dashboard** | ❌ | ❌ | ✅ | **✅** |
| **Event Sourcing** | ❌ | ❌ | ❌ | **✅** |
| **Attribution Models** | Basic | Basic | Advanced | **Advanced (5 models)** |
| **Ad Account Health** | ❌ | ❌ | ❌ | **✅ AI-Powered** |
| **CNAME Cloaking** | ✅ | ✅ | ❌ | **✅** |
| **Deduplication** | Basic | Basic | Advanced | **Multi-Key** |
| **Retry Logic** | 3 attempts | 10 attempts | Unknown | **10 attempts + dead letter** |
| **Pricing** | $49+/mo | $29+/mo | Custom | **Competitive** |

---

## Legend

| Symbol | Meaning |
|--------|---------|
| ✅ | Already implemented / completed |
| ⚠️ | Partial implementation |
| ❌ | Not yet implemented / planned |
| 🏗️ | Currently in progress |
| **New file** | File does not exist, needs creation |
| *Modified file* | File exists, needs edits |

---

> **Last Updated:** May 29, 2026
>
> **Owner:** JeniCare Engineering Team
>
> **Next Milestone:** Phase 8 — SaaS Multi-Tenancy Architecture
