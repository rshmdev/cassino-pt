##############################################################################
#  DEPLOY AUTOMATIZADO - VIPERPRO -> HOSTINGER (FTP)
#  
#  USO:
#    .\deploy.ps1                    # Deploy completo
#    .\deploy.ps1 -BuildFirst        # Roda npm run build antes do deploy
#
#  REQUISITO: WinSCP instalado (https://winscp.net/eng/download.php)
#             Marque "Install .NET assembly" durante a instalacao
#
#  PRIMEIRA VEZ: Configure as variaveis abaixo com seus dados da Hostinger
##############################################################################

param(
    [switch]$BuildFirst    # Roda npm run build antes
)

# ============================================================================
# CONFIGURACAO - PREENCHA COM SEUS DADOS DA HOSTINGER
# ============================================================================
$config = @{
    FtpHost     = "147.93.37.120"
    FtpPort     = 21
    FtpUser     = "u481113675.cassinovegas.bet"
    FtpPassword = "Senha@777"
    
    LocalPath   = "E:\Projetos Black\viperpro"
    RemotePath  = "/public_html"
    
    WinSCPPath  = "C:\Program Files (x86)\WinSCP\WinSCPnet.dll"
}

# ============================================================================
# PASTAS E ARQUIVOS PARA NAO ENVIAR (local -> remoto)
# ============================================================================
$excludeLocal = @(
    "storage",
    "node_modules",
    ".git",
    ".idea",
    ".vscode",
    ".gemini",
    "tests",
    "stubs",
    ".agent",
    ".env",
    ".env.example",
    ".gitignore",
    ".gitattributes",
    ".editorconfig",
    "build_log.txt",
    "package-lock.json",
    "deploy.ps1",
    "deploy-config.json"
)

# ============================================================================
# PASTAS/ARQUIVOS PARA PRESERVAR NO SERVIDOR (nao apagar)
# ============================================================================
$preserveRemote = @(
    "/public_html/.env",
    "/public_html/public/storage"
)

# ============================================================================
# FUNCOES AUXILIARES
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

function Remove-RemoteDirectory {
    param(
        $Session,
        [string]$RemotePath,
        [string[]]$Preserve
    )
    
    Write-Step "Listando arquivos no servidor..." "INFO"
    
    $remoteFiles = $Session.EnumerateRemoteFiles(
        $RemotePath,
        $null,
        [WinSCP.EnumerationOptions]::AllDirectories
    )
    
    # Separar arquivos e pastas, ordenar para deletar arquivos primeiro
    $filesToDelete = @()
    $dirsToDelete = @()
    
    foreach ($fileInfo in $remoteFiles) {
        $fullPath = $fileInfo.FullName
        
        # Verificar se esta no caminho preservado
        $skip = $false
        foreach ($p in $Preserve) {
            if ($fullPath -like "$p*" -or $fullPath -eq $p) {
                $skip = $true
                break
            }
        }
        
        if ($skip) { continue }
        
        if ($fileInfo.IsDirectory) {
            $dirsToDelete += $fullPath
        } else {
            $filesToDelete += $fullPath
        }
    }
    
    $totalItems = $filesToDelete.Count + $dirsToDelete.Count
    Write-Step "Encontrados $totalItems itens para remover (preservando $($Preserve.Count) caminhos)" "INFO"
    
    # Deletar arquivos primeiro
    $deleted = 0
    foreach ($file in $filesToDelete) {
        try {
            $Session.RemoveFile($file)
            $deleted++
            if ($deleted % 50 -eq 0) {
                Write-Host "    Removidos $deleted/$($filesToDelete.Count) arquivos..." -ForegroundColor DarkGray
            }
        } catch {
            Write-Host "    Falha ao remover: $file" -ForegroundColor DarkYellow
        }
    }
    
    # Deletar pastas (do mais profundo ao mais raso)
    $dirsToDelete = $dirsToDelete | Sort-Object -Property Length -Descending
    foreach ($dir in $dirsToDelete) {
        try {
            $Session.RemoveFile($dir)
        } catch {
            # Pasta pode nao estar vazia se tinha itens preservados
        }
    }
    
    Write-Step "Removidos $deleted arquivos e $($dirsToDelete.Count) pastas do servidor" "OK"
}

# ============================================================================
# VERIFICACOES INICIAIS
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
        if (Test-Path $path) {
            $config.WinSCPPath = $path
            $found = $true
            break
        }
    }
    
    if (-not $found) {
        Write-Step "WinSCP nao encontrado!" "ERROR"
        Write-Host ""
        Write-Host "  Instale o WinSCP:" -ForegroundColor Yellow
        Write-Host "    1. Acesse: https://winscp.net/eng/download.php" -ForegroundColor Gray
        Write-Host "    2. Baixe e instale" -ForegroundColor Gray
        Write-Host "    3. IMPORTANTE: Marque 'Install .NET assembly' na instalacao" -ForegroundColor Red
        Write-Host ""
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
        $buildResult = & npm run build 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Step "Build concluido com sucesso!" "OK"
        }
        else {
            Write-Step "Erro no build! Verifique os erros acima." "ERROR"
            Write-Host $buildResult -ForegroundColor Red
            Pop-Location
            exit 1
        }
    }
    finally {
        Pop-Location
    }
}

