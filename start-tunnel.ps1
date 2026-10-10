# start-tunnel.ps1 - nyalakan Cloudflare Tunnel untuk KolamKita
#
# Jalankan:  .\start-tunnel.ps1
#
# XAMPP (Apache + MySQL) harus sudah jalan lewat XAMPP Control Panel.
# Script ini hanya mengurus tunnel-nya.

$ErrorActionPreference = "Stop"

$exe        = "C:\Program Files (x86)\cloudflared\cloudflared.exe"
$outLog     = Join-Path $PSScriptRoot "tunnel-out.log"
$errLog     = Join-Path $PSScriptRoot "tunnel-err.log"
$urlRegex   = 'https://[a-z0-9-]+\.trycloudflare\.com'
$timeoutSec = 60
$appPath    = "/Booking_Pemancingan/public/index.php"

function Exit-Script { Write-Host ""; exit }

function Show-Tail {
    param([string]$Path, [int]$Lines = 12)
    if (Test-Path $Path) {
        Write-Host "  12 baris terakhir dari log:" -ForegroundColor DarkGray
        Write-Host "  ---------------------------------------------" -ForegroundColor DarkGray
        Get-Content $Path -Tail $Lines -ErrorAction SilentlyContinue |
            ForEach-Object { Write-Host "  $_" -ForegroundColor DarkGray }
    }
}

function Get-LastUrl {
    param([string]$Path)
    if (-not (Test-Path $Path)) { return $null }
    $m = Select-String -Path $Path -Pattern $urlRegex -AllMatches |
         Select-Object -Last 1
    if ($m) { return $m.Matches[0].Value }
    return $null
}

Write-Host ""
Write-Host "  KolamKita - Cloudflare Tunnel" -ForegroundColor Cyan
Write-Host "  ==============================" -ForegroundColor DarkCyan
Write-Host ""

# --- 1. Pre-flight: binary ada? ---
if (-not (Test-Path $exe)) {
    Write-Host "  [X] cloudflared tidak ditemukan" -ForegroundColor Red
    Write-Host ""
    Write-Host "      Lokasi yang dicek: $exe"
    Write-Host "      Install dengan:"
    Write-Host "        winget install --id Cloudflare.cloudflared --exact --source winget" -ForegroundColor Yellow
    Exit-Script
}
Write-Host "  [OK] cloudflared ditemukan" -ForegroundColor Green

# --- 2. Sudah ada tunnel yang jalan? ---
$running = Get-Process cloudflared -ErrorAction SilentlyContinue
if ($running) {
    $existing = Get-LastUrl $errLog
    Write-Host ""
    Write-Host "  [!] Tunnel sudah berjalan (PID $($running[0].Id))" -ForegroundColor Yellow
    if ($existing) {
        Write-Host ""
        Write-Host "      $existing$appPath" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "      Mau tunnel BARU? Jalankan .\stop-tunnel.ps1 dulu." -ForegroundColor DarkGray
    } else {
        Show-Tail $errLog 8
        Write-Host ""
        Write-Host "      URL tidak ditemukan di log. Jalankan .\stop-tunnel.ps1 lalu ulangi." -ForegroundColor DarkGray
    }
    Exit-Script
}

# --- 3. Bersihkan log lama ---
Remove-Item $outLog, $errLog -Force -ErrorAction SilentlyContinue

Write-Host "  [..] Menghubungkan ke Cloudflare (HTTP/2, tanpa QUIC)..." -ForegroundColor DarkGray

# --- 4. Jalankan tunnel ---
$proc = Start-Process -FilePath $exe `
    -ArgumentList "tunnel", "--url", "http://localhost:80", "--no-autoupdate", "--protocol", "http2" `
    -RedirectStandardOutput $outLog `
    -RedirectStandardError  $errLog `
    -WindowStyle Hidden `
    -PassThru

# --- 5. Tunggu URL muncul ---
$url      = $null
$deadline = (Get-Date).AddSeconds($timeoutSec)

while ((Get-Date) -lt $deadline) {
    if ($proc.HasExited) { break }
    $url = Get-LastUrl $errLog
    if ($url) { break }
    Start-Sleep -Seconds 2
}

# --- 6. Hasil ---
if (-not $url) {
    Write-Host ""
    Write-Host "  [X] Gagal dapat URL dalam $timeoutSec detik" -ForegroundColor Red
    if ($proc.HasExited) {
        Write-Host "      Proses tunnel langsung mati (exit code $($proc.ExitCode))." -ForegroundColor Red
    }
    Write-Host ""
    Show-Tail $errLog 12
    Write-Host ""
    Write-Host "  Penyebab yang sering:" -ForegroundColor Yellow
    Write-Host "    - XAMPP Apache belum jalan          -> buka XAMPP Control Panel, Start Apache"
    Write-Host "    - DNS diblokir / timeout            -> coba: .\start-tunnel.ps1 lagi"
    Write-Host "    - Firewall memblokir outbound        -> izinkan cloudflared di Windows Defender Firewall"
    Write-Host ""
    Write-Host "  Log lengkap: $errLog"
    Exit-Script
}

$fullUrl = "$url$appPath"

Write-Host ""
Write-Host "  ==============================================" -ForegroundColor Green
Write-Host "   TUNNEL SIAP" -ForegroundColor Green
Write-Host "  ==============================================" -ForegroundColor Green
Write-Host ""
Write-Host "   $($fullUrl)"
Write-Host ""
Write-Host "   URL juga sudah dicopy ke clipboard." -ForegroundColor DarkGray
Write-Host "   Kirim link di atas ke anggota kelompok." -ForegroundColor DarkGray
Write-Host ""

try {
    Set-Clipboard -Value $fullUrl
    Start-Process $fullUrl
    Write-Host "   Browser dibuka otomatis." -ForegroundColor DarkGray
} catch {
    Write-Host "   (gagal buka browser otomatis - salin manual dari atas)" -ForegroundColor DarkYellow
}
Write-Host ""
Write-Host "   Tunnel berjalan di background. Menutup terminal TIDAK mematikan"
Write-Host "   tunnel. Untuk forcibly hentikan: .\stop-tunnel.ps1"
Write-Host ""
Write-Host "  --- log tunnel (Ctrl+C untuk berhenti melihat log, tunnel tetap jalan) ---"
Write-Host ""

Get-Content $errLog -Wait -Tail 10 -ErrorAction SilentlyContinue

Exit-Script