@echo off
:: 1. เปลี่ยน Drive (เผื่อโปรเจกต์ไม่ได้อยู่ที่ Drive C เช่น Drive D:)
%~d0

:: 2. วิ่งเข้าไปที่โฟลเดอร์ที่ไฟล์ .bat นี้วางอยู่
cd "%~dp0"

echo 🚀 Starting Deploy to GitHub Pages...
echo Location: %cd%

:: 3. รันคำสั่ง deploy
call npm run deploy

echo.
echo ✅ Deployment Finished!
pause