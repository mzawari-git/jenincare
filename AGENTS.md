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
