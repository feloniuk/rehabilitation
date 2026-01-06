# ServiceCenter SaaS - Дорожная карта проекта

## Обзор проекта

**ServiceCenter** - это SaaS платформа для управления онлайн-записями и сервис-центров. Это трансформация single-tenant приложения в multi-tenant архитектуру с Stripe биллингом.

**Модель подписки:** $10/мастер/месяц (фиксированная сумма)

---

## ✅ Реализовано (Этапы 1-3)

### Фаза 1: Multi-Tenancy Фундамент

#### Миграции БД
- ✅ `tenants` - таблица организаций
- ✅ `subscriptions` - таблица подписок
- ✅ `tenant_user` - pivot таблица для связи пользователей с организациями
- ✅ `add_tenant_id_to_tables` - добавлена поддержка tenant_id ко всем основным таблицам
- ✅ `add_super_admin_to_users` - поле is_super_admin для глобальных админов

#### Модели
- ✅ `Tenant` - основная модель организации с relations
- ✅ `Subscription` - модель подписки со статусами
- ✅ `BelongsToTenant` trait - автоматическое разделение данных по tenant_id
- ✅ `User` - обновлена с tenant relations и новыми методами проверки ролей

#### Обновлены все модели
- ✅ Service, Appointment, Page, Setting, TextBlock
- ✅ NotificationTemplate, NotificationLog, MasterNotificationLog
- ✅ MasterService, ServiceFaq

### Фаза 2: Middleware и Routing

#### Middleware
- ✅ `TenantMiddleware` - резолв tenant из URL (path-based)
- ✅ `TenantRoleMiddleware` - проверка ролей в контексте tenant
- ✅ `SubscriptionActiveMiddleware` - проверка активной подписки
- ✅ `RoleMiddleware` - обновлен для поддержки super_admin

#### Routes (полная реструктуризация)
- ✅ **Platform routes** - публичные страницы платформы
  - `/` - landing page
  - `/login` - вход
  - `/register` - регистрация нового tenant
  - `/pricing` - страница с ценами
  - `/features` - описание возможностей

- ✅ **Super Admin routes** - `/super-admin/*`
  - Dashboard с статистикой
  - CRUD для tenants
  - Toggle status, impersonate

- ✅ **Tenant routes** - `/{tenant-slug}/*`
  - Публичные страницы (home, services, masters)
  - Запись на услуги
  - Admin panel (`/{tenant-slug}/admin/*`)
    - Для owner/admin: clients, masters, services, pages, settings, notifications
    - Для owner только: billing, team management

### Фаза 3: Контроллеры и Views

#### Контроллеры
- ✅ `Platform/PlatformController` - landing, pricing, features
- ✅ `Platform/AuthController` - глобальная авторизация, выбор tenant
- ✅ `Platform/TenantRegistrationController` - регистрация нового tenant
- ✅ `SuperAdmin/DashboardController` - dashboard супер-админа
- ✅ `SuperAdmin/TenantController` - управление tenants

#### Views
- ✅ `platform/landing.blade.php` - главная страница
- ✅ `platform/login.blade.php` - форма входа
- ✅ `platform/register.blade.php` - регистрация
- ✅ `platform/select-tenant.blade.php` - выбор организации
- ✅ `platform/pricing.blade.php` - страница с ценами
- ✅ `platform/features.blade.php` - описание возможностей
- ✅ `super-admin/dashboard.blade.php` - dashboard
- ✅ `tenant/admin/billing/index.blade.php` - управление подпиской
- ✅ `tenant/admin/team/index.blade.php` - управление командой

#### Seeder
- ✅ `MigrateSingleTenantToMultiTenantSeeder` - миграция существующих данных

---

## 📋 TODO (Будущие этапы)

### Этап 4: Stripe Биллинг [СЛЕДУЮЩИЙ]

**Ожидается:** Stripe подписки, управление платежами, webhooks

