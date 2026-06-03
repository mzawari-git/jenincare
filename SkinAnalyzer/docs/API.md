# SkinAnalyzer API Documentation

**Base URL:** `https://jenincare.shop/api/v1`

---

## Authentication

All API endpoints except registration and login require a valid Bearer token obtained via Sanctum authentication.

### Register

```
POST /api/v1/auth/register
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Full name |
| email | string | Yes | Valid email address |
| password | string | Yes | Min 8 characters |
| password_confirmation | string | Yes | Must match password |
| phone | string | No | Phone number |
| device_id | string | No | Unique device identifier |

**Success Response (201):**

```json
{
  "user": {
    "id": 1,
    "name": "Ahmed",
    "email": "ahmed@example.com",
    "phone": "+972599123456",
    "created_at": "2024-01-15T10:30:00Z"
  },
  "token": "1|abc123def456token"
}
```

### Login

```
POST /api/v1/auth/login
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | Registered email |
| password | string | Yes | Account password |
| device_id | string | No | Device identifier |

**Success Response (200):**

```json
{
  "user": {
    "id": 1,
    "name": "Ahmed",
    "email": "ahmed@example.com"
  },
  "token": "1|xyz789token"
}
```

### Logout

```
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

**Success Response (200):**

```json
{
  "message": "تم تسجيل الخروج بنجاح"
}
```

### Refresh Token

```
POST /api/v1/auth/refresh
Authorization: Bearer {token}
```

**Success Response (200):**

```json
{
  "token": "2|newTokenAbc123"
}
```

### Get Profile

```
GET /api/v1/profile
Authorization: Bearer {token}
```

**Success Response (200):**

```json
{
  "id": 1,
  "name": "Ahmed",
  "email": "ahmed@example.com",
  "phone": "+972599123456",
  "created_at": "2024-01-15T10:30:00Z",
  "total_scans": 5,
  "latest_scan": {
    "id": 42,
    "status": "approved",
    "overall_health_score": 78,
    "created_at": "2024-01-20T14:00:00Z"
  }
}
```

---

## Scan Endpoints

### Upload New Scan

Uploads a skin image for AI analysis. The scan enters `pending` status and requires admin approval before the user can view results.

```
POST /api/v1/scans
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| image | file | Yes | JPEG, PNG, or WebP (max 10MB) |
| face_angle | string | No | front, left, right (default: front) |
| notes | string | No | Optional user notes |

**Success Response (201):**

```json
{
  "id": 42,
  "status": "pending",
  "image_url": "https://jenincare.shop/storage/scans/enc_abc123.jpg",
  "message": "تم استلام الصورة بنجاح. التقرير قيد المراجعة من قبل الفريق المختص.",
  "created_at": "2024-01-20T14:00:00Z"
}
```

### List User Scans

Retrieves all scans for the authenticated user, ordered by most recent.

```
GET /api/v1/scans
Authorization: Bearer {token}
```

| Query Parameter | Type | Default | Description |
|-----------------|------|---------|-------------|
| page | integer | 1 | Page number |
| per_page | integer | 10 | Results per page (max 50) |
| status | string | null | Filter: pending, approved, rejected |

**Success Response (200):**

```json
{
  "data": [
    {
      "id": 42,
      "status": "pending",
      "overall_health_score": null,
      "thumbnail_url": "https://jenincare.shop/storage/scans/thumb_abc123.jpg",
      "created_at": "2024-01-20T14:00:00Z"
    },
    {
      "id": 41,
      "status": "approved",
      "overall_health_score": 78,
      "thumbnail_url": "https://jenincare.shop/storage/scans/thumb_def456.jpg",
      "approved_at": "2024-01-19T16:30:00Z",
      "created_at": "2024-01-19T16:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  }
}
```

### Get Scan Detail

Fetch full analysis report for a specific scan. For pending scans with PIN protection, returns locked state.

```
GET /api/v1/scans/{id}
Authorization: Bearer {token}
```

**Success Response — Approved Scan (200):**

```json
{
  "id": 41,
  "status": "approved",
  "image_url": "https://jenincare.shop/storage/scans/enc_def456.jpg",
  "heatmap_url": "https://jenincare.shop/storage/scans/heatmap_def456.jpg",
  "overall_health_score": 78,
  "formatted_score": "78% — جيد",
  "radar_metrics": {
    "hydration": 72,
    "sebum": 45,
    "pigmentation": 68,
    "pores": 55,
    "elasticity": 80
  },
  "heatmap_coordinates": {
    "acne": [{"x": 120, "y": 200, "severity": "moderate"}],
    "pigmentation": [{"x": 350, "y": 180, "severity": "mild"}]
  },
  "custom_arabic_analysis": "بناءً على تحليل صور بشرتك، نلاحظ أن...",
  "expert_free_tips": [
    "ننصح باستخدام غسول لطيف خالٍ من الزيوت مرتين يومياً",
    "تجنب لمس الوجه باليدين لتقليل انتقال البكتيريا",
    "احرص على شرب 8 أكواب من الماء يومياً"
  ],
  "recommended_products": [
    {
      "id": 5,
      "name": "غسول الوجه المنقي",
      "name_ar": "غسول الوجه المنقي",
      "price": "75.00",
      "currency": "ILS",
      "image_url": "https://jenincare.shop/images/products/cleanser.jpg",
      "matching_reason": "مناسب للبشرة الدهنية المعرضة لحب الشباب",
      "affiliate_url": "https://jenincare.shop/products/5"
    }
  ],
  "ai_provider": "Yimei AI",
  "approved_at": "2024-01-19T16:30:00Z",
  "created_at": "2024-01-19T16:00:00Z"
}
```

