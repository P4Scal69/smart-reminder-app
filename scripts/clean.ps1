param(
    [switch]$IncludeVendors,
    [switch]$IncludeNodeModules,
    [switch]$IncludeBuiltAssets
)

$ErrorActionPreference = 'Stop'

function Remove-PathIfExists([string]$Path) {
    if (Test-Path -LiteralPath $Path) {
        Write-Host "Removing $Path" -ForegroundColor Yellow
        Remove-Item -LiteralPath $Path -Recurse -Force
    }
}

$repoRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $repoRoot

Write-Host "Cleaning Laravel caches + runtime artifacts" -ForegroundColor Cyan

Remove-PathIfExists "storage\\logs"
New-Item -ItemType Directory -Path "storage\\logs" -Force | Out-Null

Remove-PathIfExists "storage\\framework\\cache"
Remove-PathIfExists "storage\\framework\\sessions"
Remove-PathIfExists "storage\\framework\\views"

New-Item -ItemType Directory -Path "storage\\framework\\cache" -Force | Out-Null
New-Item -ItemType Directory -Path "storage\\framework\\sessions" -Force | Out-Null
New-Item -ItemType Directory -Path "storage\\framework\\views" -Force | Out-Null

if (Test-Path -LiteralPath "bootstrap\\cache") {
    Get-ChildItem -LiteralPath "bootstrap\\cache" -Filter "*.php" -File -ErrorAction SilentlyContinue | Remove-Item -Force
}

if (Test-Path -LiteralPath "public\\hot") {
    Remove-PathIfExists "public\\hot"
}

if ($IncludeBuiltAssets) {
    Remove-PathIfExists "public\\build"
}

if ($IncludeNodeModules) {
    Remove-PathIfExists "node_modules"
}

if ($IncludeVendors) {
    Remove-PathIfExists "vendor"
}

Write-Host "Done." -ForegroundColor Green