#### Что нужно сделать:
- [ ] Установить Laravel Cashier
- [ ] Создать миграцию для stripe_customer_id в tenants
- [ ] `BillingService` - управление подписками в Stripe
- [ ] `StripeWebhookController` - обработка webhook событий
- [ ] `TenantBillingController` - логика управления подписками
- [ ] Listener для автоматического обновления quantity при добавлении мастера
- [ ] Views для billing portal
- [ ] Ограничение функционала при неактивной подписке

**Ключевой файл:** `app/Services/BillingService.php`

---

### Этап 5: Адаптация существующих контроллеров

**Ожидается:** Обновление всех старых контроллеров для работы с tenant

#### Что нужно сделать:
- [ ] Скопировать Admin контроллеры в `Tenant/Admin/`
- [ ] Обновить все queries для фильтрации по tenant
- [ ] Обновить route model binding для tenant-aware запросов
- [ ] Обновить фильтрацию в index() методах

**Ключевые файлы:**
- `app/Http/Controllers/Tenant/Admin/AppointmentController.php`
- `app/Http/Controllers/Tenant/Admin/ClientController.php`
- `app/Http/Controllers/Tenant/Admin/MasterController.php`
- `app/Http/Controllers/Tenant/Admin/ServiceController.php`
- `app/Http/Controllers/Tenant/Admin/SettingController.php`
- `app/Http/Controllers/Tenant/Admin/NotificationController.php`

---

### Этап 6: Views (адаптация и обновление)

**Ожидается:** Обновление всех существующих views для tenant-aware routes

#### Что нужно сделать:
- [ ] Обновить все `route()` вызовы с передачей tenant параметра
- [ ] Обновить layouts для отображения информации о tenant
- [ ] Перенести admin views в `tenant/admin/`
- [ ] Убедиться что global scope работает правильно в views

**Примеры обновлений:**
```blade
{{-- Было: --}}
route('admin.appointments.index')

{{-- Стало: --}}
route('tenant.admin.appointments.index', ['tenant' => $currentTenant->slug])
```

---

### Этап 7: Telegram per-tenant

**Ожидается:** Поддержка индивидуальных Telegram ботов для каждого tenant

#### Что нужно сделать:
- [ ] Хранить telegram_bot_token в `tenants.settings`
- [ ] Обновить `TelegramBotNotificationService` для work с tenant-specific tokens
- [ ] Fallback на глобальный бот если не указан custom

**Ключевой файл:** `app/Services/MasterTelegramBotNotificationService.php`

---

### Этап 8: Миграция существующих данных

**Ожидается:** Запуск seeder и тестирование

#### Что нужно сделать:
- [ ] Запустить: `php artisan db:seed --class=MigrateSingleTenantToMultiTenantSeeder`
- [ ] Проверить что все данные перенеслись корректно
- [ ] Убедиться что существующий админ стал super_admin
- [ ] Протестировать доступ к старым функциям через новые URL

---

### Этап 9: Тестирование и документация

**Ожидается:** Полное тестирование всех flows

#### Что нужно сделать:
- [ ] Unit тесты для моделей (Tenant, Subscription, User)
- [ ] Feature тесты для tenant isolation
- [ ] Feature тесты для billing flows
- [ ] E2E тесты для критических путей
- [ ] Документация API (если использовать)

---

## 🚀 Быстрый старт

### 1. Запустить миграцию данных
```bash
php artisan db:seed --class=MigrateSingleTenantToMultiTenantSeeder
```

### 2. Запустить сервер
```bash
php artisan serve
```

### 3. Доступ

**Landing page:** http://localhost:8000/
```
- Email: admin@example.com (если есть существующий админ)
- Или зарегистрировать новый tenant
```

**Super Admin:** http://localhost:8000/super-admin
```
- Доступен для пользователя с is_super_admin = true
- Первый админ автоматически становится super_admin при миграции
```

