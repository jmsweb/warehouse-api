@echo off

SET BUILD=%1%
SET DEPLOY_TO=%2%

IF EXIST build (
    RMDIR /S /Q build\
)

MKDIR build

COPY /Y .%BUILD%.env build\.env

XCOPY /F /Y composer.json build\

XCOPY /F /Y composer.lock build\

MKDIR build\public
XCOPY /E .\public build\public

MKDIR build\src
XCOPY /E .\src build\src

IF EXIST %DEPLOY_TO% (
    RMDIR /S /Q %DEPLOY_TO%
    MKDIR %DEPLOY_TO%
)

XCOPY /S /E .\build %DEPLOY_TO%

RMDIR /S /Q build

php composer.phar update --working-dir=%DEPLOY_TO%

