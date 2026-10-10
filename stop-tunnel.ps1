# stop-tunnel.ps1 - matikan Cloudflare Tunnel dengan bersih
#
# Jalankan:  .\stop-tunnel.ps1

$ErrorActionPreference = "Stop"

$proc = Get-Process cloudflared -ErrorAction SilentlyContinue

if (-not $proc) {
    Write-Host ""
    Write-Host "  [OK] Tidak ada tunnel yang jalan. Sudah aman." -ForegroundColor Green
    Write-Host ""
    exit 0
}

Write-Host ""
Write-Host "  Temuan $($proc.Count) proses cloudflared:" -ForegroundColor Cyan

foreach ($p in $proc) {
    $started = try { $p.StartTime.ToString("HH:mm:ss") } catch { "?" }
    Write-Host "    PID $($p.Id)   start $started" -ForegroundColor DarkGray
}

Write-Host ""
foreach ($p in $proc) {
    try {
        Stop-Process -Id $p.Id -Force -ErrorAction Stop
        Write-Host "  [OK] PID $($p.Id) dimatikan" -ForegroundColor Green
    } catch {
        Write-Host "  [X] Gagal mematikan PID $($p.Id): $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Beri waktu proses benar-benar keluar
Start-Sleep -Seconds 2
$left = Get-Process cloudflared -ErrorAction SilentlyContinue

Write-Host ""
if ($left) {
    Write-Host "  [!] Masih ada $($left.Count) proses tersisa." -ForegroundColor Yellow
    Write-Host "      Coba jalankan ulang .\stop-tunnel.ps1, atau paksa lewat Task Manager." -ForegroundColor Yellow
} else {
    Write-Host "  Tunnel sudah mati. Link trycloudflare.com-nya sudah tidak aktif." -ForegroundColor Green
    Write-Host "  Ingat: folder htdocs lain (dashboard, pbw-2..4, stockbarang) ikut kembali tidak terekspos." -ForegroundColor DarkGray
}
Write-Host ""