$env:JAVA_HOME = 'C:\Program Files\JetBrains\IntelliJ IDEA 2025.3.3\jbr'
$env:Path = "$env:JAVA_HOME\bin;$env:PATH"
cd 'C:\xampp\htdocs\jenincare\SkinAnalyzer\android-app'
.\gradlew assembleDebug --no-daemon 2>&1 | Tee-Object -FilePath build_v1.3.1.txt
Read-Host "Press Enter to exit"
