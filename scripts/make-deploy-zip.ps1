param(
    [string]$OutFile = "deploy.zip"
)

$ErrorActionPreference = 'Stop'

$repoRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $repoRoot

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw "git is required to create the deploy archive."
}

Write-Host "Creating deploy archive from tracked files (git archive)" -ForegroundColor Cyan

if (Test-Path -LiteralPath $OutFile) {
    Remove-Item -LiteralPath $OutFile -Force
}

git archive --format=zip --output $OutFile HEAD

Write-Host "Wrote $OutFile" -ForegroundColor Green
