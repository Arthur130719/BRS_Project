@echo off
echo ========================================================
echo MENGOMPRES UKURAN DISK DOCKER (WSL 2)
echo Pastikan Anda menjalankan ini sebagai Administrator (Run as administrator)
echo ========================================================
echo.

echo 1. Mematikan Docker Desktop dan WSL sementara...
wsl --shutdown
timeout /t 3 /nobreak > nul

echo.
echo 2. Mencari file virtual disk Docker...

set VHDX1="%LOCALAPPDATA%\Docker\wsl\disk\docker_data.vhdx"
set VHDX2="%LOCALAPPDATA%\Docker\wsl\data\ext4.vhdx"

if exist %VHDX1% (
    echo Ditemukan: %VHDX1%
    echo select vdisk file=%VHDX1% > compact.txt
) else if exist %VHDX2% (
    echo Ditemukan: %VHDX2%
    echo select vdisk file=%VHDX2% > compact.txt
) else (
    echo GAGAL: File virtual disk Docker tidak ditemukan!
    pause
    exit /b
)

echo attach vdisk readonly >> compact.txt
echo compact vdisk >> compact.txt
echo detach vdisk >> compact.txt

echo.
echo 3. Mengompres disk (memakan waktu beberapa detik/menit)...
diskpart /s compact.txt

del compact.txt

echo.
echo ========================================================
echo SELESAI! Disk C: Anda seharusnya sudah lega kembali.
echo Anda bisa menutup jendela ini dan menyalakan ulang Docker Desktop.
echo ========================================================
pause
