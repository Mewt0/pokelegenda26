# TASK-000 — Установить дизайн-скиллы в Codex

**Исполнитель:** Codex-агент · **Зона:** `C:\Users\grut\.codex\skills`, `~/.codex/config.toml` · **Статус:** todo

## Контекст

DSH-агент уже установил и проверил набор дизайн-инженерных скиллов в `C:\Users\grut\.dsh\skills`. Codex использует тот же формат (`<name>/SKILL.md` с frontmatter `name`/`description`), но свой корень — `C:\Users\grut\.codex\skills`. Нужно, чтобы у Codex был тот же набор: он будет выполнять задачи по вёрстке, иконкам и UI-полировке.

## Что сделать

### 1. Подключить MCP-реестр дизайн-скиллов (даёт ~150 скиллов по запросу, без раздувания каталога)

```powershell
$codex = 'C:\Users\grut\AppData\Local\OpenAI\Codex\bin\5ea220ae823df3d7\codex.exe'
& $codex mcp add ui-skills --url https://www.ui-skills.com/mcp
& $codex mcp list
```

После этого доступны инструменты `list_skills` и `get_skill` (реестр ui-skills.com: shadcn, Anthropic, Emil Kowalski, vercel-labs, accessibility и др.).

### 2. Установить сам набор скиллов (быстрый путь — копия из DSH, тот же формат)

```powershell
$src = 'C:\Users\grut\.dsh\skills'
$dst = 'C:\Users\grut\.codex\skills'
$names = @(
  'ui-ux-pro-max',        # мастер-скилл: стили, палитры, типографика, UX-гайдлайны, GSAP-пресеты
  'baseline-ui',          # анти-слоп: отступы, иерархия, типографика, z-index шкала, состояния
  'create-design-md',     # DESIGN.md: токены и правила из кода/сайта
  'fixing-accessibility', # WCAG: ARIA, клавиатура, фокус, контраст
  'transitions-dev',      # 12 готовых CSS-переходов с prefers-reduced-motion
  'animate',              # решения по анимации (Эмиль Ковальски)
  'mastering-animate-presence',
  'morphing-icons',
  'svg-animations',
  'design-system-checklist', # 42 раздела / 230 чекпоинтов (166 по компонентам)
  'astryx', 'clone-website',
  'gsap-core','gsap-timeline','gsap-scrolltrigger','gsap-plugins','gsap-utils','gsap-performance'
)
foreach ($n in $names) {
  if (Test-Path "$src\$n\SKILL.md") {
    if (Test-Path "$dst\$n") { Remove-Item "$dst\$n" -Recurse -Force }
    Copy-Item "$src\$n" "$dst\$n" -Recurse -Force
    "installed $n"
  } else { "MISSING SOURCE $n" }
}
Get-ChildItem $dst -Directory | Measure-Object | Select-Object -ExpandProperty Count
```

### 3. Альтернатива — установка из первоисточников (если нужна свежая версия)

```powershell
$py = 'C:\Users\grut\.codex\skills\.system\skill-installer\scripts\install-skill-from-github.py'
python $py --repo nextlevelbuilder/ui-ux-pro-max-skill --path .claude/skills/ui-ux-pro-max
python $py --repo greensock/gsap-skills --path skills/gsap-core skills/gsap-timeline skills/gsap-scrolltrigger skills/gsap-plugins skills/gsap-utils skills/gsap-performance
python $py --repo supermemoryai/skills --path skills/svg-animations
python $py --repo greedychipmunk/agent-skills --path astryx
python $py --repo huangmingfu/clone-website --path . --name clone-website
# скиллы из реестра ui-skills (их нет на GitHub в виде репозиториев-скиллов):
foreach ($id in 'baseline-ui','create-design-md','fixing-accessibility','transitions-dev','emilkowalski/animate','mastering-animate-presence','morphing-icons') {
  $dir = "C:\Users\grut\.codex\skills\$($id -replace '.*/','')"
  New-Item -ItemType Directory -Path $dir -Force | Out-Null
  npx -y ui-skills get $id | Out-File -FilePath "$dir\SKILL.md" -Encoding utf8
}
```

> Внимание: `Out-File -Encoding utf8` в Windows PowerShell 5.1 добавляет BOM. Если скилл не читается — перезапиши через `[System.IO.File]::WriteAllText($path, $text, (New-Object System.Text.UTF8Encoding($false)))`.

## Критерии приёмки

1. `codex mcp list` показывает `ui-skills`.
2. В `C:\Users\grut\.codex\skills` присутствуют все 18 каталогов из списка (или 7 из альтернативного пути), каждый с `SKILL.md` и валидным frontmatter (`name`, `description`).
3. Ни один существующий скилл (`.system` и базовые) не удалён и не испорчен.

## Доказательства в отчёте

- Вывод `codex mcp list`.
- Список установленных каталогов с числом файлов.
- Проверка frontmatter одного скилла (первые 4 строки `SKILL.md`).
- Что не получилось и почему.
