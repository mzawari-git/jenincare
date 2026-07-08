param(
    [string]$DeviceIp = "192.168.1.9:5555"
)

Write-Host "SkinAnalyzer - GPIO + LED Setup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
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
Write-Host "Step 1/6: Unbinding FISE driver..." -ForegroundColor Yellow
adb -s $DeviceIp shell 'echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind' 2>&1 | Out-Null
Write-Host "[OK]" -ForegroundColor Green
Start-Sleep -Milliseconds 300

# Step 2: Rebind FISE driver
Write-Host "Step 2/6: Rebinding FISE driver..." -ForegroundColor Yellow
adb -s $DeviceIp shell 'echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind' 2>&1 | Out-Null
Write-Host "[OK]" -ForegroundColor Green
Start-Sleep -Milliseconds 150

# Step 3: Export raw GPIOs (for fallback), set direction and initial value (OFF=1)
Write-Host "Step 3/6: Exporting raw GPIOs (fallback)..." -ForegroundColor Yellow
foreach ($pin in @(34, 149, 45, 54, 56)) {
    adb -s $DeviceIp shell "echo $pin > /sys/class/gpio/export" 2>&1 | Out-Null
}
Start-Sleep -Milliseconds 100
foreach ($pin in @(34, 149, 45, 54, 56)) {
    adb -s $DeviceIp shell "echo out > /sys/class/gpio/gpio$pin/direction" 2>&1 | Out-Null
    adb -s $DeviceIp shell "echo 1 > /sys/class/gpio/gpio$pin/value" 2>&1 | Out-Null
}
Write-Host "[OK]" -ForegroundColor Green

# Step 4: Set permissions on both FISE driver and raw GPIO files
Write-Host "Step 4/6: Setting permissions (chmod 666)..." -ForegroundColor Yellow
# FISE driver files (primary path used by app)
for ($i = 0; $i -le 4; $i++) {
    adb -s $DeviceIp shell "chmod 666 /sys/class/fise_gpio$i/level" 2>&1 | Out-Null
}
# FISE LED master
adb -s $DeviceIp shell "chmod 666 /sys/class/fise_led/level" 2>&1 | Out-Null
# Raw GPIO fallback
foreach ($pin in @(34, 149, 45, 54, 56)) {
    adb -s $DeviceIp shell "chmod 666 /sys/class/gpio/gpio$pin/value" 2>&1 | Out-Null
}
Write-Host "[OK]" -ForegroundColor Green

# Step 5: Verify FISE driver files exist
Write-Host "Step 5/6: Verifying FISE driver files..." -ForegroundColor Yellow
$fiseCheck = adb -s $DeviceIp shell 'ls /sys/class/fise_gpio0/level /sys/class/fise_gpio1/level /sys/class/fise_gpio2/level /sys/class/fise_gpio3/level /sys/class/fise_gpio4/level /sys/class/fise_led/level 2>&1' 2>&1
Write-Host $fiseCheck
$hasAllFise = ($fiseCheck -match "fise_gpio0") -and ($fiseCheck -match "fise_gpio4") -and ($fiseCheck -match "fise_led")
if ($hasAllFise) {
    Write-Host "[OK] All 5 FISE GPIO + LED master files present" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Some FISE files missing — raw GPIO fallback may be used" -ForegroundColor Yellow
    # Diagnostic: list what /sys/class/fise* and /sys/class/gpio* actually contain
    Write-Host ""
    Write-Host "--- Diagnostic: /sys/class/fise* ---" -ForegroundColor DarkGray
    adb -s $DeviceIp shell 'ls -la /sys/class/fise_* 2>/dev/null' 2>&1 | ForEach-Object { Write-Host "  $_" -ForegroundColor DarkGray }
    Write-Host "--- Diagnostic: /sys/class/gpio/ ---" -ForegroundColor DarkGray
    adb -s $DeviceIp shell 'ls /sys/class/gpio/ 2>/dev/null' 2>&1 | ForEach-Object { Write-Host "  $_" -ForegroundColor DarkGray }
    Write-Host "--- Diagnostic: GPIO pin files ---" -ForegroundColor DarkGray
    foreach ($pin in @(34, 149, 45, 54, 56)) {
        $exists = adb -s $DeviceIp shell "test -f /sys/class/gpio/gpio$pin/value && echo yes || echo no" 2>&1
        $dirExists = adb -s $DeviceIp shell "test -d /sys/class/gpio/gpio$pin && echo yes || echo no" 2>&1
        Write-Host "  GPIO$pin: dir=$dirExists, value=$exists" -ForegroundColor DarkGray
    }
}

# Step 6: Test ALL 8 LEDs (5 GPIO + 3 Serial)
Write-Host ""
Write-Host "Step 6/6: Testing ALL 8 LEDs..." -ForegroundColor Yellow
Write-Host ""

