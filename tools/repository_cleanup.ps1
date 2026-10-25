param([switch]$Apply, [string]$ArchiveRoot)
$ErrorActionPreference = 'Stop'
$workspaceRoot = [IO.Path]::GetFullPath((Split-Path -Parent $PSScriptRoot)).TrimEnd('\', '/')
if (!$ArchiveRoot) { $ArchiveRoot = Join-Path (Split-Path -Parent (Split-Path -Parent $workspaceRoot)) ('maintenance/pokemonchic-cleanup-' + (Get-Date -Format 'yyyyMMdd-HHmmss')) }
$archivePath = [IO.Path]::GetFullPath($ArchiveRoot).TrimEnd('\', '/')
if ($archivePath.StartsWith($workspaceRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase) -or $archivePath -eq $workspaceRoot) { throw 'Archive must be outside the project/webroot' }
$candidates = @(
    Get-ChildItem -LiteralPath $workspaceRoot -File -Force | Where-Object {
        $_.Name -match '\.(zip|rar|7z|tar|gz|xlsx)$' -or $_.Name -match '^(?:\d{1,2}|ChatGPT Image.*|chrome_.*)\.png$' -or
        $_.Name -match '^debug_.*\.(html|txt)$' -or $_.Name -match '\.sql$' -or
        $_.Name -in @('_qa-view.php','_qa-viewport.php','probe.js','shot.js','build_referat.py','render_pdf_pages.py','download.py','test.html','Rewire_mysql.json','Покелегенда — Игровой мир.html')
    }
    foreach ($directory in @('downloaded_assets','downloaded_assets_sorted','downloaded_images','render_referat')) {
        $candidate = Join-Path $workspaceRoot $directory
        if (Test-Path -LiteralPath $candidate) { Get-Item -LiteralPath $candidate }
    }
    Get-ChildItem -LiteralPath $workspaceRoot -Directory | Where-Object { $_.Name -like '*_files' }
    $nurseryBackup = Join-Path $workspaceRoot 'public/css/game-pokemon.css.bak-nursery'
    if (Test-Path -LiteralPath $nurseryBackup) { Get-Item -LiteralPath $nurseryBackup }
) | Sort-Object FullName -Unique
$entries = @()
foreach ($candidate in $candidates) {
    $resolved = [IO.Path]::GetFullPath($candidate.FullName)
    if (!$resolved.StartsWith($workspaceRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Source escaped workspace' }
    if ($candidate.Attributes -band [IO.FileAttributes]::ReparsePoint) { throw 'Refusing linked cleanup source' }
    $relative = $resolved.Substring($workspaceRoot.Length + 1)
    $destination = [IO.Path]::GetFullPath((Join-Path $archivePath $relative))
    if (!$destination.StartsWith($archivePath + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Destination escaped archive' }
    $files = if ($candidate.PSIsContainer) { @(Get-ChildItem -LiteralPath $resolved -File -Recurse -Force) } else { @($candidate) }
    $entries += [pscustomobject]@{source=$resolved;destination=$destination;directory=$candidate.PSIsContainer;files=$files.Count;bytes=($files | Measure-Object Length -Sum).Sum}
}
$summary = [ordered]@{status='preview';created_at=[DateTime]::UtcNow.ToString('o');workspace=$workspaceRoot;archive=$archivePath;entries=$entries}
if ($Apply) {
    if (Test-Path -LiteralPath $archivePath) { throw 'Archive already exists; use a fresh destination' }
    New-Item -ItemType Directory -Path $archivePath | Out-Null
    $manifest = Join-Path $archivePath 'CLEANUP_MANIFEST.json'
    $summary.status = 'moving'
    $summary | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $manifest -Encoding utf8
    foreach ($entry in $entries) {
        $parent = Split-Path -Parent $entry.destination
        if (!(Test-Path -LiteralPath $parent)) { New-Item -ItemType Directory -Path $parent -Force | Out-Null }
        Move-Item -LiteralPath $entry.source -Destination $entry.destination
    }
    $summary.status = 'completed'
    $summary | ConvertTo-Json -Depth 8 | Set-Content -LiteralPath $manifest -Encoding utf8
}
$bytes = ($entries | Measure-Object bytes -Sum).Sum
Write-Output ("{0}: {1} entries, {2} files, {3:N1} MiB; archive={4}" -f $summary.status, $entries.Count, ($entries | Measure-Object files -Sum).Sum, ($bytes / 1MB), $archivePath)
if (!$Apply) { $entries | Select-Object source,files,bytes | ConvertTo-Json -Depth 4 }