**Pending Scan with PIN (200):**

```json
{
  "id": 42,
  "status": "pending",
  "locked": true,
  "image_url": "https://jenincare.shop/storage/scans/enc_abc123.jpg",
  "message": "التقرير قيد المراجعة. يرجى إدخال رمز PIN للاطلاع على النتائج.",
  "created_at": "2024-01-20T14:00:00Z"
}
```

### Unlock Scan with PIN

Redeem a 4-digit PIN code to unlock a pending analysis report.

```
POST /api/v1/scans/{id}/unlock
Authorization: Bearer {token}
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| pin_code | string | Yes | 4-digit PIN code |

**Success Response (200):**

```json
{
  "id": 42,
  "status": "approved",
  "overall_health_score": 72,
  "radar_metrics": { ... },
  "custom_arabic_analysis": "...",
  "expert_free_tips": [ ... ],
  "recommended_products": [ ... ],
  "message": "تم فتح التقرير بنجاح"
}
```

**Error Response — Invalid PIN (422):**

```json
{
  "message": "رمز PIN غير صحيح. تبقى 2 محاولات.",
  "errors": {
    "pin_code": ["الرمز المدخل غير صحيح"]
  }
}
```

**Error Response — PIN Expired (410):**

```json
{
  "message": "انتهت صلاحية رمز PIN. يرجى طلب رمز جديد من المركز."
}
```

### Scan Timeline

Get historical comparison data for progress tracking.

```
GET /api/v1/scans/{id}/timeline
Authorization: Bearer {token}
```

**Success Response (200):**

```json
{
  "current_scan": { ... },
  "previous_scans": [
    {
      "id": 35,
      "overall_health_score": 65,
      "created_at": "2024-01-05T10:00:00Z",
      "thumbnail_url": "https://jenincare.shop/storage/scans/thumb_ghi789.jpg"
    }
  ],
  "progress": {
    "overall_health_score": { "from": 65, "to": 78, "change": +13 },
    "hydration": { "from": 58, "to": 72, "change": +14 },
    "sebum": { "from": 60, "to": 45, "change": -15 },
    "pigmentation": { "from": 55, "to": 68, "change": +13 },
    "pores": { "from": 50, "to": 55, "change": +5 },
    "elasticity": { "from": 70, "to": 80, "change": +10 }
  }
}
```

---

## Admin Endpoints

All admin endpoints require authentication and admin role.

```
Base path: /api/admin
Authorization: Bearer {admin_token}
```

### Login

```
POST /api/admin/login
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | Admin email |
| password | string | Yes | Admin password |

**Success Response (200):**

```json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@jenincare.shop",
    "is_admin": true
  },
  "token": "1|adminTokenXyz"
}
```

### Logout

```
POST /api/admin/logout
Authorization: Bearer {token}
```

### Dashboard Stats

```
GET /api/admin/dashboard/stats
```

**Response:**

```json
{
  "total_scans": 1250,
  "pending_scans": 12,
  "approved_today": 45,
  "active_users": 340,
  "ai_providers": {
    "active": "Native Engine",
    "quota_remaining": 850
  },
  "revenue": {
    "today": "1,250.00",
    "this_month": "28,400.00"
  }
}
```

### List Pending Scans

```
GET /api/admin/scans/pending
```

**Response:** Array of pending scans with user info.

### List All Scans

```
GET /api/admin/scans/all
```

| Query Parameter | Type | Default | Description |
|-----------------|------|---------|-------------|
| page | integer | 1 | Page number |
| per_page | integer | 20 | Results per page |
| status | string | null | Filter by status |
| search | string | null | Search user name/email |

### Approve Scan

```
POST /api/admin/scans/{id}/approve
```

Immediately unlocks the report for the user. Sends webhook notification to the client app.

**Response (200):**

```json
{
  "message": "تمت الموافقة على الفحص وإرسال التقرير للعميل",
  "scan": { ... }
}
```

### Reject Scan

