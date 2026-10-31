function Get-PhpExecutable {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) {
        return $phpCmd.Source
    }

    return 'php'
}

function Test-QueueWorkerRunningForBackend {
    param(
        [Parameter(Mandatory = $true)]
        [string]$BackendPath
    )

    Get-CimInstance Win32_Process -Filter "name='php.exe'" -ErrorAction SilentlyContinue |
        Where-Object {
            $_.CommandLine -match 'queue:work' -and
            $_.CommandLine -match [regex]::Escape($BackendPath)
        } |
        Select-Object -First 1
}

function Start-LaravelDevQueueWorker {
    param(
        [Parameter(Mandatory = $true)]
        [string]$BackendPath,
        [Parameter(Mandatory = $true)]
        [string]$ProjectLabel,
        [string]$QueueConnection = 'database',
        [string]$QueueName = 'default',
        [int]$Tries = 3,
        [int]$TimeoutSeconds = 120
    )

    $backendPath = (Resolve-Path $BackendPath).Path
    $artisan = Join-Path $backendPath 'artisan'
    if (-not (Test-Path $artisan)) {
        Write-Warning "[$ProjectLabel] No hay artisan en $backendPath"
        return
    }

    $existing = Test-QueueWorkerRunningForBackend -BackendPath $backendPath
    if ($null -ne $existing) {
        Write-Host "[$ProjectLabel] Worker ya activo (PID $($existing.ProcessId))."
        return
    }

    $php = Get-PhpExecutable
    Push-Location $backendPath
    try {
        $queueDriver = (php artisan config:show queue.default --no-ansi 2>$null).Trim()
        if ($queueDriver -eq 'sync') {
            Write-Host "[$ProjectLabel] QUEUE_CONNECTION=sync - worker omitido (cola en linea)."
            return
        }

        $argumentList = @(
            $artisan,
            'queue:work', $QueueConnection,
            '--queue=' + $QueueName,
            '--tries=' + $Tries,
            '--timeout=' + $TimeoutSeconds,
            '--sleep=1'
        )
        Start-Process -FilePath $php -ArgumentList $argumentList -WorkingDirectory $backendPath -WindowStyle Minimized
        Write-Host ('[{0}] Worker iniciado ({1}, cola {2}).' -f $ProjectLabel, $QueueConnection, $QueueName)
    }
    finally {
        Pop-Location
    }
}
