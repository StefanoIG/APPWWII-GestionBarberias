# Script para generar certificados SSL auto-firmados para Nginx
$sslPath = "$PSScriptRoot\ssl"
$certFile = "$sslPath\barberia.local.crt"
$keyFile = "$sslPath\barberia.local.key"

if (!(Test-Path $sslPath)) {
    New-Item -ItemType Directory -Path $sslPath -Force | Out-Null
}

if (!(Test-Path $certFile) -or !(Test-Path $keyFile)) {
    Write-Host "Generando certificados SSL auto-firmados..." -ForegroundColor Yellow
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout $keyFile -out $certFile -subj "/CN=barberia.local"
    Write-Host "Certificados generados en $sslPath" -ForegroundColor Green
} else {
    Write-Host "Certificados SSL ya existen en $sslPath" -ForegroundColor Green
}
