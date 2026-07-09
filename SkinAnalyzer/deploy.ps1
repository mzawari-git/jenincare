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
    $devices = adb devices 2>&1 | Select-String -Pattern "\d+\.\d+\.\d+\.\d+:\d+" | ForEach-Object { ($_ -split "\s+")[0] }
    if ($devices) {
        Write-Host "[OK] Found connected device: $($devices[0])" -ForegroundColor Green
        return $devices[0]
    }

    # Scan local network for port 5555
    $localIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" -and $_.IPAddress -notlike "100.*" } | Select-Object -First 1).IPAddress
    if (-not $localIP) {
        Write-Host "[FAIL] Cannot determine local IP" -ForegroundColor Red
        return $null
    }

    $subnet = $localIP.Substring(0, $localIP.LastIndexOf('.'))
    Write-Host "Scanning subnet $subnet.x:5555..." -ForegroundColor DarkGray

    $found = $null
    1..254 | ForEach-Object {
        $ip = "$subnet.$_:5555"
        $job = Start-Job -ScriptBlock {
            param($ip)
            $tcp = New-Object System.Net.Sockets.TcpClient
            try {
                $tcp.Connect($ip.Split(':')[0], [int]$ip.Split(':')[1])
                $tcp.Close()
                return $ip
            } catch {
                return $null
            }
        } -ArgumentList $ip
        $jobs += $job
    }

    $jobs | Wait-Job -Timeout 5 | Out-Null
    foreach ($job in $jobs) {
        $result = Receive-Job $job
        if ($result) { $found = $result; break }
        Remove-Job $job -Force
    }
    $jobs | Remove-Job -Force -ErrorAction SilentlyContinue

    if ($found) {
        Write-Host "[OK] Found device at: $found" -ForegroundColor Green
        $r = adb connect $found 2>&1
        if ($r -match "connected") {
            return $found
        }
    }

    # Fallback: try common IPs
    $commonIPs = @("192.168.1.100:5555", "192.168.1.50:5555", "192.168.0.100:5555", "192.168.0.50:5555")
    foreach ($ip in $commonIPs) {
        $r = adb connect $ip 2>&1
        if ($r -match "connected") {
            Write-Host "[OK] Found device at: $ip" -ForegroundColor Green
            return $ip
        }
    }

    Write-Host "[FAIL] No ADB device found on network" -ForegroundColor Red
    Write-Host "Make sure:" -ForegroundColor Yellow
    Write-Host "  1. Device is powered on" -ForegroundColor Yellow
    Write-Host "  2. ADB over TCP/IP is enabled (adb tcpip 5555)" -ForegroundColor Yellow
    Write-Host "  3. Device is on the same network" -ForegroundColor Yellow
    return $null
}

# Auto-detect device if not specified
if (-not $DeviceIp) {
    $DeviceIp = Find-Device
    if (-not $DeviceIp) { exit 1 }
} else {
    $r = adb connect $DeviceIp 2>&1
    if ($r -notmatch "connected") {
        Write-Host "[FAIL] Cannot connect to $DeviceIp, trying auto-detect..." -ForegroundColor Yellow
        $DeviceIp = Find-Device
        if (-not $DeviceIp) { exit 1 }
    }
}

Write-Host ""
Write-Host "[OK] Connected to $DeviceIp" -ForegroundColor Green

# Verify APK exists
$apkFull = Join-Path $PSScriptRoot $ApkPath
if (-not (Test-Path $apkFull)) {
    Write-Host "[FAIL] APK not found at: $apkFull" -ForegroundColor Red
    Write-Host "Building APK..." -ForegroundColor Yellow
    Push-Location "$PSScriptRoot\android-app"
    .\gradlew assembleDebug --no-daemon 2>&1 | Out-Null
    Pop-Location
    if (-not (Test-Path $apkFull)) {
        Write-Host "[FAIL] Build failed. APK still not found." -ForegroundColor Red
        exit 1
    }
}

$apkSize = [math]::Round((Get-Item $apkFull).Length / 1MB, 1)
Write-Host "[OK] APK: $apkSize MB" -ForegroundColor Green

# Push APK
Write-Host ""
Write-Host "Pushing APK to device..." -ForegroundColor Yellow
$pushResult = adb -s $DeviceIp push $apkFull /data/local/tmp/app-debug.apk 2>&1
if ($pushResult -match "pushed") {
    Write-Host "[OK] APK pushed" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Push failed: $pushResult" -ForegroundColor Red
    exit 1
}

# Install
Write-Host "Installing..." -ForegroundColor Yellow
$installResult = adb -s $DeviceIp shell pm install -r /data/local/tmp/app-debug.apk 2>&1
if ($installResult -match "Success") {
    Write-Host "[OK] Installed successfully!" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Install failed: $installResult" -ForegroundColor Red
    exit 1
}

# Launch
Write-Host "Launching app..." -ForegroundColor Yellow
adb -s $DeviceIp shell am start -n com.ebtikar.skinanalyzer.pro/com.ebtikar.skinanalyzer.ui.home.HomeActivity 2>&1 | Out-Null
Write-Host "[OK] App launched!" -ForegroundColor Green

Write-Host ""
Write-Host "===== DEPLOY COMPLETE =====" -ForegroundColor Cyan
Write-Host "Device: $DeviceIp" -ForegroundColor Green
Write-Host "Version: 1.2.51" -ForegroundColor Green
