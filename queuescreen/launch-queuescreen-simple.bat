@echo off
REM Launch Queue Screen (Simple Version - No PHP Required)
REM This version works without web server, uses hardcoded stream URL

SET "HTML_FILE=%~dp0www\dev\queuescreen-simple.html"
SET "CHROME_PATH=C:\Program Files\Google\Chrome\Application\chrome.exe"
SET "EDGE_PATH=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

echo ==========================================
echo Queue Screen Launcher (Simple)
echo ==========================================
echo.
echo This version works without Laragon!
echo Stream URL is hardcoded in the HTML file.
echo.

REM Check which browser is available
IF EXIST "%CHROME_PATH%" (
    echo Starting with Google Chrome...
    start "" "%CHROME_PATH%" --start-fullscreen --kiosk "%HTML_FILE%"
) ELSE IF EXIST "%EDGE_PATH%" (
    echo Starting with Microsoft Edge...
    start "" "%EDGE_PATH%" --start-fullscreen --kiosk "%HTML_FILE%"
) ELSE (
    echo No supported browser found.
    echo Trying default browser...
    start "" "%HTML_FILE%"
)

echo.
echo Queue screen launched!
echo.
echo To change stream URL: Press 'U' key in the browser
echo Press F11 to exit fullscreen mode
echo Press Alt+F4 to close the browser
echo.
pause

