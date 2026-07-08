param(
    [string]$DeviceIp = "192.168.1.9:5555",
    [string]$ApkPath = "android-app\app\build\outputs\apk\debug\app-debug.apk"
)

Write-Host "SkinAnalyzer - System App Installation Script" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This script installs the app as a SYSTEM app in /system/priv-app/" -ForegroundColor Yellow
Write-Host "After this, the app can set up GPIO/LEDs automatically on boot." -ForegroundColor Yellow
Write-Host "This only needs to be done ONCE." -ForegroundColor Yellow
Write-Host ""

# Ensure ADB connection
Write-Host "Step 1/9: Connecting to device..." -ForegroundColor Yellow
$r = adb connect $DeviceIp 2>&1
if ($r -notmatch "connected") {
    Write-Host "[FAIL] Cannot connect to $DeviceIp" -ForegroundColor Red
    Write-Host "Make sure device is on and USB cable is connected." -ForegroundColor Yellow
    exit 1
}
Write-Host "[OK] Connected to $DeviceIp" -ForegroundColor Green
Write-Host ""

# Verify APK exists
Write-Host "Step 2/9: Verifying APK..." -ForegroundColor Yellow
$apkFull = Join-Path $PSScriptRoot $ApkPath
if (-not (Test-Path $apkFull)) {
    Write-Host "[FAIL] APK not found at: $apkFull" -ForegroundColor Red
    Write-Host "Run '.\gradlew assembleDebug --no-daemon' first." -ForegroundColor Yellow
    exit 1
}
$apkSize = (Get-Item $apkFull).Length / 1MB
Write-Host "[OK] APK found ($([math]::Round($apkSize, 1)) MB)" -ForegroundColor Green
Write-Host ""

# Get root access
Write-Host "Step 3/9: Getting root access..." -ForegroundColor Yellow
$rootResult = adb -s $DeviceIp root 2>&1
if ($rootResult -match "adbd is already running as root") {
    Write-Host "[OK] Already running as root" -ForegroundColor Green
} elseif ($rootResult -match "restarting adbd as root") {
    Write-Host "[OK] ADB restarted as root" -ForegroundColor Green
    Start-Sleep -Seconds 3
    adb connect $DeviceIp | Out-Null
} else {
    Write-Host "[FAIL] Cannot get root access: $rootResult" -ForegroundColor Red
    Write-Host "Device must be running a userdebug/eng build." -ForegroundColor Yellow
    exit 1
}
Write-Host ""

# Remount /system as read-write
Write-Host "Step 4/9: Remounting /system as read-write..." -ForegroundColor Yellow
$remountResult = adb -s $DeviceIp remount 2>&1
if ($remountResult -match "remount succeeded" -or $remountResult -match "already remounted") {
    Write-Host "[OK] /system remounted as read-write" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Remount may have failed: $remountResult" -ForegroundColor Yellow
    Write-Host "Trying alternative method..." -ForegroundColor Yellow
    adb -s $DeviceIp shell "mount -o rw,remount /system" 2>&1 | Out-Null
    Write-Host "[OK] Attempted alternative remount" -ForegroundColor Green
}
Write-Host ""

# Create directories
Write-Host "Step 5/9: Creating directory structure..." -ForegroundColor Yellow
adb -s $DeviceIp shell "mkdir -p /system/priv-app/SkinAnalyzer" 2>&1 | Out-Null
adb -s $DeviceIp shell "mkdir -p /system/etc/permissions" 2>&1 | Out-Null
Write-Host "[OK] Directories created" -ForegroundColor Green
Write-Host ""

