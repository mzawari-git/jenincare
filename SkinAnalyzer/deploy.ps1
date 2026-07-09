param(
    [string]$DeviceIp = "",
    [string]$ApkPath = "android-app\app\build\outputs\apk\debug\app-debug.apk"
)

Write-Host "SkinAnalyzer - Auto Deploy Script" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

function Find-Device {
    Write-Host "Scanning network for ADB devices..." -ForegroundColor Yellow

    # Check already-connected devices first
    $raw = adb devices 2>&1
    $devices = $raw | Select-String -Pattern "\d+\.\d+\.\d+\.\d+:\d+" | ForEach-Object { ($_ -split "\t")[0] }
    if ($devices.Count -gt 0) {
        Write-Host "[OK] Found connected: $($devices[0])" -ForegroundColor Green
        return $devices[0]
    }

    # Scan common ports on local subnet
    $localIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" -and $_.IPAddress -notlike "100.*" } | Select-Object -First 1).IPAddress
    if (-not $localIP) {
        Write-Host "[FAIL] Cannot determine local IP" -ForegroundColor Red
        return $null
    }
    $subnet = $localIP.Substring(0, $localIP.LastIndexOf('.'))
    Write-Host "Scanning $subnet.x:5555..." -ForegroundColor DarkGray

    # Sequential scan (more reliable than parallel jobs in PS)
    for ($i = 1; $i -le 254; $i++) {
        $ip = "$subnet.$i"
        $tcp = New-Object System.Net.Sockets.TcpClient
        try {
            $result = $tcp.BeginConnect($ip, 5555, $null, $null)
            $success = $result.AsyncWaitHandle.WaitOne(100)
            if ($success) {
                $tcp.EndConnect($result)
                $tcp.Close()
                Write-Host "[OK] Found: $ip:5555" -ForegroundColor Green
                $r = adb connect "$ip:5555" 2>&1
                if ($r -match "connected") {
                    return "$ip:5555"
                }
            }
            $tcp.Close()
        } catch {
            try { $tcp.Close() } catch {}
        }
    }

    # Fallback: try common IPs
    $fallbacks = @("192.168.1.100:5555", "192.168.1.101:5555", "192.168.1.50:5555", "192.168.0.100:5555")
    foreach ($ip in $fallbacks) {
        $r = adb connect $ip 2>&1
        if ($r -match "connected") {
            Write-Host "[OK] Found (fallback): $ip" -ForegroundColor Green
            return $ip
        }
    }

    Write-Host "[FAIL] No ADB device found on network" -ForegroundColor Red
    return $null
}

# Auto-detect or verify
if (-not $DeviceIp) {
    $DeviceIp = Find-Device
    if (-not $DeviceIp) { exit 1 }
} else {
    $r = adb connect $DeviceIp 2>&1
    if ($r -notmatch "connected") {
        Write-Host "Cannot connect to $DeviceIp, scanning..." -ForegroundColor Yellow
        $DeviceIp = Find-Device
        if (-not $DeviceIp) { exit 1 }
    }
}

# Wait a moment for connection to stabilize
Start-Sleep -Seconds 1

# Verify device is reachable
$verify = adb devices 2>&1
if ($verify -notmatch [regex]::Escape($DeviceIp.Split(':')[0])) {
    Write-Host "[WARN] Device may not be ready, retrying connection..." -ForegroundColor Yellow
    adb disconnect | Out-Null
    Start-Sleep -Seconds 1
    adb connect $DeviceIp | Out-Null
    Start-Sleep -Seconds 2
}

Write-Host ""
Write-Host "[OK] Target: $DeviceIp" -ForegroundColor Green

# Verify APK
$apkFull = Join-Path $PSScriptRoot $ApkPath
if (-not (Test-Path $apkFull)) {
    Write-Host "[FAIL] APK not found: $apkFull" -ForegroundColor Red
    Write-Host "Building..." -ForegroundColor Yellow
    Push-Location "$PSScriptRoot\android-app"
    .\gradlew assembleDebug --no-daemon 2>&1 | Out-Null
    Pop-Location
    if (-not (Test-Path $apkFull)) {
        Write-Host "[FAIL] Build failed" -ForegroundColor Red
        exit 1
    }
}

$apkSize = [math]::Round((Get-Item $apkFull).Length / 1MB, 1)
Write-Host "[OK] APK: $apkSize MB" -ForegroundColor Green

# Push
Write-Host ""
Write-Host "Pushing APK..." -ForegroundColor Yellow
$pushResult = adb -s $DeviceIp push $apkFull /data/local/tmp/app-debug.apk 2>&1
if ($pushResult -match "pushed") {
    Write-Host "[OK] Pushed" -ForegroundColor Green
} else {
    Write-Host "[FAIL] $pushResult" -ForegroundColor Red
    Write-Host "Retrying with direct adb..." -ForegroundColor Yellow
    adb connect $DeviceIp | Out-Null
    Start-Sleep -Seconds 2
    $pushResult = adb -s $DeviceIp push $apkFull /data/local/tmp/app-debug.apk 2>&1
    if ($pushResult -match "pushed") {
        Write-Host "[OK] Pushed (retry)" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] Push failed: $pushResult" -ForegroundColor Red
        exit 1
    }
}

# Install
Write-Host "Installing..." -ForegroundColor Yellow
$installResult = adb -s $DeviceIp shell pm install -r /data/local/tmp/app-debug.apk 2>&1
if ($installResult -match "Success") {
    Write-Host "[OK] Installed!" -ForegroundColor Green
} else {
    Write-Host "[FAIL] $installResult" -ForegroundColor Red
    exit 1
}

# Launch
Write-Host "Launching..." -ForegroundColor Yellow
adb -s $DeviceIp shell am start -n com.ebtikar.skinanalyzer.pro/com.ebtikar.skinanalyzer.ui.home.HomeActivity 2>&1 | Out-Null
Write-Host "[OK] App launched!" -ForegroundColor Green

Write-Host ""
Write-Host "===== DEPLOY COMPLETE =====" -ForegroundColor Cyan
Write-Host "Device: $DeviceIp" -ForegroundColor Green
