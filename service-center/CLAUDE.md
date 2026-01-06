# Rehabilitation Center - Developer Roadmap

## Project Overview

Система бронирования и управления реабилитационным центром. Построена на Laravel 12, PHP 8.2.
Язык интерфейса: украинский.

**Основные функции:**
- Онлайн запись клиентов к мастерам
- Управление расписанием мастеров
- Telegram уведомления (Bot API + MadelineProto)
- Админ-панель с календарём записей
- Управление услугами, страницами, настройками

---

## Quick Reference (Быстрый справочник)

### Структура ролей пользователей
| Роль | Доступ |
|------|--------|
| `admin` | Полный доступ ко всему |
| `master` | Свои записи, dashboard |
| `client` | Просмотр/отмена своих записей |

### Ключевые модели

| Модель | Таблица | Назначение |
|--------|---------|------------|
| `User` | users | Все пользователи (роль в поле `role`) |
| `Appointment` | appointments | Записи на приём |
| `Service` | services | Услуги центра |
| `MasterService` | master_services | Pivot: услуги мастера с ценой |
| `NotificationTemplate` | notification_templates | Шаблоны Telegram сообщений |
| `NotificationLog` | notification_logs | История отправки |
| `Page` | pages | Динамические страницы |
| `Setting` | settings | Настройки (key-value) |
| `TextBlock` | text_blocks | Контентные блоки (кешированные) |
| `ServiceFaq` | service_faqs | FAQ по услугам |

### Связи моделей

```
User (role=master)
  ├── masterServices → MasterService[] (цены/длительность)
  │     └── service → Service
  └── masterAppointments → Appointment[]

User (role=client)
  └── clientAppointments → Appointment[]

Appointment
  ├── client → User
  ├── master → User
  └── service → Service

Service
  ├── masters → User[] (through master_services)
  └── faqs → ServiceFaq[]
```

### Структура work_schedule (JSON в User)

```json
{
  "monday": {"start": "09:00", "end": "17:00", "is_working": true},
  "tuesday": {"start": "09:00", "end": "17:00", "is_working": true},
  "wednesday": {"start": "09:00", "end": "17:00", "is_working": false}
}
```

### Статусы записей (Appointment)
- `scheduled` — запланировано
- `completed` — завершено
- `cancelled` — отменено

---

## Routes Map (Карта маршрутов)

### Public Routes
```
GET  /                                    → HomeController@index
GET  /services/{service}                  → ServiceController@show
GET  /masters/{master}                    → MasterController@show
GET  /masters/{master}/available-slots/{date}/{service}  → AJAX slots

GET  /appointment/create                  → AppointmentController@create
POST /appointment                         → AppointmentController@store
GET  /appointment/success                 → AppointmentController@success
PATCH /appointment/{id}/cancel            → AppointmentController@cancel

GET  /{slug}                              → Dynamic page (Page model)
```

### Admin Routes (prefix: /admin, middleware: auth + role)
```
GET  /admin                               → Dashboard (calendar)
GET  /admin/appointments                  → List appointments
GET  /admin/appointments/create-manual    → Manual booking form

# Resources (admin only):
/admin/clients      → ClientController (CRUD)
/admin/masters      → MasterController (CRUD)
/admin/services     → ServiceController (CRUD)
/admin/pages        → PageController (CRUD + blocks)
/admin/settings     → SettingController
/admin/notifications → NotificationController (templates, logs, send)
```

---

## Controllers Overview

### Public Controllers (app/Http/Controllers/)

| Controller | Файл | Ответственность |
|------------|------|-----------------|
| `HomeController` | HomeController.php | Главная страница |
| `AppointmentController` | AppointmentController.php | Запись на приём |
| `ServiceController` | ServiceController.php | Страница услуги |
| `MasterController` | MasterController.php | Профиль мастера, слоты |

### Admin Controllers (app/Http/Controllers/Admin/)

