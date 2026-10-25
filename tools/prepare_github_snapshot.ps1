param([Parameter(Mandatory=$true)][string]$Destination)
$ErrorActionPreference = 'Stop'
$sourceRoot = [IO.Path]::GetFullPath((Split-Path -Parent $PSScriptRoot)).TrimEnd('\', '/')
$publishRoot = [IO.Path]::GetFullPath($Destination).TrimEnd('\', '/')
if ($publishRoot -eq $sourceRoot -or $publishRoot.StartsWith($sourceRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Use an isolated checkout outside the live project' }
if (!(Test-Path -LiteralPath (Join-Path $publishRoot '.git'))) { throw 'Destination must be an existing isolated Git checkout' }
$runtimeRoots = @('admin','app','config','css','database','images','img','include','new_diz','new_diz4','pok','public','script','src','views','tools','sql','.github')
$rootFiles = @('.editorconfig','.gitignore','.env.example','.htaccess','composer.json','favicon.ico','AGENTS.md','README.md','README_RU.md','CONTRIBUTING.md','PROJECT_CONTEXT.md','PROJECT_MAP_RU.md','PROJECT_STRUCTURE_RU.md','REWRITE_STATUS.md','DESIGN_REFRESH_AND_BUGFIX_PLAN.md')
function Test-PublishPath([string]$Relative) {
    $path = $Relative.Replace('\','/')
    if ($path -match '(?i)(?:^|/)(?:\.git|\.hybrid|\.codebase-memory|node_modules|storage|tmp|prototypes|backups?)(?:/|$)') { return $false }
    if ($path -match '(?i)(?:^|/)\.env(?:\..*)?$' -and $path -ne '.env.example') { return $false }
    if ($path -match '(?i)\.bak(?:-|$)|\.(?:zip|rar|7z|gz|tar|log|xlsx|pyc)$') { return $false }
    if ($path -match '^img/room/generated-[^/]+/(?:icons-)?preview\.html$') { return $false }
    if ($path -match '^tools/mysql84' -or $path -match '^tools/mysql_84') { return $false } # separate unfinished compatibility task
    if ($path -match '\.sql$' -and $path -notmatch '^database/migrations/' -and $path -notmatch '^sql/OPTIONAL_[A-Z_]+\.sql$') { return $false }
    if ($path.StartsWith('docs/')) {
        return $path -match '^docs/(?:README\.md|GETTING_STARTED\.md|TESTING\.md|RELEASE_NOTES_2026-10-25\.md|media/[^/]+\.(?:svg|png|webp))$'
    }
    if ($path.Contains('/')) { return $runtimeRoots -contains $path.Split('/')[0] }
    if ($rootFiles -contains $path) { return $true }
    return $path -match '\.php$' -and $path -notmatch '^_qa-'
}
$files = @{}
foreach ($directory in ($runtimeRoots + @('docs'))) {
    $folder = Join-Path $sourceRoot $directory
    if (!(Test-Path -LiteralPath $folder)) { continue }
    foreach ($file in Get-ChildItem -LiteralPath $folder -File -Recurse -Force) {
        if ($file.Attributes -band [IO.FileAttributes]::ReparsePoint) { continue }
        $relative = $file.FullName.Substring($sourceRoot.Length + 1).Replace('\','/')
        if (Test-PublishPath $relative) { $files[$relative] = $file.FullName }
    }
}
foreach ($file in Get-ChildItem -LiteralPath $sourceRoot -File -Force) {
    if (Test-PublishPath $file.Name) { $files[$file.Name] = $file.FullName }
}
# Prune files only in the isolated checkout, never the working game or its Git index.
$removed = 0
foreach ($relative in (& git -C $publishRoot -c core.quotepath=false ls-files)) {
    if ($files.ContainsKey($relative)) { continue }
    $target = [IO.Path]::GetFullPath((Join-Path $publishRoot $relative))
    if (!$target.StartsWith($publishRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Prune target escaped isolated checkout' }
    if (Test-Path -LiteralPath $target -PathType Leaf) { Remove-Item -LiteralPath $target; $removed++ }
}
$manifest = @()
foreach ($relative in ($files.Keys | Sort-Object)) {
    $target = [IO.Path]::GetFullPath((Join-Path $publishRoot $relative))
    if (!$target.StartsWith($publishRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Copy target escaped isolated checkout' }
    $parent = Split-Path -Parent $target
    if (!(Test-Path -LiteralPath $parent)) { New-Item -ItemType Directory -Path $parent -Force | Out-Null }
    Copy-Item -LiteralPath $files[$relative] -Destination $target -Force
    $hash = (Get-FileHash -LiteralPath $target -Algorithm SHA256).Hash
    $sourceHash = (Get-FileHash -LiteralPath $files[$relative] -Algorithm SHA256).Hash
    if ($hash -ne $sourceHash) { throw ('Source changed during snapshot: ' + $relative) }
    $manifest += [pscustomobject]@{path=$relative;sha256=$hash}
}
$manifestPath = Join-Path (Split-Path -Parent $publishRoot) ((Split-Path -Leaf $publishRoot) + '-manifest.json')
$manifest | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath $manifestPath -Encoding utf8
Write-Output ("Snapshot: copied {0} files; pruned {1} previous non-product files. Manifest: {2}" -f $manifest.Count, $removed, $manifestPath)
