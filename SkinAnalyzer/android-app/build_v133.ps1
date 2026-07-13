$env:JAVA_HOME = 'C:\Program Files\JetBrains\IntelliJ IDEA 2025.3.3\jbr'
$env:Path = "$env:JAVA_HOME\bin;$env:PATH"
cd 'C:\xampp\htdocs\jenincare\SkinAnalyzer\android-app'
Remove-Item -Recurse -Force ".gradle\configuration-cache" -ErrorAction SilentlyContinue
Write-Host "Building v1.3.3..."
.\gradlew assembleRelease --no-daemon 2>&1 | Tee-Object -FilePath build_v133.log
Write-Host "`nDone! Check build_v133.log for details"
Read-Host "Press Enter to close"