| Controller | Файл | Ответственность |
|------------|------|-----------------|
| `DashboardController` | DashboardController.php | Календарь, статистика |
| `AppointmentController` | AppointmentController.php | CRUD записей |
| `ManualAppointmentController` | ManualAppointmentController.php | Ручное создание записи |
| `ClientController` | ClientController.php | CRUD клиентов |
| `MasterController` | MasterController.php | CRUD мастеров |
| `ServiceController` | ServiceController.php | CRUD услуг + FAQ |
| `PageController` | PageController.php | Страницы + блоки |
| `SettingController` | SettingController.php | Настройки центра |
| `NotificationController` | NotificationController.php | Telegram уведомления |

---

## Services & Jobs

### Telegram интеграция

| Класс | Назначение |
|-------|------------|
| `MasterTelegramBotNotificationService` | Уведомления мастерам через Bot API |
| `TelegramNotificationService` | Bulk рассылка через MadelineProto |
| `SendMasterTelegramNotification` (Job) | Очередь уведомлений |

**Конфигурация (.env):**
```env
TELEGRAM_BOT_TOKEN=xxx        # Bot API токен
TELEGRAM_API_ID=xxx           # my.telegram.org
TELEGRAM_API_HASH=xxx         # my.telegram.org
```

**Авторизация MadelineProto:**
```bash
php artisan telegram:auth
```

---

## File Structure

```
app/
├── Console/Commands/
│   └── TelegramAuth.php           # Artisan команда авторизации
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                 # 9 контроллеров
│   │   └── (public controllers)   # 4 контроллера
│   └── Middleware/
│       └── RoleMiddleware.php     # Проверка ролей
├── Jobs/
│   └── SendMasterTelegramNotification.php
├── Models/                        # 10 моделей
└── Services/                      # Telegram сервисы

resources/views/
├── layouts/
│   ├── app.blade.php              # Public layout
│   └── admin.blade.php            # Admin layout
├── admin/                         # Admin views
├── appointments/                  # Booking views
├── masters/                       # Master profiles
├── services/                      # Service pages
└── home.blade.php                 # Landing page

routes/
├── web.php                        # Все веб-маршруты
└── console.php                    # Console routes
```

---

## Common Tasks (Частые задачи)

### Добавить новую услугу
1. Admin panel → Services → Create
2. Или: создать запись в `services` таблице
3. Назначить мастерам через `master_services`

### Добавить нового мастера
1. Admin panel → Masters → Create
2. Указать `role = master`
3. Настроить `work_schedule` (JSON)
4. Привязать услуги с ценами

### Создать запись вручную
1. Admin panel → Appointments → Manual Create
2. Выбрать клиента, мастера, услугу, дату/время
3. Опционально: разрешить наложение времени

### Отправить Telegram рассылку
1. Admin panel → Notifications
2. Выбрать шаблон и записи
3. Click "Надіслати"

---

## Placeholders для шаблонов уведомлений

```
{client_name}    - Имя клиента
{master_name}    - Имя мастера
{service_name}   - Название услуги
{date}           - Дата записи
{time}           - Время записи
{duration}       - Длительность (мин)
{price}          - Цена
{center_name}    - Название центра
{center_phone}   - Телефон центра
{center_address} - Адрес центра
```

---

## Known Issues & Solutions

| Проблема | Решение |
|----------|---------|
| Vite manifest error | `npm run build` или `npm run dev` |
| Telegram не отправляет | `php artisan telegram:auth` |
| Duration ошибки | Всегда кастить: `(int)$duration` |
| N+1 queries | Использовать `with(['client', 'master', 'service'])` |
| Фото не отображаются | `php artisan storage:link` |

---

---

## Telegram Notifications (Уведомления мастерам)

### Как это работает

1. **Регистрация мастера в Telegram боте:**
   - Мастер отправляет `/start` боту с контактом (+38XXXXXXXXXXX)
   - Бот сохраняет `chat_id` в `users.telegram_chat_id` через webhook
   - Используется `MasterTelegramBotNotificationService::saveMasterChatId()`

