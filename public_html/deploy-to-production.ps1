# ═══════════════════════════════════════════════════════════════
# شركة جنين للتجميل Auto-Deploy Script (Local Windows → Production)
# ═══════════════════════════════════════════════════════════════
# 
# This script pushes changes to GitHub then triggers a
# webhook deploy on your production server (www.jenincare.shop)
#
# USAGE:
#   From PowerShell in your project root:
#   .\deploy-to-production.ps1
#
# PREREQUISITES:
#   1. GitHub webhook set up at www.jenincare.shop/deploy.php
#   2. Git remote 'origin' is configured
# ═══════════════════════════════════════════════════════════════

$ErrorActionPreference = "Stop"
$deployUrl = "https://www.jenincare.shop/deploy.php"
$deploySecret = "jenincare-deploy-2026"

Write-Host "═══ شركة جنين للتجميل Auto Deploy ═══" -ForegroundColor Cyan

# Step 1: Push to GitHub
Write-Host "[1/3] Pushing to GitHub..." -ForegroundColor Yellow
$status = git status --short
if ($status) {
    git add .
    $commitMsg = Read-Host "Enter commit message (or press Enter for auto)"
    if (-not $commitMsg) { $commitMsg = "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm')" }
    git commit -m $commitMsg
}
git push origin master
Write-Host "  Pushed to GitHub." -ForegroundColor Green

# Step 2: Trigger webhook deploy on production
Write-Host "[2/3] Triggering deploy on www.jenincare.shop..." -ForegroundColor Yellow
$payload = @{ ref = "refs/heads/master" } | ConvertTo-Json
$signature = "sha256=" + (Get-HMAC -Text $payload -Key $deploySecret -Algorithm SHA256)

try {
    $response = Invoke-WebRequest -Uri $deployUrl -Method POST -Body $payload `
        -ContentType "application/json" `
        -Headers @{ "X-Hub-Signature-256" = $signature } `
        -TimeoutSec 30
    Write-Host "  Deploy triggered: $($response.Content)" -ForegroundColor Green
} catch {
    Write-Host "  Webhook failed. Use manual deploy option below." -ForegroundColor Red
}

# Step 3: Done
Write-Host "[3/3] Done!" -ForegroundColor Green
Write-Host ""
Write-Host "If webhook failed, deploy manually on your server:" -ForegroundColor Yellow
Write-Host "  1. SSH to jenincare.shop" -ForegroundColor White
Write-Host "  2. cd public_html && git pull origin master" -ForegroundColor White
Write-Host "  3. php artisan migrate --force && php artisan optimize:clear" -ForegroundColor White

function Get-HMAC {
    param([string]$Text, [string]$Key, [string]$Algorithm = "SHA256")
    $hmac = New-Object System.Security.Cryptography.HMACSHA256
    $hmac.Key = [Text.Encoding]::UTF8.GetBytes($Key)
    $hash = $hmac.ComputeHash([Text.Encoding]::UTF8.GetBytes($Text))
    return [BitConverter]::ToString($hash) -replace '-',''
}
