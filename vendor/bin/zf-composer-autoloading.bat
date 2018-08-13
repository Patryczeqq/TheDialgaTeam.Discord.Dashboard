@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../zfcampus/zf-composer-autoloading/bin/zf-composer-autoloading
php "%BIN_TARGET%" %*
