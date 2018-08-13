@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../zendframework/zend-expressive/bin/expressive-tooling
php "%BIN_TARGET%" %*
