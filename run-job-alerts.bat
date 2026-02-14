@echo off
echo ======================================
echo Job Portal - Email Alerts
echo ======================================
echo.
echo Starting job alert system...
echo.

C:\xampp\php\php.exe -f "C:\xampp\htdocs\job_poster\cron\send-job-alerts.php"

echo.
echo ======================================
echo Process completed!
echo ======================================
pause
