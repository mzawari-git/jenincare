param(
    [string]$Queue = "capi-events,default",
    [int]$Sleep = 3,
    [int]$Tries = 3,
    [int]$Backoff = 30
)

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$LogFile = Join-Path $ProjectRoot "storage\logs\queue-worker.log"
$PhpExe = "C:\xampp\php\php.exe"
$Artisan = Join-Path $ProjectRoot "artisan"

Write-Host "JeninCare Queue Worker" -ForegroundColor Cyan
Write-Host "Queue(s): $Queue" -ForegroundColor Yellow
Write-Host "Log: $LogFile" -ForegroundColor Yellow
Write-Host "Press Ctrl+C to stop" -ForegroundColor Green
Write-Host ""

while ($true) {
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    try {
        $output = & $PhpExe $Artisan queue:work redis `
            --queue=$Queue `
            --tries=$Tries `
            --backoff=$Backoff `
            --sleep=$Sleep `
            --timeout=120 `
            --once 2>&1

        if ($LASTEXITCODE -ne 0) {
            $errorMsg = "[$Timestamp] ERROR (exit $LASTEXITCODE): $output"
            Write-Host $errorMsg -ForegroundColor Red
            Add-Content -Path $LogFile -Value $errorMsg
            Start-Sleep -Seconds 10
        } else {
            foreach ($line in $output) {
                if ($line -match "DONE|RUNNING") {
                    Write-Host "  [$Timestamp] $line" -ForegroundColor Green
                } elseif ($line -match "FAILED") {
                    Write-Host "  [$Timestamp] $line" -ForegroundColor Red
                }
            }
        }
    } catch {
        $err = "[$Timestamp] EXCEPTION: $_"
        Write-Host $err -ForegroundColor Red
        Add-Content -Path $LogFile -Value $err
        Start-Sleep -Seconds 30
    }

    Start-Sleep -Milliseconds 100
}