2. **Отправка уведомления при создании записи:**
   - Когда запись создана через `AppointmentController` или `ManualAppointmentController`
   - Вызывается `MasterTelegramBotNotificationService::sendMasterNotification($appointment)`
   - Проверяется наличие `telegram_chat_id` у мастера
   - **Если нет** - система автоматически пытается найти `chat_id` через `TelegramMasterChatIdResolverService`
     - Использует MadelineProto (userbot) для поиска пользователя по номеру телефона
     - Если найден - сохраняет найденный `chat_id` в БД
   - Отправляется сообщение в Telegram (Bot API)
   - Логируется результат отправки (источник: БД, webhook или резолвер)

3. **Проверить статус отправки:**
   ```bash
   tail -f storage/logs/laravel.log | grep "Master notification"
   tail -f storage/logs/laravel.log | grep "attempting to resolve"
   tail -f storage/logs/laravel.log | grep "chat_id"
   ```

### Преимущества автоматического резолвинга

- ✅ Не требует повторной авторизации мастеров в боте
- ✅ Использует стабильный MadelineProto (userbot) который уже работает для уведомлений клиентам
- ✅ Автоматически заполняет `telegram_chat_id` при первой отправке уведомления
- ✅ Полное логирование всех операций для отладки

### Архитектура

**MasterTelegramBotNotificationService** (Bot API):
- Основной сервис для отправки уведомлений о новых записях
- Использует Telegram Bot API для отправки сообщений
- Fallback: при отсутствии `chat_id` вызывает резолвер

**TelegramMasterChatIdResolverService** (MadelineProto):
- Находит `chat_id` пользователя по номеру телефона
- Использует MadelineProto (userbot) - contacts.resolvePhone()
- Автоматически сохраняет найденный `chat_id` в БД

**TelegramNotificationService** (MadelineProto):
- Отправляет уведомления клиентам о напоминаниях/обновлениях
- Более гибкий, работает через userbot

### Логирование отправки уведомлений

Все попытки отправки уведомлений мастерам логируются в таблицу `master_notification_logs`:

**Информация логируется:**
- ID записи и мастера
- Телефон мастера (нормализованный)
- Статус отправки (pending → sent / failed)
- Использованный chat_id
- Источник chat_id (database, webhook, resolver)
- Полный текст сообщения
- Ошибки если были

**Просмотр логов в админке:**
```
/admin/master-notification-logs
```

Можно фильтровать по:
- Статусу (ожидание, отправлено, ошибка)
- Мастеру
- Диапазону дат

Нажав "Просмотр" - видны полные детали отправки, включая текст сообщения и ошибки.

### Deployment

При развертывании на production:
```bash
# 1. Запустить миграцию
php artisan migrate

# 2. Мастера могут либо:
#    a) Авторизоваться в боте снова (/start + контакт) - сохранится через webhook
#    b) Система сама найдет chat_id при первой отправке уведомления о записи

# 3. Проверить логи отправок в админке
#    /admin/master-notification-logs
```

---

## Development Commands

```bash
# Запуск сервера
php artisan serve

# Запуск frontend (dev mode)
npm run dev

# Сборка frontend
npm run build

# Запуск очередей
php artisan queue:work --queue=master_notifications

# Тесты
php artisan test

# Форматирование кода
vendor/bin/pint --dirty

# Telegram авторизация
php artisan telegram:auth
```

---

## Database Queries (Частые запросы)

```php
// Активные мастера с услугами
User::where('role', 'master')
    ->where('is_active', true)
    ->with('masterServices.service')
    ->get();

// Записи на сегодня
Appointment::whereDate('appointment_date', today())
    ->where('status', 'scheduled')
    ->with(['client', 'master', 'service'])
    ->get();

// Доступные слоты мастера
// См. MasterController::generateAvailableSlots()

// Настройка сайта
Setting::get('center_name', 'Default');

// Кешированный блок
TextBlock::get('hero_title', 'Default Title');
```

---

## Testing

```bash
# Все тесты
php artisan test

# Конкретный файл
php artisan test tests/Feature/ExampleTest.php

# По имени теста
php artisan test --filter=testName
```

**Factories:** `database/factories/`
**Seeders:** `database/seeders/`

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.27
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
</laravel-boost-guidelines>
