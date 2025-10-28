@echo off
REM Windows batch file to extract RTM stream URL

echo ========================================
echo RTM Stream URL Extractor (Windows)
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python not found!
    echo Please install Python from https://www.python.org/
    pause
    exit /b 1
)

echo [OK] Python found
echo.

REM Check if yt-dlp is installed
python -m pip show yt-dlp >nul 2>&1
if errorlevel 1 (
    echo [WARNING] yt-dlp not installed
    echo.
    set /p install="Install yt-dlp now? (y/n): "
    if /i "%install%"=="y" (
        echo Installing yt-dlp...
        python -m pip install yt-dlp
        echo.
    ) else (
        echo Please install manually: pip install yt-dlp
        pause
        exit /b 1
    )
)

echo [OK] yt-dlp is installed
echo.

REM Run the Python extractor
python "%~dp0extract-url.py" %*

pause

