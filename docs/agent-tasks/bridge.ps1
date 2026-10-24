<#
.SYNOPSIS
  Мост между DSH-агентом и Codex-агентом: прямой обмен сообщениями и прогон очереди задач.

.DESCRIPTION
  Работает через `codex exec` / `codex exec resume`, поэтому диалог непрерывный: первый вызов
  создаёт сессию, дальше сообщения идут в ту же сессию. Состояние (sessionId, обработанные
  сообщения, лимит) хранится в bus/state.json. Упёршись в лимит использования, мост ждёт
  указанное время и продолжает сам.

  Раскладка:
    bus/inbox/   — сообщения для Codex (кладёт DSH-агент)
    bus/outbox/  — ответы Codex (кладёт мост) и вопросы Codex к DSH-агенту
    bus/state.json, bus/bridge.log

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/bridge.ps1 -Status
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/bridge.ps1 -Ask "сколько CSS-файлов в public/css?"
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/bridge.ps1 -Send "проверь TASK-001"
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/bridge.ps1 -Run
  powershell -ExecutionPolicy Bypass -File docs/agent-tasks/bridge.ps1 -Watch -ProcessQueue -At 22:48
#>
param(
  [string] $Root = 'E:\OSPanel\domains\pokemonchic.com',
  [string] $Codex = 'C:\Users\grut\AppData\Local\OpenAI\Codex\bin\5ea220ae823df3d7\codex.exe',
  [string] $Send,
  [string] $SendFile,
  [string] $Ask,
  [switch] $Run,
  [switch] $Watch,
  [switch] $Status,
  [switch] $ProcessQueue,
  [switch] $NewSession,
  [string] $At,
  [string[]] $Tasks = @('001', '002', '003'),
  [int]    $PollSeconds = 20
)

$ErrorActionPreference = 'Continue'
$bus   = Join-Path $Root 'docs\agent-tasks\bus'
$inbox = Join-Path $bus 'inbox'
$outbox = Join-Path $bus 'outbox'
$statePath = Join-Path $bus 'state.json'
$logPath   = Join-Path $bus 'bridge.log'

foreach ($d in @($bus, $inbox, $outbox)) { if (-not (Test-Path $d)) { New-Item -ItemType Directory -Path $d -Force | Out-Null } }

function Write-BridgeLog([string] $Message) {
  $line = '[{0}] {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Message
  Write-Host $line
  Add-Content -Path $logPath -Value $line -Encoding UTF8
}

function Get-State {
  if (Test-Path $statePath) {
    try { return (Get-Content $statePath -Raw -Encoding UTF8 | ConvertFrom-Json) } catch { }
  }
  return [pscustomobject]@{ sessionId = $null; processed = @(); quotaUntil = $null; lastError = $null; runs = 0 }
}

function Save-State($state) {
  $state | ConvertTo-Json -Depth 4 | Set-Content -Path $statePath -Encoding UTF8
}

function Invoke-Codex([string] $Prompt, [string] $OutFile, [switch] $Resume) {
  if ($OutFile -and (Test-Path $OutFile)) { Remove-Item $OutFile -Force -ErrorAction SilentlyContinue }
  $state = Get-State
  $useResume = ($Resume -and $state.sessionId)
  if ($useResume) {
    Write-BridgeLog ('codex exec resume {0} (сессия продолжается)' -f $state.sessionId)
    $out = & $Codex exec resume $state.sessionId -C $Root -s workspace-write -o $OutFile $Prompt 2>&1
  } else {
    Write-BridgeLog 'codex exec (новая сессия)'
    $out = & $Codex exec -C $Root -s workspace-write -o $OutFile $Prompt 2>&1
  }
  $text = ($out | Out-String)

  $m = [regex]::Match($text, 'session id:\s*([0-9a-f-]{36})')
  if ($m.Success) { $state.sessionId = $m.Groups[1].Value }
  $state.runs = [int]$state.runs + 1

  if ($text -match 'usage limit') {
    $q = [regex]::Match($text, 'try again at (\d{1,2}:\d{2}\s*[AP]M)')
    $when = $null
    if ($q.Success) {
      try { $when = [datetime]::Parse($q.Groups[1].Value).ToString('s') } catch { $when = $null }
    }
    if (-not $when) { $when = (Get-Date).AddMinutes(90).ToString('s') }
    $state.quotaUntil = $when
    $state.lastError = 'usage limit'
    Save-State $state
    Write-BridgeLog ('ЛИМИТ CODEX. Следующая попытка после {0}' -f $when)
    return @{ ok = $false; text = $text; reason = 'quota' }
  }

  $state.lastError = $null
  Save-State $state
  return @{ ok = $true; text = $text; reason = 'ok' }
}

function Wait-Quota {
  $state = Get-State
  if (-not $state.quotaUntil) { return }
  $until = [datetime]::Parse($state.quotaUntil)
  if ($until -gt (Get-Date)) {
    Write-BridgeLog ('ожидание лимита до {0}' -f $until.ToString('HH:mm:ss'))
    while ((Get-Date) -lt $until) { Start-Sleep -Seconds 30 }
  }
  $state.quotaUntil = $null
  Save-State $state
}

