##############################################################################
#  DEPLOY RAPIDO -> FTP (WinSCP)  | so envia o que mudou
#
#  USO:
#    .\deploy.ps1                 # builda o front + envia alterados + roda migration
#    .\deploy.ps1 -NoBuild        # pula o "npm run build" (so backend/blade mudou)
#    .\deploy.ps1 -NoMigrate      # nao chama o migrate.php depois do envio
#    .\deploy.ps1 -DryRun         # so LISTA o que enviaria, sem enviar nada
#
#  PRE-REQUISITOS:
#    - WinSCP instalado com "Install .NET assembly (WinSCPnet.dll)"
#    - Copiar deploy.config.example.ps1 -> deploy.config.ps1 e preencher
##############################################################################
[CmdletBinding()]
param(
    [switch]$NoBuild,
    [switch]$NoMigrate,
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot

# ---------------------------------------------------------------------------
# Config (deploy.config.ps1 NAO vai pro git - contem senha)
# ---------------------------------------------------------------------------
$cfgPath = Join-Path $root 'deploy.config.ps1'
if (-not (Test-Path $cfgPath)) {
    Write-Host "[ERRO] deploy.config.ps1 nao encontrado." -ForegroundColor Red
    Write-Host "       Copie deploy.config.example.ps1 para deploy.config.ps1 e preencha as credenciais." -ForegroundColor Yellow
    exit 1
}
. $cfgPath   # define: $FtpHost,$FtpPort,$FtpUser,$FtpPass,$RemotePath,$SiteUrl,$MigrateKey,$FtpProtocol,$WinSCPDll,$ExtraExcludes

if (-not $FtpPort)     { $FtpPort = 21 }
if (-not $FtpProtocol) { $FtpProtocol = 'ftp' }   # 'ftp' (com TLS explicito) ou 'ftp-plain'

# localizar WinSCPnet.dll
if (-not $WinSCPDll) {
    $WinSCPDll = @(
        "C:\Program Files (x86)\WinSCP\WinSCPnet.dll",
        "C:\Program Files\WinSCP\WinSCPnet.dll",
        "$env:LOCALAPPDATA\Programs\WinSCP\WinSCPnet.dll"
    ) | Where-Object { Test-Path $_ } | Select-Object -First 1
}
if (-not $WinSCPDll -or -not (Test-Path $WinSCPDll)) {
    Write-Host "[ERRO] WinSCPnet.dll nao encontrado. Instale o WinSCP (com .NET assembly) ou defina \$WinSCPDll na config." -ForegroundColor Red
    exit 1
}

# ---------------------------------------------------------------------------
# Pastas/arquivos do SERVIDOR que NUNCA devem ser enviados/sobrescritos
# ---------------------------------------------------------------------------
$excludes = @(
    'storage/',           # logs, cache, sessions - geridos pelo servidor
    'public/storage/',    # BANNERS e uploads (config/filesystems: public_path()/storage)
    'vendor/',            # ja esta no servidor
    'node_modules/',
    '.git/',
    '.github/',
    '.idea/', '.vscode/', '.fleet/', '.gemini/', '.agent/',
    'tests/', 'stubs/',
    '.env', '.env.*',
    'deploy.ps1', 'deploy.config.ps1', 'deploy.config.example.ps1',
    'deploy.last.log', 'build_log.txt', '*.log',
    'public/logs.txt',
    '.gitignore', '.gitattributes', '.editorconfig'
)
if ($ExtraExcludes) { $excludes += $ExtraExcludes }
$fileMask = "| " + ($excludes -join "; ")

# ---------------------------------------------------------------------------
# Build do front
# ---------------------------------------------------------------------------
if (-not $NoBuild) {
    Write-Host "==> npm run build" -ForegroundColor Cyan
    Push-Location $root
    try {
        & npm run build
        if ($LASTEXITCODE -ne 0) { throw "npm run build falhou." }
    } finally { Pop-Location }
    Write-Host "    build OK" -ForegroundColor Green
} else {
    Write-Host "==> build pulado (-NoBuild)" -ForegroundColor Yellow
}

# ---------------------------------------------------------------------------
# Conexao + sincronizacao
# ---------------------------------------------------------------------------
Add-Type -Path $WinSCPDll

$sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol   = [WinSCP.Protocol]::Ftp
    HostName   = $FtpHost
    PortNumber = $FtpPort
    UserName   = $FtpUser
    Password   = $FtpPass
}
if ($FtpProtocol -eq 'ftp') {
    $sessionOptions.FtpSecure = [WinSCP.FtpSecure]::Explicit
    $sessionOptions.GiveUpSecurityAndAcceptAnyTlsHostCertificate = $true
}

$transferOptions = New-Object WinSCP.TransferOptions
$transferOptions.TransferMode = [WinSCP.TransferMode]::Automatic
$transferOptions.FileMask     = $fileMask

$session = New-Object WinSCP.Session
try {
    Write-Host "==> Conectando $FtpHost ..." -ForegroundColor Cyan
    $session.Open($sessionOptions)
    Write-Host "    local : $root"
    Write-Host "    remoto: $RemotePath"

    if ($DryRun) {
        $diff = $session.CompareDirectories(
            [WinSCP.SynchronizationMode]::Remote, $root, $RemotePath,
            $false, $false, [WinSCP.SynchronizationCriteria]::Time, $transferOptions)
        Write-Host "==> DRY-RUN: $($diff.Count) arquivo(s) seriam enviados:" -ForegroundColor Yellow
        foreach ($d in $diff) { Write-Host "    >> $($d.Local.FileName)" -ForegroundColor DarkGray }
    } else {
        Write-Host "==> Enviando alterados..." -ForegroundColor Cyan
        $r = $session.SynchronizeDirectories(
            [WinSCP.SynchronizationMode]::Remote, $root, $RemotePath,
            $false,                                       # NAO deletar no servidor
            $false,
            [WinSCP.SynchronizationCriteria]::Time,
            $transferOptions)
        $r.Check()
        $n = $r.Uploads.Count
        if ($n -eq 0) {
            Write-Host "    nada mudou. Tudo sincronizado." -ForegroundColor Green
        } else {
            $r.Uploads | Select-Object -First 25 | ForEach-Object { Write-Host "    >> $($_.FileName)" -ForegroundColor DarkGray }
            if ($n -gt 25) { Write-Host "    ... e mais $($n - 25) arquivo(s)" -ForegroundColor DarkGray }
            Write-Host "    $n arquivo(s) enviados." -ForegroundColor Green
        }
    }
} finally {
    $session.Dispose()
}

# ---------------------------------------------------------------------------
# Migration + cache (via HTTP) - migrate.php roda migrate --force + optimize:clear
# ---------------------------------------------------------------------------
if (-not $DryRun -and -not $NoMigrate -and $SiteUrl -and $MigrateKey) {
    $url = "$($SiteUrl.TrimEnd('/'))/migrate.php?key=$MigrateKey"
    Write-Host "==> Rodando migration + limpando cache: $url" -ForegroundColor Cyan
    try {
        $resp = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 120
        ($resp.Content -split "`n") | Where-Object { $_.Trim() } | ForEach-Object { Write-Host "    $($_.Trim())" -ForegroundColor DarkGray }
    } catch {
        Write-Host "    [WARN] migrate falhou: $($_.Exception.Message)" -ForegroundColor Yellow
        Write-Host "    Rode manualmente no navegador: $url" -ForegroundColor Yellow
    }
}

Write-Host "==> Deploy concluido!" -ForegroundColor Green
