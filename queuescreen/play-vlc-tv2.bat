@echo off
REM RTM TV2 VLC Player Launcher
REM Plays the extracted stream URL in VLC (borderless, left 1/3 of screen)

SET "URL_FILE=%~dp0www\dev\tv2-stream-url.txt"
SET "VLC_PATH=C:\Program Files\VideoLAN\VLC\vlc.exe"

REM Check if VLC exists
IF NOT EXIST "%VLC_PATH%" (
    echo Error: VLC not found at %VLC_PATH%
    echo Please install VLC or update the VLC_PATH in this script
    pause
    exit /b 1
)

REM Check if URL file exists
IF NOT EXIST "%URL_FILE%" (
    echo Error: Stream URL file not found at %URL_FILE%
    echo Please run extract-url-auto.sh first to extract the stream URL
    pause
    exit /b 1
)

REM Read the URL from file
SET /p STREAM_URL=<"%URL_FILE%"

IF "%STREAM_URL%"=="" (
    echo Error: Stream URL file is empty
    pause
    exit /b 1
)

echo ==========================================
echo RTM TV2 VLC Player
echo ==========================================
echo.
echo Stream URL: %STREAM_URL%
echo.
echo Starting VLC in borderless mode...
echo Position: Left Top - 1/4 of screen
echo.

REM Launch VLC with borderless window positioned for queue screen display area
REM Position matches the marked area in your queue screen
REM Adjust x, y, width, height to perfectly match your display
REM Current: x=40, y=50, width=880, height=495 (16:9 ratio)
start "" "%VLC_PATH%" --no-video-deco --no-embedded-video --video-x=40 --video-y=50 --width=880 --height=495 --no-video-title-show --no-qt-error-dialogs --no-qt-privacy-ask --autoscale --aspect-ratio=16:9 --crop=16:9 "%STREAM_URL%"

IF %ERRORLEVEL% NEQ 0 (
    echo.
    echo Error: Failed to start VLC
    echo Error code: %ERRORLEVEL%
    pause
    exit /b 1
)

echo.
echo VLC launched successfully!
echo.
echo Note: The window cannot be moved with mouse when borderless.
echo To close: Use Alt+F4 or close from taskbar.
echo.
pause