# ============================================================================
# DEPLOY VIA FTP
# ============================================================================

Write-Host ""
Write-Step "Local:  $($config.LocalPath)" "INFO"
Write-Step "Remoto: $($config.FtpHost):$($config.RemotePath)" "INFO"
Write-Step "Preservando: .env, public/storage" "WARN"
Write-Host ""

# Carregar WinSCP .NET
try {
    Add-Type -Path $config.WinSCPPath
    Write-Step "WinSCP carregado" "OK"
}
catch {
    Write-Step "Erro ao carregar WinSCP: $_" "ERROR"
    exit 1
}

# Configurar sessao FTP
$sessionOptions = New-Object WinSCP.SessionOptions -Property @{
    Protocol   = [WinSCP.Protocol]::Ftp
    HostName   = $config.FtpHost
    PortNumber = $config.FtpPort
    UserName   = $config.FtpUser
    Password   = $config.FtpPassword
    FtpSecure  = [WinSCP.FtpSecure]::Explicit
    GiveUpSecurityAndAcceptAnyTlsHostCertificate = $true
}

# Opcoes de transferencia
$transferOptions = New-Object WinSCP.TransferOptions
$transferOptions.TransferMode = [WinSCP.TransferMode]::Automatic

# Montar FileMask para excluir pastas/arquivos locais do upload
$excludeParts = @()
foreach ($item in $excludeLocal) {
    $excludeParts += "!$item"
}
$transferOptions.FileMask = "* | $($excludeParts -join '; ')"

Write-Step "Exclusoes configuradas" "OK"
Write-Host "    Nao enviar: $($excludeLocal -join ', ')" -ForegroundColor DarkGray
Write-Host ""

$session = New-Object WinSCP.Session

try {
    # Conectar
    Write-Step "Conectando ao servidor FTP..." "INFO"
    $session.Open($sessionOptions)
    Write-Step "Conectado com sucesso!" "OK"
    Write-Host ""
    
    # PASSO 1: Limpar servidor (exceto preservados)
    Write-Host "  ---- PASSO 1/2: LIMPANDO SERVIDOR ----" -ForegroundColor Yellow
    Remove-RemoteDirectory -Session $session -RemotePath $config.RemotePath -Preserve $preserveRemote
    Write-Host ""
    
    # PASSO 2: Upload de tudo
    Write-Host "  ---- PASSO 2/2: ENVIANDO ARQUIVOS ----" -ForegroundColor Yellow
    Write-Step "Enviando arquivos... (isso pode levar alguns minutos)" "INFO"
    
    $startTime = Get-Date
    
    $result = $session.PutFilesToDirectory(
        $config.LocalPath,
        $config.RemotePath,
        $null,
        $false,
        $transferOptions
    )
    
    $result.Check()
    
    $elapsed = (Get-Date) - $startTime
    $totalFiles = $result.Transfers.Count
    
    Write-Host ""
    Write-Step "$totalFiles arquivo(s) enviados em $([math]::Round($elapsed.TotalMinutes, 1)) minutos" "OK"
    
}
catch {
    Write-Step "Erro durante o deploy: $($_.Exception.Message)" "ERROR"
    
    if ($_.Exception.Message -like "*Authentication*" -or $_.Exception.Message -like "*login*") {
        Write-Host ""
        Write-Host "  Dica: Verifique suas credenciais FTP" -ForegroundColor Yellow
        Write-Host "     - Usuario e senha estao corretos?" -ForegroundColor Gray
        Write-Host "     - O acesso FTP esta ativo na Hostinger?" -ForegroundColor Gray
    }
    
    if ($_.Exception.Message -like "*timeout*" -or $_.Exception.Message -like "*connect*") {
        Write-Host ""
        Write-Host "  Dica: Verifique a conexao" -ForegroundColor Yellow
        Write-Host "     - O host FTP esta correto?" -ForegroundColor Gray
        Write-Host "     - Firewall bloqueando porta 21?" -ForegroundColor Gray
    }
    
    exit 1
    
}
finally {
    $session.Dispose()
}

Write-Host ""
Write-Host "  ============================================================" -ForegroundColor Green
Write-Host "    DEPLOY FINALIZADO COM SUCESSO!" -ForegroundColor Green
Write-Host "  ============================================================" -ForegroundColor Green
Write-Host ""
