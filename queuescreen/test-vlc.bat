@echo off
REM Simple VLC Test - Just starts VLC with the stream URL

SET "URL_FILE=%~dp0www\dev\tv2-stream-url.txt"
SET "VLC_PATH=C:\Program Files\VideoLAN\VLC\vlc.exe"

REM Read the URL
SET /p STREAM_URL=<"%URL_FILE%"

echo Testing VLC with stream...
echo URL: %STREAM_URL%
echo.
echo Starting VLC...
echo.

REM Simple start - just open VLC normally first to test
"%VLC_PATH%" "%STREAM_URL%"

echo.
echo Done!
pause

