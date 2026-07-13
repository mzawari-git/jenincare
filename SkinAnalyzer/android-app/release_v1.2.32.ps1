$ErrorActionPreference = "Stop"
$env:PATH += ";C:\Program Files\Git\bin;C:\Program Files\Git\cmd"

$REPO_OWNER  = "mzawari-git"
$REPO_NAME   = "jenincare"
$GITHUB_TOKEN = $env:GITHUB_TOKEN
$VERSION     = "1.2.32"
$TAG         = "v$VERSION"
$APK_PATH    = "app\build\outputs\apk\debug\app-debug.apk"
$APK_NAME    = "SkinAnalyzer-$TAG.apk"
$APP_DIR     = "C:\xampp\htdocs\jenincare\SkinAnalyzer\android-app"

Set-Location $APP_DIR

# 1. Build
Write-Host "[1/5] Building APK v$VERSION..." -ForegroundColor Cyan
.\gradlew --stop | Out-Null
.\gradlew assembleDebug
if ($LASTEXITCODE -ne 0) { Write-Error "Build FAILED"; exit 1 }
if (-not (Test-Path $APK_PATH)) { Write-Error "APK not found"; exit 1 }
Write-Host "[1/5] Build SUCCESS" -ForegroundColor Green

# 2. Rename APK
$APK_DEST = Join-Path $APP_DIR $APK_NAME
Copy-Item (Join-Path $APP_DIR $APK_PATH) $APK_DEST -Force
Write-Host "[2/5] APK ready: $APK_NAME" -ForegroundColor Green

# 3. Git Commit & Tag
Write-Host "[3/5] Committing..." -ForegroundColor Cyan
git -C $APP_DIR add "app/src/main/java/com/ebtikar/skinanalyzer/camera/FrameCapturePipeline.kt" "app/src/main/java/com/ebtikar/skinanalyzer/ui/analysis/AnalysisActivity.kt" "app/src/main/java/com/ebtikar/skinanalyzer/ui/settings/SettingsActivity.kt" "app/src/main/java/com/ebtikar/skinanalyzer/util/UpdateChecker.kt" "app/build.gradle.kts"
git -C $APP_DIR commit -m "${TAG}: Fix lighting pre-check, remove fake placeholders, add rollback"
git -C $APP_DIR tag -d $TAG 2>$null; git -C $APP_DIR push origin ":refs/tags/$TAG" 2>$null
git -C $APP_DIR tag $TAG
git -C $APP_DIR push origin main
git -C $APP_DIR push origin $TAG
Write-Host "[3/5] Git done" -ForegroundColor Green

# 4. Create GitHub Release
Write-Host "[4/5] Creating Release on GitHub..." -ForegroundColor Cyan
$headers = @{ "Authorization"="token $GITHUB_TOKEN"; "Accept"="application/vnd.github.v3+json"; "Content-Type"="application/json" }
$body = @{ tag_name=$TAG; target_commitish="main"; name="$TAG - Fix Lighting + Rollback"; body="## v${VERSION}`n- Hardware pre-check before scan`n- Remove fake placeholder frames`n- Add rollback feature in settings"; draft=$false; prerelease=$false } | ConvertTo-Json
$rel = Invoke-RestMethod -Uri "https://api.github.com/repos/$REPO_OWNER/$REPO_NAME/releases" -Method Post -Headers $headers -Body $body
Write-Host "[4/5] Release created: $($rel.html_url)" -ForegroundColor Green

# 5. Upload APK
Write-Host "[5/5] Uploading APK..." -ForegroundColor Cyan
$upHeaders = @{ "Authorization"="token $GITHUB_TOKEN"; "Content-Type"="application/vnd.android.package-archive" }
$upUrl = "https://uploads.github.com/repos/$REPO_OWNER/$REPO_NAME/releases/$($rel.id)/assets?name=$APK_NAME"
$apkBytes = [System.IO.File]::ReadAllBytes($APK_DEST)
$up = Invoke-RestMethod -Uri $upUrl -Method Post -Headers $upHeaders -Body $apkBytes
Write-Host "[5/5] APK uploaded: $($up.browser_download_url)" -ForegroundColor Green
Write-Host "`n=== DONE: https://github.com/$REPO_OWNER/$REPO_NAME/releases/tag/$TAG ===" -ForegroundColor Yellow
