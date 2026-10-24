# 130 особых атак: спецификация на реализацию

Проверка: `tools/special_attacks_spec.php`. Атаки берутся из `attac_power` (937 строк),
особые эффекты — из `attac_dop` (133) и `stat_attak` (55).

**Ключевой факт:** в новом движке особые эффекты поддержаны **по названию атаки**
(`src/Game/BattleMoveEffectCatalog.php`: `'spikes' => 'spikes'`, `'sticky web' => 'sticky_web'`),
а не по номерам эффектов из таблиц. Всё, чего нет в каталоге по имени, в бою не работает.

Итого: атак с побочным эффектом в данных **186**, реализовано **56**,
не реализовано **130**. Ниже они разбиты на группы по данным.

## Как делать

1. Для каждой атаки добавить обработку в `BattleMoveEffectCatalog` (и, если эффект влияет на ход
   боя, — в `BattleEngineService`), как это сделано для `spikes`, `stealth rock`, `sticky web`.
2. Эффект брать из данных по колонкам `attac_dop.dop_effc`, `tip_s`, `setting` и множителям
   `def/atc/sdef/satc/speed/acc`, а не выдумывать: у каждой атаки ниже они указаны.
3. Проверять: `php -l`, гейт `tools/open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD`
   (ожидание `Passed: 25, failed: 0, skipped: 0`), живой бой — PvE хотя бы на одной атаке из группы.

## Группы (по убыванию количества атак)

### эффект=6 | знак=- — атак: 23

- Stomp
- Rolling Kick
- Headbutt
- Bite
- Psybeam
- Bone Club
- Waterfall
- Dizzy Punch
- Rock Slide
- Hyper Fang
- Twister
- Fake Out
- Needle Arm
- Astonish
- Extrasensory
- Dark Pulse
- Air Slash
- Dragon Rush
- Zen Headbutt
- Iron Head
- Chatter
- Heart Stamp
- Icicle Crash

### эффект=- | знак=- | таблица stat_attak | stati=1 — атак: 21

- Meditate
- Minimize
- Withdraw
- Barrier
- Amnesia
- Acid Armor
- Sharpen
- Belly Drum
- Tail Glow
- Cosmic Power
- Aerial Ace
- Howl
- Rock Polish
- Defend Order
- Hone Claws
- Autotomize
- Quiver Dance
- Shell Smash
- Shift Gear
- Work Up
- Cotton Guard

### эффект=5 | знак=- — атак: 11

- Thunder Punch
- Body Slam
- Thunder Shock
- Thunderbolt
- Tri Attack
- DragonBreath
- Bounce
- Force Palm
- Thunder Fang
- Bolt Strike
- Freeze Shock

### эффект=0 | знак=minus | статы:SPD+1 — атак: 10

- Bubble Beam
- Constrict
- Bubble
- Icy Wind
- Rock Tomb
- Hammer Arm
- Low Sweep
- Bulldoze
- Electroweb
- Glaciate

### эффект=- | знак=- | таблица stat_attak | stati=2 — атак: 9

- Kinesis
- Flash
- Cotton Spore
- Scary Face
- Memento
- Feather Dance
- Tickle
- Psycho Boost
- Defog

### эффект=0 | знак=minus | статы:SDEF+1 — атак: 8

- Acid
- Shadow Ball
- Luster Purge
- Bug Buzz
- Focus Blast
- Energy Ball
- Earth Power
- Seed Flare

### эффект=0 | знак=minus | статы:ACCURACY+1 — атак: 7

- Mud-Slap
- Octazooka
- Muddy Water
- Mud Bomb
- Mirror Shot
- Steamroller
- Night Daze

### эффект=3 | знак=- — атак: 5

- Ember
- Flame Wheel
- Fire Fang
- Searing Shot
- Ice Burn

### эффект=0 | знак=minus | статы:DEF+1 — атак: 5

- Steel Wing
- Iron Tail
- Rock Smash
- Crush Claw
- Razor Shell

### эффект=0 | знак=minus | статы:SATK+1 — атак: 5

- Mist Ball
- Flash Cannon
- Charge Beam
- Struggle Bug
- Snarl

### эффект=4 | знак=- — атак: 3

- Ice Punch
- Powder Snow
- Ice Fang

### эффект=7 | знак=- — атак: 3

- Dynamicpunch
- Signal Beam
- Water Pulse

### эффект=0 | знак=plus | статы:DEF+1,ATK+1,SDEF+1,SATK+1,SPD+1 — атак: 3

- AncientPower
- Silver Wind
- Ominous Wind

### эффект=1 | знак=- — атак: 2

- Poison Sting
- Twineedle

### эффект=5 | знак=0 — атак: 2

- Spark
- Discharge

### эффект=0 | знак=plus | статы:ATK+1 — атак: 2

- Metal Claw
- Meteor Mash

### эффект=0 | знак=minus | статы:SDEF+2 — атак: 2

- Overheat
- Acid Spray

### эффект=0 | знак=minus | статы:SATK+2 — атак: 2

- Draco Meteor
- Leaf Storm

### эффект=0 | знак=minus | статы:ATK+1 — атак: 1

- Aurora Beam

### эффект=0 | знак=minus | статы:DEF+1,ATK+1 — атак: 1

- Superpower

### эффект=0 | знак=minus | статы:DEF+1,SDEF+1 — атак: 1

- Close Combat

### эффект=0 | знак=plus | статы:SPD+1 — атак: 1

- Flame Charge

### эффект=2 | знак=- — атак: 1

- Relic Song

### эффект=0 | знак=plus | статы:SATK+1 — атак: 1

- Fiery Dance

### эффект=0 | знак=minus | статы:DEF+1,SDEF+1,SPD+1 — атак: 1

- V-create

