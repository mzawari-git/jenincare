#!/system/bin/sh
# =============================================================================
# JeninCare Skin Analyzer — GPIO Setup Script for ZMLH02
# =============================================================================
# Place at: /vendor/bin/setup_gpio.sh
# Permissions: chmod 755 /vendor/bin/setup_gpio.sh
#
# This script can be called from:
#   1. init.rc: exec /vendor/bin/setup_gpio.sh
#   2. BootReceiver (if app has shell permissions)
#   3. Manual: adb shell /vendor/bin/setup_gpio.sh
# =============================================================================

LOG_TAG="ZMLH02_GPIO"
PINS="34 149 45 54 56"

log_msg() {
    log -t "$LOG_TAG" "$1"
    echo "$(date '+%H:%M:%S') $LOG_TAG: $1"
}

log_msg "Starting GPIO setup for LED ring..."

# Step 1: Unbind FISE driver to release pinctrl claims
if [ -d "/sys/bus/platform/drivers/fise_gpio" ]; then
    echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind 2>/dev/null
    if [ $? -eq 0 ]; then
        log_msg "FISE driver unbound successfully"
    else
        log_msg "WARNING: FISE driver unbind failed (may already be unbound)"
    fi
    sleep 1
else
    log_msg "FISE driver directory not found, skipping unbind"
fi

# Step 2: Export GPIO pins
for pin in $PINS; do
    if [ ! -d "/sys/class/gpio/gpio${pin}" ]; then
        echo $pin > /sys/class/gpio/export 2>/dev/null
        if [ $? -ne 0 ]; then
            log_msg "ERROR: Failed to export GPIO $pin"
        fi
    fi
done
sleep 1

# Step 3: Configure pins as output, set OFF (active LOW: 1=OFF), set permissions
for pin in $PINS; do
    if [ -d "/sys/class/gpio/gpio${pin}" ]; then
        echo out > /sys/class/gpio/gpio${pin}/direction 2>/dev/null
        echo 1 > /sys/class/gpio/gpio${pin}/value 2>/dev/null
        chmod 666 /sys/class/gpio/gpio${pin}/value 2>/dev/null
        chmod 666 /sys/class/gpio/gpio${pin}/direction 2>/dev/null
        log_msg "GPIO $pin: configured (OFF)"
    else
        log_msg "ERROR: GPIO $pin directory does not exist after export"
    fi
done

# Step 4: Verify
SUCCESS=0
FAIL=0
for pin in $PINS; do
    if [ -f "/sys/class/gpio/gpio${pin}/value" ]; then
        VALUE=$(cat /sys/class/gpio/gpio${pin}/value 2>/dev/null)
        if [ "$VALUE" = "1" ]; then
            SUCCESS=$((SUCCESS + 1))
        else
            log_msg "WARNING: GPIO $pin value=$VALUE (expected 1=OFF)"
            FAIL=$((FAIL + 1))
        fi
    else
        log_msg "ERROR: GPIO $pin value file missing"
        FAIL=$((FAIL + 1))
    fi
done

log_msg "GPIO setup complete: $SUCCESS OK, $FAIL failed out of 5 pins"

if [ $FAIL -eq 0 ]; then
    log_msg "LED ring GPIO ready — all LEDs OFF"
else
    log_msg "WARNING: Some GPIO pins may not be working correctly"
fi