```
POST /api/admin/scans/{id}/reject
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| reason | string | No | Rejection reason |

**Response (200):**

```json
{
  "message": "تم رفض الفحص",
  "scan": { ... }
}
```

### Generate PIN

```
POST /api/admin/scans/{id}/generate-pin
```

Generates a unique 4-digit PIN for manual client handoff.

**Response (201):**

```json
{
  "pin_code": "4829",
  "expires_at": "2024-01-20T14:30:00Z"
}
```

### AI Providers

```
GET    /api/admin/ai-providers          # List all providers
POST   /api/admin/ai-providers/{id}/activate  # Activate a provider
PUT    /api/admin/ai-providers/{id}     # Update provider config
```

**Activate Response (200):**

```json
{
  "message": "تم تفعيل مزود الخدمة بنجاح",
  "provider": {
    "id": 2,
    "name": "OpenAI",
    "driver_key": "openai",
    "is_active": true
  }
}
```

### LLM Prompts

```
GET  /api/admin/prompts     # List system prompts
PUT  /api/admin/prompts/{id}  # Update prompt
```

### White-Label Config

```
GET  /api/admin/white-label   # Get config
PUT  /api/admin/white-label   # Update config
```

---

## Product Recommendations

### Get Recommended Products for Scan

```
GET /api/v1/products/recommended/{scanId}
Authorization: Bearer {token}
```

**Response (200):**

```json
{
  "scan_id": 42,
  "defects_detected": ["acne", "oily_skin", "large_pores"],
  "products": [
    {
      "id": 5,
      "name": "غسول الوجه المنقي",
      "name_ar": "غسول الوجه المنقي",
      "description": "منظف عميق للبشرة الدهنية",
      "price": "75.00",
      "currency": "ILS",
      "image_url": "https://jenincare.shop/images/products/cleanser.jpg",
      "matching_reason": "مناسب للبشرة الدهنية المعرضة لحب الشباب — يساعد في تنظيف المسام وتقليل الإفرازات الدهنية",
      "in_stock": true
    }
  ]
}
```

### Add to Cart

```
POST /api/v1/cart/add
Authorization: Bearer {token}
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| product_id | integer | Yes | Product ID |
| quantity | integer | No | Default: 1 |
| scan_id | integer | No | Associated scan for analytics |

---

## Webhook Notifications

The server sends webhook events to the Android app via Laravel broadcasting (Pusher/WebSockets).

### Events

| Event | Channel | Payload |
|-------|---------|---------|
| `scan.approved` | `private-scan.{userId}` | `{ scan_id, status: "approved" }` |
| `scan.rejected` | `private-scan.{userId}` | `{ scan_id, status: "rejected", reason }` |
| `scan.pin_generated` | `private-admin.{adminId}` | `{ scan_id, pin_code }` |

### WebSocket Connection

```
wss://jenincare.shop
```

Authenticate with the same Bearer token.

---

## Error Codes

| HTTP Status | Code | Meaning |
|-------------|------|---------|
| 200 | success | Request succeeded |
| 201 | created | Resource created |
| 400 | bad_request | Invalid request format |
| 401 | unauthenticated | Missing or invalid token |
| 403 | forbidden | Insufficient permissions |
| 404 | not_found | Resource not found |
| 410 | gone | PIN expired or already used |
| 413 | payload_too_large | Upload exceeds size limit |
| 422 | validation_error | Request validation failed |
| 429 | too_many_requests | Rate limit exceeded |
| 500 | server_error | Internal server error |
| 503 | maintenance | System temporarily unavailable |

### Error Response Format

```json
{
  "message": "وصف الخطأ بالعربية",
  "errors": {
    "field_name": ["رسالة التحقق الأولى", "رسالة التحقق الثانية"]
  }
}
```

---

## Rate Limiting

| Endpoint Group | Limit | Window |
|---------------|-------|--------|
| Authentication | 10 requests | per minute |
| Scan Upload | 5 uploads | per minute |
| Scan List | 60 requests | per minute |
| PIN Unlock | 5 attempts | per minute per scan |
| Admin Endpoints | 120 requests | per minute |

Rate limit headers included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1705770000
Retry-After: 30
```

---

## Data Types & Enums

### Scan Status

| Value | Arabic | English |
|-------|--------|---------|
| pending | قيد المراجعة | Under Review |
| approved | تمت الموافقة | Approved |
| rejected | مرفوض | Rejected |

### Engine Type

| Value | Description |
|-------|-------------|
| structured | Numerical data extraction |
| generative | Natural language analysis |
| hybrid | Combined structured + generative |

### Defect Categories

`acne`, `pigmentation`, `dark_circles`, `dryness`, `oiliness`, `pores`, `wrinkles`, `redness`, `texture`, `elasticity`

### Product Categories

`cleanser`, `moisturizer`, `serum`, `sunscreen`, `toner`, `mask`, `exfoliator`, `eye_care`, `treatment`, `supplement`
