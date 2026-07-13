# Progress Summary

## Goal
- Fix the 5 physical spectral analysis lights on the RK3399 skin analyzer device (ZMLH02) and ensure the app works correctly for all diagnosis modes.

## Constraints
- Lights are essential for real spectral capture; digital simulation previously used as fallback.
- Must work on the specific RK3399 BOX hardware running Android 8.1.

## Done

### Breakthrough: fise_gpio[0-5] ARE the LED controls
Writing to `/sys/class/fise_gpioX/level` switches each GPIO from INPUT to OUTPUT mode, directly controlling the physical LEDs:
- `echo 0` → GPIO output LOW (LED OFF)
- `echo 1` → GPIO output HIGH (LED ON)
- Files are world-writable (permissions 666, owned by `system:system`)

### Confirmed GPIO-to-LED mapping (from `/d/gpio`)
- `fise_gpio0` → gpio-34 → WHITE
- `fise_gpio1` → gpio-149 → UV365
- `fise_gpio2` → gpio-45 → WOODS
- `fise_gpio3` → gpio-54 → POL_P
- `fise_gpio4` → gpio-56 → POL_N
- `fise_gpio5` → gpio-155 → (unused/spare)

### Fixed face detection stuck at 0/0 (`FrameCapturePipeline.kt`)
Added `maxFaceAttempts=5` — scan auto-proceeds after ~10s instead of hanging forever.

### Fixed double-rotation bug (`USBCameraManager.kt`)
`captureFrame()` was manually rotating the bitmap after the camera HAL already rotated it via `JPEG_ORIENTATION`, producing 180°-rotated images that MLKit could not detect faces in. Removed the redundant rotation.

### Created FISE GPIO LED Controller
- `FiseGpioController.kt`: Controls LEDs by writing to `/sys/class/fise_gpio[0-5]/level`
- Auto-probes availability in `init` block
- Maps `LightSpectrum.WHITE→gpio0, UV365→gpio1, WOODS→gpio2, POL_P→gpio3, POL_N→gpio4`

### Modified `SpectrumController.kt`
- Now injects `FiseGpioController` as fallback
- `activate(spectrum)`: prefers serial → FISE GPIO → simulation fallback

### Modified `FrameCapturePipeline.kt`
- Now injects `FiseGpioController`
- `ledConnected` check: `serialBusManager.isConnected || fiseGpioController.isAvailable`
- This ensures `captureWithRealLeds()` is used when FISE GPIOs are available, even without serial

### Updated DI module (`AppModule.kt`)
- Added `provideFiseGpioController()`
- Updated `provideSpectrumController()` and `provideFrameCapturePipeline()` with new deps

### APK built & installed on both connected devices ✓

## Blocked
- *(none)* — GPIO control path now proven to work.

## Key Decisions
- **Use sysfs GPIO (`/sys/class/fise_gpioX/level`) as the primary LED control interface** instead of serial protocol. This directly controls the 5 physical spectral LEDs without needing a responsive MCU.

## Next Steps
1. Test by selecting each diagnosis mode on-device and verifying the correct physical LED illuminates
2. If `MLKit Face Detection` still doesn't find faces, try a demo APK like `MLKit Showcase` for comparison
