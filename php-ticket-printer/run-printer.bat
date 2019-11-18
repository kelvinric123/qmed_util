@echo off
echo "Printer connector is now running. You can close this window now."
cd printer_files
bg-runner "serve-printer.bat"