# AGENTS.md

## Terminal Rules

- NEVER run long-running or blocking commands (gradle builds, adb install, dev servers) in the current terminal window
- ALWAYS use a new/separate terminal window for:
  - `gradlew assemble*` or `gradlew build`
  - `adb install` or `adb` commands that take time
  - Any command that takes more than 30 seconds
  - Any build/compile commands
- Use `start` (Windows) or `open -a Terminal` (Mac) to launch commands in a new window when needed
- For Gradle builds specifically, prefer running them detached so they don't block the conversation

## GPIO & Camera Summary

### GPIO (RK3399, ZMLH02, FISE)
- **5 diagnosis LEDs** (gpio155/GPIO5 is unused and removed from `gpioMap`).
- **All active LOW**: write `0`=ON, `1`=OFF.
- GPIO mapping: **0→34** (WHITE), **1→149** (UV365), **2→45** (WOODS), **3→54** (POL_P), **4→56** (POL_N).
- Permissions: `chmod 666 /sys/class/gpio/gpioN/value` after export.
- **GPIO export fails on fresh boot** because FISE driver claims pins via pinctrl. **Fix**: run `adb shell` from PowerShell with **single quotes**:
  ```
  adb shell 'echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/unbind'
  adb shell 'echo fise_gpio > /sys/bus/platform/drivers/fise_gpio/bind'
  ```
  Then export GPIOs (use single quotes to preserve `$pin` in PowerShell):
  ```
  adb shell 'for pin in 34 149 45 54 56; do echo $pin > /sys/class/gpio/export; echo out > /sys/class/gpio/gpio$pin/direction; echo 1 > /sys/class/gpio/gpio$pin/value; chmod 666 /sys/class/gpio/gpio$pin/value; done'
  ```
- **Boot persistence**: Exports are **lost after every reboot**. The app cannot re-export GPIOs because it lacks root. **Must run `setup_gpio.ps1`** (in project root) after every power cycle.
- **Helper script**: `setup_gpio.ps1` — run in PowerShell: `.\setup_gpio.ps1`
- **PowerShell single-quote requirement**: Always use single quotes `'...'` in `adb shell` commands containing `$` — double quotes cause PowerShell to expand `$pin` as an empty variable.
- **CONFIG_STRICT_DEVMEM** is enabled in the kernel — `/dev/mem` cannot access GPIO registers. Direct register write approach is blocked.
- **Important**: Always write `1` (OFF) immediately after `direction=out` to prevent default-ON state.

### Camera (OV13850, ID 0)
- **CameraX** used in HomeActivity for preview. **Camera2** used in AnalysisActivity for frame capture.
- **Conflict**: CameraX holds the camera; Camera2 `open()` silently hangs (onOpened never fires).
- **Fix**: `AnalysisActivity.onSurfaceTextureAvailable()` calls `ProcessCameraProvider.getInstance(this).await().unbindAll()` + **1500ms** delay before `initializeAnalysis()`.
- **Capture settings per spectrum** (USBCameraManager.captureFrame()):
  - UV365/WOODS: `CONTROL_MODE_AUTO` + `AWB_MODE_DAYLIGHT` + `AE_LOCK=true` (UV is mostly invisible so AUTO mode lets exposure adjust)
  - Other spectra: `CONTROL_MODE_AUTO` + `AWB_MODE_AUTO` (no AWB lock — lets camera adjust per LED color)
- Camera HAL is "Marvin" for OV13850 on I2C bus #3. Sometimes emits exposure warnings but works.
- Streaming 10 FPS at 4224x3136 sensor resolution, downscaled for Camera2 captures.

### Analysis pipeline
- `SkinAnalysisRepositoryImpl.analyzeImages()` tries: Cloud → TFLite (stubbed) → Advanced (MediaPipe) → OpenCV (CVUtils) → **zero fallback** (metrics all 0, no mock data).
- Both AdvancedSkinAnalyzer and OpenCVSkinAnalyzer have safety nets that should always produce metrics if frame files exist and are decodable.
- `FrameCapturePipeline` checks `ImageUtils.saveBitmap()` return value — failures are logged.
- Logging added at every analysis stage to trace empty-results cascade.

