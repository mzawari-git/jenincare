param(
    [string]$DeviceIp = "192.168.1.9:5555"
)

Write-Host "SkinAnalyzer - GPIO Setup Script" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Ensure ADB connection
$r = adb connect $DeviceIp 2>&1
if ($r -notmatch "connected") {
    Write-Host "[FAIL] Cannot connect to $DeviceIp" -ForegroundColor Red
    Write-Host "Make sure device is on and ADB over TCP/IP is enabled." -ForegroundColor Yellow
    exit 1
}
Write-Host "[OK] Connected to $DeviceIp" -ForegroundColor Green
Write-Host ""

# Step 1: Unbind FISE driver
Write-Host "Step 1/5: Unbinding FISE driver..." -ForegroundColor Yellow
adb -s $DeviceIp shell 'echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind' 2>&1 | Out-Null
Write-Host "[OK]" -ForegroundColor Green
Start-Sleep -Milliseconds 300

# Step 2: Rebind FISE driver
Write-Host "Step 2/5: Rebinding FISE driver..." -ForegroundColor Yellow
adb -s $DeviceIp shell 'echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind' 2>&1 | Out-Null
Write-Host "[OK]" -ForegroundColor Green
Start-Sleep -Milliseconds 150

# Step 3: Export GPIOs, set direction and initial value
Write-Host "Step 3/5: Exporting GPIOs..." -ForegroundColor Yellow
adb -s $DeviceIp shell 'echo 34 > /sys/class/gpio/export' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 149 > /sys/class/gpio/export' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 45 > /sys/class/gpio/export' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 54 > /sys/class/gpio/export' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 56 > /sys/class/gpio/export' 2>&1 | Out-Null
Start-Sleep -Milliseconds 100
adb -s $DeviceIp shell 'echo out > /sys/class/gpio/gpio34/direction' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo out > /sys/class/gpio/gpio149/direction' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo out > /sys/class/gpio/gpio45/direction' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo out > /sys/class/gpio/gpio54/direction' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo out > /sys/class/gpio/gpio56/direction' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 1 > /sys/class/gpio/gpio34/value' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 1 > /sys/class/gpio/gpio149/value' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 1 > /sys/class/gpio/gpio45/value' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 1 > /sys/class/gpio/gpio54/value' 2>&1 | Out-Null
adb -s $DeviceIp shell 'echo 1 > /sys/class/gpio/gpio56/value' 2>&1 | Out-Null
Write-Host "[OK]" -ForegroundColor Green

# Step 4: Set permissions to world-writable
Write-Host "Step 4/5: Setting chmod 666..." -ForegroundColor Yellow
adb -s $DeviceIp shell 'chmod 666 /sys/class/gpio/gpio34/value'
adb -s $DeviceIp shell 'chmod 666 /sys/class/gpio/gpio149/value'
adb -s $DeviceIp shell 'chmod 666 /sys/class/gpio/gpio45/value'
adb -s $DeviceIp shell 'chmod 666 /sys/class/gpio/gpio54/value'
adb -s $DeviceIp shell 'chmod 666 /sys/class/gpio/gpio56/value'
Write-Host "[OK]" -ForegroundColor Green

# Step 5: Verify
Write-Host "Step 5/5: Verifying..." -ForegroundColor Yellow
$verify = adb -s $DeviceIp shell 'ls -la /sys/class/gpio/gpio34/value /sys/class/gpio/gpio149/value /sys/class/gpio/gpio45/value /sys/class/gpio/gpio54/value /sys/class/gpio/gpio56/value' 2>&1
Write-Host $verify
if ($verify -match "gpio34" -and $verify -match "rw-rw-rw-") {
    Write-Host ""
    Write-Host "[SUCCESS] All 5 GPIO pins are ready!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "[WARNING] GPIO files exist but permissions may not be 666" -ForegroundColor Yellow
}

# Test WHITE LED
Write-Host ""
Write-Host "Testing WHITE LED..." -ForegroundColor Yellow
Start-Sleep -Milliseconds 200
adb -s $DeviceIp shell 'echo 0 > /sys/class/gpio/gpio34/value'
Start-Sleep -Milliseconds 600
adb -s $DeviceIp shell 'echo 1 > /sys/class/gpio/gpio34/value'
Write-Host "[OK] WHITE LED should have flashed" -ForegroundColor Green

Write-Host ""
Write-Host "===== SETUP COMPLETE =====" -ForegroundColor Cyan
Write-Host "The app can now control the diagnosis LEDs." -ForegroundColor Cyan
Write-Host "NOTE: Run this script after EVERY reboot." -ForegroundColor Yellow
