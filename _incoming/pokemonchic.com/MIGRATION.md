# Миграция на PHP 8.x — League of Pokemons

## Что изменено

### Все файлы
- Добавлен `declare(strict_types=1);`
- Убраны короткие теги `<?php` → `<?php`
- Заменены `print` / `die()` → `echo` / `exit`
- `htmlspecialchars()` с явным `ENT_QUOTES, 'UTF-8'` везде где выводятся данные

---

### error.php
| Было | Стало |
|---|---|
| `mysql_error()` | Убрано (нет аналога в mysqli без подключения) |
| `count()` | `count()` |
| Нет перехвата исключений | `set_exception_handler()` для `Throwable` |

---

### ban.php
| Было | Стало |
|---|---|
| `getenv("HTTP_X_FORWARDED_FOR")` | `$_SERVER['HTTP_X_FORWARDED_FOR']` |
| Нет валидации IP | `filter_var($ip, FILTER_VALIDATE_IP)` |
| Разрозненные проверки | Функция `getRealIp()` |

---

### autoriz.php
| Было | Стало |
|---|---|
| `mysql_escape_string()` | Параметры через `first()` / `update()` с экранированием внутри |
| `strrev($password)."b3p6f"` | Сохранена совместимость со старыми хэшами, задокументировано |
| `getenv()` для IP | `getRealIp()` с `filter_var()` |
| `strlen()` | `mb_strlen()` для корректной работы с UTF-8 |
| `filter_input()` не использовался | Все POST-данные через `filter_input()` |

> **Совет по безопасности:** при следующей возможности мигрируйте пароли на `password_hash(PASSWORD_BCRYPT)`.
> Схема миграции: при логине проверяете старый хэш → если совпадает, пересохраняете через `password_hash()`.

---

### buttons_world.php
| Было | Стало |
|---|---|
| Смешанный HTML/PHP без функций | Вспомогательная функция `imgButton()` |
| `<META HTTP-EQUIV>` | `<meta charset="utf-8">` |
| HTML 4.01 Transitional DOCTYPE | HTML5 DOCTYPE |

---

### attak_pokes.php
| Было | Стало |
|---|---|
| `mysql_escape_string()` | Убрано, экранирование внутри query-функций |
| `fopen/fwrite/fclose` | `file_put_contents(..., FILE_APPEND | LOCK_EX)` |
| `isset()` не везде | Проверки через `filter_input()` и `??` |

---

### img_gen.php / pdx.php
| Было | Стало |
|---|---|
| Короткий тег `<?php` | `<?php` |
| `$_GET['let']` без фильтрации | `filter_input()` + `preg_replace()` — только буквы/цифры |
| Нет `imagedestroy()` | `imagedestroy($src)` после вывода |
| Нет заголовков кэша | `Cache-Control: no-cache` |

> `pdx.php` — дубликат `img_gen.php` с кириллицей (кодировка сломана). Рекомендуется удалить и использовать только `img_gen.php`.

---

### itemsinpage_class3.php
| Было | Стало |
|---|---|
| Нет типизации | Все свойства и методы типизированы |
| `count()` | `count()` |
| `inpageinc` не использовался | `range()` с шагом |

---

### mailTo.php
| Было | Стало |
|---|---|
| `mysql_escape_string($code)` | Параметр передаётся через `obr_txt()` и подставляется в prepared-стиле |
| `die(...)` | `exit` + `http_response_code()` |

---

### page.php
| Было | Стало |
|---|---|
| Нет валидации `$_GET['id']` | `filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)` |
| Нет fallback | Редирект на `/game.php?go=start` при невалидном id |

---

### moder_func.php
| Было | Стало |
|---|---|
| `for(0..14){ sleep(1); } print 123;` | Заглушка с проверкой прав + TODO |

---

### .htaccess
| Было | Стало |
|---|---|
| `AddDefaultCharset cp1251` | `AddDefaultCharset UTF-8` |
| `php_value default_charset cp1251` | `php_value default_charset UTF-8` |
| `Order allow,deny / Deny from all` (Apache 2.2) | `Require all denied` (Apache 2.4) |
| `display_errors on` | `display_errors off` (для production) |

---

## Что НЕ изменялось

- Логика работы игры
- Структура БД и имена таблиц
- Формат хэша паролей (совместимость)
- API функций `first()`, `update()`, `insert()`, `delete()`, `select()`, `query()` из `db3.php`

## Следующие шаги (рекомендации)

1. Перейти на `password_hash()` / `password_verify()` для паролей
2. Убрать `strrev()` + захардкоженную соль `b3p6f`, использовать `PASSWORD_BCRYPT`
3. Переписать `db3.php` на PDO с подготовленными запросами
4. Включить `CSP`, `X-Frame-Options`, `X-Content-Type-Options` заголовки
5. Удалить `pdx.php` (дубликат `img_gen.php`)
6. Убрать `moder_func.php` — заглушка с `sleep(15)` явно случайно попала в продакшн