**Tenant Admin:** http://localhost:8000/{tenant-slug}/admin
```
- Доступен для owner/admin/master этого tenant
- {tenant-slug} - slug организации, созданный при регистрации
```

---

## 📊 Архитектура

### Multi-Tenancy модель
- **Single DB** - одна база для всех tenants
- **Разделение данных** - через `tenant_id` и Global Scope
- **Path-based URLs** - `yourapp.com/{tenant-slug}/...`

### Роли пользователей
- `super_admin` - глобальный администратор платформы
- `owner` - владелец организации (платит за подписку)
- `admin` - администратор организации (не платит)
- `master` - специалист/мастер
- `client` - клиент

### Ключевые таблицы
```
tenants - организации
  ├─ id, name, slug, owner_id
  ├─ stripe_customer_id, settings
  └─ is_active, trial_ends_at

subscriptions - подписки
  ├─ tenant_id (FK)
  ├─ stripe_subscription_id, stripe_price_id
  ├─ status, quantity (кол-во мастеров)
  └─ trial_ends_at, current_period_end

tenant_user - связь пользователей с организациями
  ├─ tenant_id (FK)
  ├─ user_id (FK)
  └─ role (owner, admin, master, client)

users - пользователи
  ├─ role (legacy field для backward compatibility)
  ├─ is_super_admin
  └─ ... (другие поля)

[все остальные таблицы имеют tenant_id]
```

---

## 🔐 Безопасность

### Global Scope
Все модели с `BelongsToTenant` trait автоматически фильтруются по текущему tenant:
```php
$appointments = Appointment::all(); // Вернёт только записи текущего tenant
```

### Middleware для tenant context
```php
// TenantMiddleware - резолв tenant из URL
Route::prefix('{tenant}')->middleware('tenant')->group(...);

// TenantRoleMiddleware - проверка ролей
Route::middleware('tenant.role:owner,admin')->group(...);

// SubscriptionActiveMiddleware - проверка подписки
Route::middleware('subscription.active')->group(...);
```

---

## 📝 Файлы для отслеживания

**Критические для реализации:**
- `app/Models/Tenant.php` - основная модель
- `app/Models/User.php` - обновлённая с tenant relations
- `routes/web.php` - реструктуризированные routes
- `bootstrap/app.php` - регистрация middleware

**Для тестирования:**
- `database/seeders/MigrateSingleTenantToMultiTenantSeeder.php`
- `resources/views/platform/` - views платформы
- `resources/views/super-admin/` - views супер-админа

---

## 🔄 Workflow регистрации нового tenant

1. Пользователь заходит на `/register`
2. Заполняет данные (компания, имя, email, пароль, телефон)
3. Создаётся новый User с ролью 'admin'
4. Создаётся новый Tenant с этим пользователем как owner
5. User привязывается к Tenant через pivot таблицу с ролью 'owner'
6. Tenant получает trial период на 14 дней
7. Автоматический редирект на `/admin` dashboard

---

## 🔄 Workflow миграции существующих данных

1. Запуск seeder
2. Поиск первого админа в системе
3. Создание Tenant с именем из Setting 'center_name'
4. Привязка всех пользователей к Tenant с их текущими ролями
5. Установка первого админа как super_admin
6. Обновление tenant_id у всех существующих записей
7. Готово к использованию через новые URLs

---

## 📞 Контакты для поддержки

При возникновении проблем проверьте:
1. Миграции выполнены: `php artisan migrate`
2. Seeder запущен: `php artisan db:seed --class=MigrateSingleTenantToMultiTenantSeeder`
3. `app('currentTenant')` установлен в контексте (через TenantMiddleware)
4. Correct URL format: `http://localhost:8000/{tenant-slug}/admin`

---

**Версия документа:** v1.0
**Дата обновления:** 2026-01-06
**Статус:** В разработке (Этап 3 завершён)
