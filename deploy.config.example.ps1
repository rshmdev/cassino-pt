# ----------------------------------------------------------------------------
# COPIE este arquivo para "deploy.config.ps1" e preencha.
# O deploy.config.ps1 NAO vai para o git (contem senha).
# ----------------------------------------------------------------------------

$FtpHost    = "147.93.37.120"          # IP/host do FTP
$FtpPort    = 21
$FtpUser    = "usuario.do.ftp"
$FtpPass    = "sua-senha"
$RemotePath = "/public_html"           # raiz do projeto Laravel no servidor

# Site (para rodar migration + limpar cache via HTTP). Deixe vazio p/ pular.
$SiteUrl    = "https://seusite.com"
$MigrateKey = "viperpro-migrate-2026-x9k2m"   # mesma chave do public/migrate.php

# Opcionais:
# $FtpProtocol = "ftp"        # "ftp" = TLS explicito (padrao) | "ftp-plain" = sem TLS
# $WinSCPDll   = "C:\Program Files (x86)\WinSCP\WinSCPnet.dll"
# $ExtraExcludes = @("public/uploads/", "public/img/banners/")   # outras pastas de upload a proteger
