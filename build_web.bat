@echo off
echo Building TransitOps Flutter Web Application...
flutter build web --release
if %ERRORLEVEL% EQU 0 (
    echo Build successful! Output is in build/web directory
) else (
    echo Build failed!
)
pause