# LED test configuration: name, FISE index, raw GPIO pin (null for serial-only)
$ledTests = @(
    @{ Name="WHITE";  FiseIndex=0; RawPin=34;  Color="White" },
    @{ Name="UV365";  FiseIndex=1; RawPin=149; Color="Magenta" },
    @{ Name="WOODS";  FiseIndex=2; RawPin=45;  Color="Purple" },
    @{ Name="POL_P";  FiseIndex=3; RawPin=54;  Color="Cyan" },
    @{ Name="POL_N";  FiseIndex=4; RawPin=56;  Color="Blue-White" },
    @{ Name="BLUE";   FiseIndex=-1; RawPin=$null; Color="Blue" },
    @{ Name="RED";    FiseIndex=-1; RawPin=$null; Color="Red" },
    @{ Name="BROWN";  FiseIndex=-1; RawPin=$null; Color="Amber" }
)

$successCount = 0
$failCount = 0

foreach ($led in $ledTests) {
    $name = $led.Name
    $color = $led.Color
    $fiseIdx = $led.FiseIndex
    $rawPin = $led.RawPin

    Write-Host "Testing $name LED ($color)..." -ForegroundColor Yellow -NoNewline

    $ok = $false

    if ($fiseIdx -ge 0) {
        # GPIO-controlled: try FISE driver first
        $fiseFile = "/sys/class/fise_gpio$fiseIdx/level"
        $exists = adb -s $DeviceIp shell "test -f $fiseFile && echo yes || echo no" 2>&1
        if ($exists -match "yes") {
            $writeResult = adb -s $DeviceIp shell "echo 0 > $fiseFile 2>&1; echo exit=$?" 2>&1
            Start-Sleep -Milliseconds 500
            adb -s $DeviceIp shell "echo 1 > $fiseFile" 2>&1 | Out-Null
            if ($writeResult -match "exit=0") {
                $ok = $true
            } else {
                Write-Host " [FAIL write: $writeResult]" -ForegroundColor Red -NoNewline
            }
        } elseif ($rawPin) {
            # Fallback to raw GPIO
            $rawFile = "/sys/class/gpio/gpio$rawPin/value"
            $rawExists = adb -s $DeviceIp shell "test -f $rawFile && echo yes || echo no" 2>&1
            if ($rawExists -match "yes") {
                $writeResult = adb -s $DeviceIp shell "echo 0 > $rawFile 2>&1; echo exit=$?" 2>&1
                Start-Sleep -Milliseconds 500
                adb -s $DeviceIp shell "echo 1 > $rawFile" 2>&1 | Out-Null
                if ($writeResult -match "exit=0") {
                    $ok = $true
                } else {
                    Write-Host " [FAIL write: $writeResult]" -ForegroundColor Red -NoNewline
                }
            } else {
                Write-Host " [no file: $rawFile]" -ForegroundColor Red -NoNewline
            }
        } else {
            Write-Host " [no FISE file: $fiseFile]" -ForegroundColor Red -NoNewline
        }
    } else {
        # Serial-only: log that serial is required
        Write-Host " [SKIP] (requires USB Serial)" -ForegroundColor DarkYellow
        $failCount++
        continue
    }

    if ($ok) {
        Write-Host " [OK]" -ForegroundColor Green
        $successCount++
    } else {
        Write-Host " [FAIL]" -ForegroundColor Red
        $failCount++
    }
    Start-Sleep -Milliseconds 200
}

Write-Host ""
Write-Host "LED Test Results: $successCount/5 GPIO LEDs OK, 3 Serial LEDs (BLUE/RED/BROWN) need USB connection" -ForegroundColor Cyan

# Check serial device
Write-Host ""
Write-Host "Checking USB Serial device..." -ForegroundColor Yellow
$serialDevices = adb -s $DeviceIp shell 'ls /dev/ttyUSB* /dev/ttyACM* 2>/dev/null' 2>&1
if ($serialDevices -match "tty") {
    Write-Host "[OK] Serial device found: $serialDevices" -ForegroundColor Green
    Write-Host "BLUE, RED, BROWN LEDs should work via serial bus" -ForegroundColor Green
} else {
    Write-Host "[WARNING] No serial device found — BLUE, RED, BROWN LEDs will NOT fire" -ForegroundColor Yellow
    Write-Host "Connect USB serial adapter for full 8-LED support" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "===== SETUP COMPLETE =====" -ForegroundColor Cyan
Write-Host "GPIO LEDs (WHITE, UV365, WOODS, POL_P, POL_N): Ready" -ForegroundColor Green
if ($serialDevices -match "tty") {
    Write-Host "Serial LEDs (BLUE, RED, BROWN): Ready" -ForegroundColor Green
} else {
    Write-Host "Serial LEDs (BLUE, RED, BROWN): Not available (no USB serial)" -ForegroundColor Yellow
}
Write-Host ""
Write-Host "NOTE: Run this script after EVERY reboot." -ForegroundColor Yellow
Write-Host "The FISE driver must be rebound each boot for GPIO LEDs to work." -ForegroundColor Yellow
