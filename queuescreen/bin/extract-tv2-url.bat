@echo off
REM Extract RTM TV2 Stream URL for Windows
REM This script extracts the direct HLS stream URL from RTM Klik

echo ========================================
echo RTM TV2 Stream URL Extractor (Windows)
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    echo.
    echo Please install Python from https://www.python.org/
    echo.
    pause
    exit /b 1
)

echo Python found
echo.

REM Check if yt-dlp is installed
python -m pip show yt-dlp >nul 2>&1
if errorlevel 1 (
    echo yt-dlp is not installed. Installing now...
    echo.
    python -m pip install yt-dlp
    echo.
)

echo Extracting stream URL from RTM Klik...
echo.

REM Extract the stream URL
for /f "delims=" %%i in ('yt-dlp -g "https://rtmklik.rtm.gov.my/live/tv2" 2^>nul') do (
    set STREAM_URL=%%i
    goto :found
)

echo Failed to extract stream URL
echo.
echo Please check:
echo   1. Internet connection
echo   2. RTM Klik website is accessible
echo   3. yt-dlp is up to date
echo.
pause
exit /b 1

:found
echo Stream URL extracted successfully!
echo.
echo URL: %STREAM_URL%
echo.

REM Save to cache file
echo %STREAM_URL% > ..\www\dev\tv2-stream-url.txt

echo URL saved to cache
echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo The Live TV2 player is now ready to use.
echo You can test it by opening:
echo   http://localhost/qmed-util/qmed-utils/queuescreen/www/dev/tv2-player.html
echo.
pause

