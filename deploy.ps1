##############################################################################
#  DEPLOY AUTOMATIZADO - VIPERPRO -> HOSTINGER (FTP)
#  
#  USO:
#    .\deploy.ps1                    # Deploy completo (sobrescreve tudo)
#    .\deploy.ps1 -BuildFirst        # Roda npm run build antes do deploy
#
#  REQUISITO: WinSCP instalado (https://winscp.net/eng/download.php)
#             Marque "Install .NET assembly" durante a instalacao
##############################################################################

param(
    [switch]$BuildFirst
)

# ============================================================================
# CONFIGURACAO
# ============================================================================
$config = @{
    FtpHost     = "147.93.37.120"
    FtpPort     = 21
    FtpUser     = "u481113675.cassinovegas.bet"
    FtpPassword = "Senha@777"
    
    LocalPath   = "E:\Projetos Black\viperpro"
    RemotePath  = "/public_html"
    
    WinSCPPath  = "C:\Program Files (x86)\WinSCP\WinSCPnet.dll"
    
    # URL do site (para rodar migrations)
    SiteUrl     = "https://cassinovegas.bet"
    MigrateKey  = "viperpro-migrate-2026-x9k2m"
}

# Pastas/arquivos para NAO enviar
$excludeLocal = @(
    "storage/",
    "node_modules/",
    ".git/",
    ".idea/",
    ".vscode/",
    ".gemini/",
    "tests/",
    "stubs/",
    ".agent/",
    ".env",
    ".env.example",
    ".gitignore",
    ".gitattributes",
    ".editorconfig",
    "build_log.txt",
    "package-lock.json",
    "deploy.ps1",
    "deploy-config.json",
    "*.log"
)

# ============================================================================
# FUNCOES
# ============================================================================

function Write-Banner {
    Write-Host ""
    Write-Host "  ============================================================" -ForegroundColor Cyan
    Write-Host "    VIPERPRO DEPLOY TOOL - Hostinger FTP" -ForegroundColor Cyan
    Write-Host "  ============================================================" -ForegroundColor Cyan
    Write-Host ""
}

function Write-Step {
    param([string]$Message, [string]$Status = "INFO")
    $color = switch ($Status) {
        "OK"    { "Green" }
        "WARN"  { "Yellow" }
        "ERROR" { "Red" }
        "INFO"  { "Cyan" }
        default { "White" }
    }
    $prefix = switch ($Status) {
        "OK"    { " [OK]   " }
        "WARN"  { " [WARN] " }
        "ERROR" { " [ERRO] " }
        "INFO"  { " [INFO] " }
        default { " [....] " }
    }
    Write-Host $prefix -NoNewline -ForegroundColor $color
    Write-Host $Message
}

# ============================================================================
# VERIFICACOES
# ============================================================================

Write-Banner

# Verificar WinSCP
if (-not (Test-Path $config.WinSCPPath)) {
    $altPaths = @(
        "C:\Program Files\WinSCP\WinSCPnet.dll",
        "C:\Program Files (x86)\WinSCP\WinSCPnet.dll",
        "$env:LOCALAPPDATA\Programs\WinSCP\WinSCPnet.dll"
    )
    $found = $false
    foreach ($path in $altPaths) {
        if (Test-Path $path) { $config.WinSCPPath = $path; $found = $true; break }
    }
    if (-not $found) {
        Write-Step "WinSCP nao encontrado! Instale: https://winscp.net" "ERROR"
        exit 1
    }
}

# ============================================================================
# BUILD (opcional)
# ============================================================================

if ($BuildFirst) {
    Write-Step "Rodando build de producao..." "INFO"
    Push-Location $config.LocalPath
    try {
        & npm run build 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) { Write-Step "Build OK!" "OK" }
        else { Write-Step "Erro no build!" "ERROR"; Pop-Location; exit 1 }
    } finally { Pop-Location }
}

# ============================================================================
# DEPLOY - UPLOAD DIRETO (sobrescreve tudo)
# ============================================================================

Write-Host ""
Write-Step "Local:  $($config.LocalPath)" "INFO"
Write-Step "Remoto: $($config.FtpHost):$($config.RemotePath)" "INFO"
Write-Step "Nao enviar: storage/, node_modules/, .git/, .env" "INFO"
Write-Host ""

# Carregar WinSCP
try {
    Add-Type -Path $config.WinSCPPath
    Write-Step "WinSCP carregado" "OK"
} catch {
    Write-Step "Erro ao carregar WinSCP: $_" "ERROR"
    exit 1
}