function Run-Queue {
  foreach ($num in $Tasks) {
    $spec = Get-ChildItem (Join-Path $Root 'docs\agent-tasks') -Filter ('TASK-{0}-*.md' -f $num) |
            Where-Object { $_.Name -notlike '*.report.md' } | Select-Object -First 1
    if (-not $spec) { Write-BridgeLog ('TASK-{0}: спека не найдена' -f $num); continue }
    $report = Join-Path $spec.DirectoryName ($spec.BaseName + '.report.md')
    if (Test-Path $report) { Write-BridgeLog ('TASK-{0}: отчёт уже есть, пропуск' -f $num); continue }
    Wait-Quota
    $prompt = 'Выполни задачу ' + $spec.Name + ' из docs/agent-tasks. Соблюдай AGENTS.md: работай только в своей зоне, ' +
              'ничего не удаляй, приведи в отчёте команды и результаты. Отчёт: ' + $report
    $r = Invoke-Codex $prompt $report -Resume
    Write-BridgeLog ('TASK-{0}: результат={1}' -f $num, $r.reason)
    if ($r.reason -eq 'quota') { return }
  }
}

function Process-Inbox {
  $state = Get-State
  $files = Get-ChildItem $inbox -Filter '*.md' -ErrorAction SilentlyContinue | Sort-Object Name
  foreach ($f in $files) {
    if ($state.processed -contains $f.Name) { continue }
    Wait-Quota
    $reply = Join-Path $outbox ($f.BaseName + '.reply.md')
    $body  = Get-Content $f.FullName -Raw -Encoding UTF8
    Write-BridgeLog ('inbox: {0}' -f $f.Name)
    $r = Invoke-Codex $body $reply -Resume
    if ($r.reason -eq 'quota') { return }
    $fresh = Get-State
    $fresh.processed = @($fresh.processed) + $f.Name
    Save-State $fresh
    Write-BridgeLog ('outbox: {0}' -f (Split-Path $reply -Leaf))
  }
}

# ── Режимы ──────────────────────────────────────────────────────────────────
if ($Status) {
  $state = Get-State
  Write-Host ('sessionId : {0}' -f $state.sessionId)
  Write-Host ('runs      : {0}' -f $state.runs)
  Write-Host ('quotaUntil: {0}' -f $state.quotaUntil)
  Write-Host ('processed : {0}' -f (($state.processed | Measure-Object).Count))
  Write-Host ('inbox     : {0} файлов' -f (Get-ChildItem $inbox -Filter *.md -ErrorAction SilentlyContinue | Measure-Object).Count)
  Write-Host ('outbox    : {0} файлов' -f (Get-ChildItem $outbox -Filter *.md -ErrorAction SilentlyContinue | Measure-Object).Count)
  exit 0
}

if ($Send -or $SendFile) {
  $text = if ($SendFile) { Get-Content $SendFile -Raw -Encoding UTF8 } else { $Send }
  $name = '{0}-msg.md' -f (Get-Date -Format 'yyyyMMdd-HHmmss')
  Set-Content -Path (Join-Path $inbox $name) -Value $text -Encoding UTF8
  Write-Host ('в inbox: {0}' -f $name)
  if (-not ($Run -or $Watch -or $Ask)) { exit 0 }
}

if ($Ask) {
  $name = '{0}-ask.md' -f (Get-Date -Format 'yyyyMMdd-HHmmss')
  Set-Content -Path (Join-Path $inbox $name) -Value $Ask -Encoding UTF8
  Process-Inbox
  $reply = Join-Path $outbox (([System.IO.Path]::GetFileNameWithoutExtension($name)) + '.reply.md')
  if (Test-Path $reply) { Get-Content $reply -Raw -Encoding UTF8 } else { Write-Host 'ответа нет (лимит или ошибка) — см. bus/bridge.log' }
  exit 0
}

if ($At -and ($Watch -or $Run -or $ProcessQueue)) {
  $target = [datetime]::ParseExact($At, 'HH:mm', [System.Globalization.CultureInfo]::InvariantCulture)
  if ($target -lt (Get-Date)) { $target = $target.AddDays(1) }
  Write-BridgeLog ('ожидание до {0}' -f $target.ToString('HH:mm'))
  while ((Get-Date) -lt $target) { Start-Sleep -Seconds 30 }
}

if ($ProcessQueue) { Wait-Quota; Run-Queue }

if ($Run) { Wait-Quota; Process-Inbox; exit 0 }

if ($Watch) {
  Write-BridgeLog ('watch: старт (poll {0} c), уже обработано {1}' -f $PollSeconds, (Get-State).processed.Count)
  if ($ProcessQueue -and -not $At) { }
  while ($true) {
    Wait-Quota
    Process-Inbox
    $state = Get-State
    if ($state.quotaUntil) { Start-Sleep -Seconds 60 } else { Start-Sleep -Seconds $PollSeconds }
  }
}

if (-not ($Status -or $Send -or $SendFile -or $Ask -or $Run -or $Watch -or $ProcessQueue)) {
  Get-Help $PSCommandPath -Detailed
}
