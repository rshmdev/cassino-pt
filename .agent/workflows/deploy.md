---
description: Deploy da aplicação VipPro para Hostinger via FTP
---

# Deploy para Hostinger

## Pré-requisitos
1. WinSCP instalado com .NET assembly: https://winscp.net/eng/download.php
2. Credenciais FTP configuradas no arquivo `deploy.ps1`

## 1. Build de Produção (se necessário)
Rode o build dos assets antes do deploy:
// turbo
```
npm run build
```

## 2. Deploy com Simulação (Dry Run)
Primeiro, simule o deploy para ver o que seria enviado:
// turbo
```powershell
powershell -ExecutionPolicy Bypass -File "deploy.ps1" -DryRun
```

## 3. Deploy Real
Após verificar a simulação, rode o deploy real:
```powershell
powershell -ExecutionPolicy Bypass -File "deploy.ps1"
```

## 4. Deploy Completo (Build + Deploy)
Para build + deploy em um comando:
```powershell
powershell -ExecutionPolicy Bypass -File "deploy.ps1" -BuildFirst
```

## Pastas Ignoradas
As seguintes pastas/arquivos NÃO são enviados:
- `storage/` - Imagens e uploads dos usuários
- `node_modules/` - Dependências do Node
- `.git/` - Repositório Git
- `.env` - Variáveis de ambiente
- `tests/`, `stubs/` - Arquivos de desenvolvimento

## Personalização
Edite as variáveis `$excludeFolders` e `$excludeFiles` no `deploy.ps1` para adicionar ou remover exclusões.