# Sessao FTP
$sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol   = [WinSCP.Protocol]::Ftp
    HostName   = $config.FtpHost
    PortNumber = $config.FtpPort
    UserName   = $config.FtpUser
    Password   = $config.FtpPassword
    FtpSecure  = [WinSCP.FtpSecure]::Explicit
    GiveUpSecurityAndAcceptAnyTlsHostCertificate = $true
}

# Transferencia com exclusoes
$transferOptions = New-Object WinSCP.TransferOptions
$transferOptions.TransferMode = [WinSCP.TransferMode]::Automatic
$transferOptions.OverwriteMode = [WinSCP.OverwriteMode]::Overwrite

# Montar FileMask WinSCP
$excludeParts = ($excludeLocal | ForEach-Object { "!$_" }) -join "; "
$transferOptions.FileMask = "* | $excludeParts"

$session = New-Object WinSCP.Session

try {
    # Conectar
    Write-Step "Conectando ao FTP..." "INFO"
    $session.Open($sessionOptions)
    Write-Step "Conectado!" "OK"
    Write-Host ""
    
    # ================================================================
    # SINCRONIZAR: Envia tudo que mudou, sobrescreve no servidor
    # ================================================================
    Write-Host "  ---- ENVIANDO ARQUIVOS ----" -ForegroundColor Yellow
    Write-Step "Sincronizando arquivos (enviando alterados)..." "INFO"
    Write-Host "    Isso pode levar alguns minutos..." -ForegroundColor DarkGray
    Write-Host ""
    
    $startTime = Get-Date
    
    # SynchronizeDirectories: envia apenas arquivos novos/alterados
    # Muito mais rapido que deletar tudo + reupload
    $syncResult = $session.SynchronizeDirectories(
        [WinSCP.SynchronizationMode]::Remote,      # Local -> Remoto
        $config.LocalPath,
        $config.RemotePath,
        $false,                                      # NAO deletar arquivos extras no servidor
        $false,                                      # NAO espelhar
        [WinSCP.SynchronizationCriteria]::Time,     # Comparar por data
        $transferOptions
    )
    
    $syncResult.Check()
    
    $elapsed = (Get-Date) - $startTime
    $totalFiles = $syncResult.Uploads.Count
    
    Write-Host ""
    if ($totalFiles -eq 0) {
        Write-Step "Nenhuma alteracao detectada. Tudo sincronizado!" "OK"
    } else {
        # Mostrar primeiros 20 arquivos enviados
        $shown = 0
        foreach ($upload in $syncResult.Uploads) {
            if ($shown -lt 20) {
                Write-Host "    >> $($upload.FileName)" -ForegroundColor DarkGray
                $shown++
            }
        }
        if ($totalFiles -gt 20) {
            Write-Host "    ... e mais $($totalFiles - 20) arquivo(s)" -ForegroundColor DarkGray
        }
        Write-Host ""
        Write-Step "$totalFiles arquivo(s) enviados em $([math]::Round($elapsed.TotalMinutes, 1)) min" "OK"
    }
    
} catch {
    Write-Step "Erro durante o deploy: $($_.Exception.Message)" "ERROR"
    exit 1
} finally {
    $session.Dispose()
}

# ============================================================================
# RODAR MIGRATIONS + CACHE (via HTTP)
# ============================================================================

Write-Host ""
Write-Host "  ---- MIGRATIONS & CACHE ----" -ForegroundColor Yellow

$migrateUrl = "$($config.SiteUrl)/migrate.php?key=$($config.MigrateKey)"
Write-Step "Rodando migrations via HTTP..." "INFO"

try {
    Start-Sleep -Seconds 3
    $response = Invoke-WebRequest -Uri $migrateUrl -UseBasicParsing -TimeoutSec 120
    
    if ($response.StatusCode -eq 200) {
        Write-Step "Migrations OK!" "OK"
        Write-Host ""
        foreach ($line in ($response.Content -split "`n")) {
            if ($line.Trim()) { Write-Host "    $($line.Trim())" -ForegroundColor DarkGray }
        }
    } else {
        Write-Step "Migration retornou status: $($response.StatusCode)" "WARN"
    }
} catch {
    Write-Step "Migrations: $($_.Exception.Message)" "WARN"
    Write-Host "    Rode manualmente: $migrateUrl" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "  ============================================================" -ForegroundColor Green
Write-Host "    DEPLOY FINALIZADO!" -ForegroundColor Green
Write-Host "  ============================================================" -ForegroundColor Green
Write-Host ""
