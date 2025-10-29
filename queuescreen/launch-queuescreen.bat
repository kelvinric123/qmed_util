@echo off
REM Launch Queue Screen with Integrated TV2 Stream
REM This opens the queue screen via Laragon web server

SET "URL=http://localhost/qmed-util/qmed-utils%%20-rtm2/queuescreen/www/dev/queuescreen-with-tv2.html"
SET "CHROME_PATH=C:\Program Files\Google\Chrome\Application\chrome.exe"
SET "EDGE_PATH=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

echo ==========================================
echo Queue Screen Launcher
echo ==========================================
echo.
echo Opening via Laragon web server...
echo URL: %URL%
echo.

REM Check if Laragon is running
timeout /t 1 /nobreak >nul
curl -s -o nul -w "%%{http_code}" http://localhost >nul 2>&1
IF ERRORLEVEL 1 (
    echo WARNING: Laragon may not be running!
    echo Please start Laragon first.
    echo.
    pause
    exit /b 1
)

REM Check which browser is available
IF EXIST "%CHROME_PATH%" (
    echo Starting with Google Chrome...
    start "" "%CHROME_PATH%" --start-fullscreen --kiosk --app="%URL%"
) ELSE IF EXIST "%EDGE_PATH%" (
    echo Starting with Microsoft Edge...
    start "" "%EDGE_PATH%" --start-fullscreen --kiosk --app="%URL%"
) ELSE (
    echo No supported browser found.
    echo Trying default browser...
    start "" "%URL%"
)

echo.
echo Queue screen launched!
echo.
echo Press F11 to exit fullscreen mode
echo Press Alt+F4 to close the browser
echo.
pause