### Compilation notes
- `ProcessCameraProvider` import: `androidx.camera.lifecycle.ProcessCameraProvider` (NOT `camera.core`).
- `await()` from `kotlinx-coroutines-guava` for ListenableFuture.
- Build runs in separate PowerShell window via `Start-Process pwsh -ArgumentList "-NoExit", "-Command", "cd 'path'; .\gradlew assembleDebug"`.
- **Kotlin daemon** often fails to connect on first build after daemon restart. Run `.\gradlew --stop` then rebuild.

## Regression Testing Rules

**BEFORE EVERY COMMIT/RELEASE, ALL of these MUST pass on device:**

1. App launches without crash
2. Home screen: hero text, scan button, stats, diagnosis cards visible
3. Tap "ابدأ الفحص" → AnalysisActivity opens
4. Camera preview: full face visible (not cropped)
5. Face detection: message changes to "جاري قراءة ملامح الوجه"
6. 8-spectrum capture: all 8 LEDs fire (check logcat for each spectrum)
7. Analysis completes: score > 0, metrics > 0 (not all zeros)
8. Report screen: score gauge, radar chart, metrics table visible
9. PDF share: generates and opens in viewer
10. History screen: past reports listed
11. Settings screen: opens, toggles save preferences
12. Back navigation: no crash on any screen

**If ANY test fails → DO NOT RELEASE. Fix first.**

**Logcat filters for debugging:**
```
adb logcat -s "FrameCapturePipeline:*" "CVUtils:*" "USBCameraManager:*" "FaceLandmarkDetector:*" "AnalysisViewModel:*"
```

**Build & deploy (auto-detect device):**
```powershell
# Option 1: Use deploy.ps1 (auto-detects device IP)
.\deploy.ps1

# Option 2: Manual build + deploy
cd android-app
.\gradlew --stop
.\gradlew assembleDebug --no-daemon
# Auto-detect device and push:
$device = (adb devices 2>&1 | Select-String "\d+\.\d+\.\d+\.\d+:\d+").Matches[0].Value
adb -s $device push app\build\outputs\apk\debug\app-debug.apk /data/local/tmp/
adb -s $device shell pm install -r /data/local/tmp/app-debug.apk
```

## Session Progress (2026-07-05)

### Summary
Released v1.2.16 through v1.2.21. Fixed face detection (HSV ranges, ML Kit thresholds, threshold capping, bypass logic), redesigned home screen UI, professional PDF report, AI face reading flow, bigger scan button, and Arabic hero text update.

### Changes
1. **v1.2.16** — Initial stable release with full analysis pipeline
2. **v1.2.17** — Fixed face detection after viewing results (analysisInitialized flag, fresh Surface re-fetch)
3. **v1.2.18** — Fixed face detection false negatives (widened HSV ranges, lowered ML Kit minFaceSize to 0.05)
4. **v1.2.19** — Fixed face detection threshold (capped at 85, bypass: coverage>=10% + score>=50)
5. **v1.2.20** — Home UI redesign (glassmorphism hero, diagnosis cards, avg score stat) + professional 3-page PDF
6. **v1.2.21** — Hero text "تحليل البشرة في الذكاء الاصطناعي", bigger scan button (220x52dp), face reading flow (detect → read face → capture)

### Critical Notes
- **GPIO requires `setup_gpio.ps1` after every boot** — app cannot re-export GPIOs as untrusted_app
- **Camera preview fix**: `rotateTextureView()` always applies aspect-ratio fill scaling
- **Face detection flow**: HSV skin detection → ML Kit fallback (every 5 attempts) → final ML Kit fallback → "جاري قراءة ملامح الوجه" → 2.5s face reading → 8-spectrum capture
- **Analysis pipeline**: Cloud → TFLite → Advanced (MediaPipe) → OpenCV → Basic Pixel → Fallback estimates

### Next
- Voice guidance (Arabic) for scan steps
- Face heatmap overlay on report
- Product recommendations linked to jenincare.shop
- Skin progress timeline comparison
