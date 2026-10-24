<#
.SYNOPSIS
  Прогоняет очередь задач docs/agent-tasks/TASK-*.md через `codex exec`.

.DESCRIPTION
  Для каждой задачи: запускает Codex-агента в песочнице workspace-write с корнем проекта,
  пишет последний ответ агента в TASK-<N>-*.report.md и ведёт общий лог.
  Останавливается, если Codex упёрся в лимит использования, чтобы не жечь попытки.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/run-codex-queue.ps1 -At 22:48
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/run-codex-queue.ps1 -Tasks 001
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/run-codex-queue.ps1 -DryRun
#>
param(
  [string[]] $Tasks = @('001', '002', '003'),
  [string]   $At,
  [string]   $Root  = 'E:\OSPanel\domains\pokemonchic.com',
  [string]   $Codex = 'C:\Users\grut\AppData\Local\OpenAI\Codex\bin\5ea220ae823df3d7\codex.exe',
  [switch]   $DryRun
)

$ErrorActionPreference = 'Continue'
$queueDir = Join-Path $Root 'docs\agent-tasks'
$logFile  = Join-Path $queueDir ('queue-run-{0}.log' -f (Get-Date -Format 'yyyy-MM-dd_HHmmss'))

function Write-QueueLog([string] $Message) {
  $line = '[{0}] {1}' -f (Get-Date -Format 'HH:mm:ss'), $Message
  Write-Host $line
  Add-Content -Path $logFile -Value $line -Encoding UTF8
}

if ($At) {
  $target = [datetime]::ParseExact($At, 'HH:mm', [System.Globalization.CultureInfo]::InvariantCulture)
  if ($target -lt (Get-Date)) { $target = $target.AddDays(1) }
  Write-QueueLog ('ожидание до {0} (сейчас {1})' -f $target.ToString('HH:mm'), (Get-Date -Format 'HH:mm'))
  while ((Get-Date) -lt $target) { Start-Sleep -Seconds 30 }
  Write-QueueLog 'старт очереди'
}

if (-not (Test-Path $Codex)) { Write-QueueLog ('ОШИБКА: не найден codex: {0}' -f $Codex); exit 2 }

foreach ($num in $Tasks) {
  $spec = Get-ChildItem $queueDir -Filter ('TASK-{0}-*.md' -f $num) |
          Where-Object { $_.Name -notlike '*.report.md' } | Select-Object -First 1
  if (-not $spec) { Write-QueueLog ('TASK-{0}: спека не найдена, пропуск' -f $num); continue }

  $report = Join-Path $queueDir ($spec.BaseName + '.report.md')

  if ($DryRun) { Write-QueueLog ('DRY-RUN: {0} -> {1}' -f $spec.Name, $report); continue }

  Write-QueueLog ('TASK-{0}: запуск ({1})' -f $num, $spec.Name)
  $prompt = 'Выполни задачу ' + $spec.Name + '. Соблюдай AGENTS.md: не меняй файлы вне зоны задачи, приведи доказательства по формату из спеки.'
  $output = & $Codex exec -C $Root -s workspace-write -o $report $prompt 2>&1
  $code   = $LASTEXITCODE
  $tail   = ($output | Select-Object -Last 6) -join ' | '
  Write-QueueLog ('TASK-{0}: exit={1}; хвост: {2}' -f $num, $code, $tail)

  if ($output -match 'usage limit') {
    Write-QueueLog 'ЛИМИТ ИСПОЛЬЗОВАНИЯ CODEX: останавливаю очередь. Запусти скрипт снова после сброса лимита.'
    break
  }
}

Write-QueueLog ('очередь завершена. Лог: {0}' -f $logFile)
