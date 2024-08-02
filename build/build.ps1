# 
# Filename: f:\Projekte\yootheme-supermailer\dev\pack.ps1
# Path: f:\Projekte\yootheme-supermailer\dev
# Created Date: Tuesday, July 30th 2024, 9:41:57 pm
# Author: Necati Meral https://meral.cloud
# 
# Copyright (c) 2024 Meral IT
# 

[CmdletBinding()]
param (
    [Parameter()]
    [string]
    $Version = '1.0.0'
)

$ExtensionElement = 'yoosupermailer'
$WhitelistedElements = @(
    'assets'
    'language'
    'modules'
    'sql'
    'index.html'
    "$ExtensionElement.xml"
    '*.php'
    'README.md'
    'LICENSE'
)

$WorkingDirectory = Join-Path $PSScriptRoot '../'
$TempDirectory = Join-Path $WorkingDirectory 'build' '_w'
$DistDirectory = Join-Path $WorkingDirectory 'build' 'plg_system_yoosupermailer.zip'

# Update current working directory
Set-Location $WorkingDirectory > $null

# Reset temporary directory
if (Test-Path $TempDirectory -PathType Container) {
    Remove-Item $TempDirectory -Recurse -Force > $null
}

New-Item -ItemType Directory -Path $TempDirectory -Force > $null

# Copy whitelisted elements to temporary directory
foreach ($Element in $WhitelistedElements) {
    Copy-Item -Path $Element -Destination $TempDirectory -Recurse -Force
}

# Update version in XML file
$XmlFile = Join-Path $TempDirectory "$ExtensionElement.xml"
$XmlContent = [xml](Get-Content $XmlFile)
$XmlContent.SelectSingleNode('/extension/version').InnerText = $Version
$Xmlcontent.Save($XmlFile)

# Create archive
Compress-Archive -Path "$TempDirectory/*" -DestinationPath $DistDirectory -Force

# Calculate Hash
$Hash = Get-FileHash -Path $DistDirectory -Algorithm SHA512
$Hash = $Hash.Hash.ToLowerInvariant()

# Create update entry
$UpdatesFile = Join-Path $WorkingDirectory 'update.xml'
$UpdatesContent = [xml](Get-Content $UpdatesFile)
$Updates = $UpdatesContent.SelectNodes('/updates/update');
$IsNewUpdate = $False

# Check if update already exists
foreach ($ExistingUpdate in $Updates) {
    if ($ExistingUpdate.version -eq $Version) {
        
        Write-Host "Update $Version already exists; updating entry"
        $Update = $ExistingUpdate
    }
}

if ($Null -eq $Update) {
    $Update = $Updates[-1].CloneNode($true)
    $IsNewUpdate = $True
}

$Update.SelectSingleNode('version').InnerText = $Version
$Update.SelectSingleNode('sha512').InnerText = $Hash

$NameNode = $Update.SelectSingleNode('name')
$NameNode.InnerText = $NameNode.InnerText -replace '(\d+\.\d+\.\d+)', $Version

$DownloadUrlNode = $Update.SelectSingleNode('downloads/downloadurl');
$DownloadUrlNode.InnerText = $DownloadUrlNode.InnerText -replace '(\d+\.\d+\.\d+)', $Version

if ($IsNewUpdate) {
    $UpdatesContent.SelectSingleNode('/updates').AppendChild($Update) > $null
}
$UpdatesContent.Save($UpdatesFile)
