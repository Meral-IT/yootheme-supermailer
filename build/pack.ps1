# 
# Filename: f:\Projekte\yootheme-supermailer\dev\pack.ps1
# Path: f:\Projekte\yootheme-supermailer\dev
# Created Date: Tuesday, July 30th 2024, 9:41:57 pm
# Author: Necati Meral https://meral.cloud
# 
# Copyright (c) 2024 Meral IT
# 

$WorkingDirectory = Join-Path $PSScriptRoot '../'
$DistDirectory = Join-Path $WorkingDirectory 'build' 'plg_system_yoosupermailer.zip'

Compress-Archive -Path $WorkingDirectory -DestinationPath $DistDirectory -Force