# Push APK
Write-Host "Step 6/9: Pushing APK to /system/priv-app/..." -ForegroundColor Yellow
$pushResult = adb -s $DeviceIp push $apkFull /system/priv-app/SkinAnalyzer/SkinAnalyzer.apk 2>&1
if ($pushResult -match "pushed") {
    Write-Host "[OK] APK pushed successfully" -ForegroundColor Green
} else {
    Write-Host "[FAIL] APK push failed: $pushResult" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Push permissions XML
Write-Host "Step 7/9: Pushing permissions whitelist..." -ForegroundColor Yellow
$permXml = Join-Path $PSScriptRoot "privapp-permissions-com.ebtikar.skinanalyzer.pro.xml"
if (-not (Test-Path $permXml)) {
    Write-Host "[FAIL] Permissions XML not found at: $permXml" -ForegroundColor Red
    exit 1
}
$pushPermResult = adb -s $DeviceIp push $permXml /system/etc/permissions/privapp-permissions-com.ebtikar.skinanalyzer.pro.xml 2>&1
if ($pushPermResult -match "pushed") {
    Write-Host "[OK] Permissions XML pushed" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Permissions XML push failed: $pushPermResult" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Set permissions
Write-Host "Step 8/9: Setting file permissions..." -ForegroundColor Yellow
adb -s $DeviceIp shell "chmod 644 /system/priv-app/SkinAnalyzer/SkinAnalyzer.apk" 2>&1 | Out-Null
adb -s $DeviceIp shell "chmod 644 /system/etc/permissions/privapp-permissions-com.ebtikar.skinanalyzer.pro.xml" 2>&1 | Out-Null
adb -s $DeviceIp shell "chown root:root /system/priv-app/SkinAnalyzer/SkinAnalyzer.apk" 2>&1 | Out-Null
adb -s $DeviceIp shell "chown root:root /system/etc/permissions/privapp-permissions-com.ebtikar.skinanalyzer.pro.xml" 2>&1 | Out-Null
Write-Host "[OK] Permissions set" -ForegroundColor Green
Write-Host ""

# Uninstall user version if exists (prevents conflict)
Write-Host "Step 9/9: Cleaning up existing installation..." -ForegroundColor Yellow
$pmPath = adb -s $DeviceIp shell "pm path com.ebtikar.skinanalyzer.pro" 2>&1
if ($pmPath -match "/data/app/") {
    Write-Host "Found user-installed version, removing..." -ForegroundColor Yellow
    adb -s $DeviceIp shell "pm uninstall com.ebtikar.skinanalyzer.pro" 2>&1 | Out-Null
    Write-Host "[OK] User version removed" -ForegroundColor Green
} elseif ($pmPath -match "/system/priv-app/") {
    Write-Host "[OK] Already installed as system app" -ForegroundColor Green
} else {
    Write-Host "[OK] No existing installation found" -ForegroundColor Green
}
Write-Host ""

# Verify
Write-Host "Verifying installation..." -ForegroundColor Cyan
$verifyPath = adb -s $DeviceIp shell "pm path com.ebtikar.skinanalyzer.pro" 2>&1
if ($verifyPath -match "/system/priv-app/") {
    Write-Host "[OK] App is installed as SYSTEM app!" -ForegroundColor Green
    Write-Host "  Path: $verifyPath" -ForegroundColor Green
} else {
    Write-Host "[WARNING] App may not be installed correctly: $verifyPath" -ForegroundColor Yellow
}
Write-Host ""

Write-Host "===== INSTALLATION COMPLETE =====" -ForegroundColor Cyan
Write-Host ""
Write-Host "The app is now installed as a SYSTEM app." -ForegroundColor Green
Write-Host "It can now set up GPIO/LEDs automatically on every boot." -ForegroundColor Green
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Reboot the device: adb -s $DeviceIp reboot" -ForegroundColor Yellow
Write-Host "2. Wait for device to come back online" -ForegroundColor Yellow
Write-Host "3. Open the app - GPIO setup should happen automatically" -ForegroundColor Yellow
Write-Host ""
Write-Host "For future app updates:" -ForegroundColor Cyan
Write-Host "  - Push new APK to GitHub release" -ForegroundColor Cyan
Write-Host "  - App auto-downloads and installs (no USB needed)" -ForegroundColor Cyan
Write-Host ""
$reboot = Read-Host "Reboot device now? (y/n)"
if ($reboot -eq "y") {
    Write-Host "Rebooting..." -ForegroundColor Yellow
    adb -s $DeviceIp reboot
    Write-Host "Done. Wait for device to come back online." -ForegroundColor Green
}
