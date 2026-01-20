# Implementation Phases - Detailed Breakdown

## Детальний план реалізації по фазах

---

## PHASE 1: CORE ENHANCEMENT

### Duration: 4-6 тижнів

---

### 1.1 Програма лояльності

#### Моделі та міграції

```bash
php artisan make:model LoyaltyProgram -m
php artisan make:model ClientBonus -m
php artisan make:model BonusTransaction -m
```

#### Структура LoyaltyProgram

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | PK |
| tenant_id | bigint | FK |
| name | string | Назва програми |
| type | enum | cashback/points/discount |
| earn_rate | decimal(5,2) | % нарахування |
| redeem_rate | decimal(5,2) | Курс списання |
| min_redeem_amount | decimal | Мінімум для списання |
| is_active | boolean | Активність |

#### API Endpoints

```
POST   /api/tenant/{tenant}/loyalty/programs         - Створити програму
GET    /api/tenant/{tenant}/loyalty/programs         - Список програм
PUT    /api/tenant/{tenant}/loyalty/programs/{id}    - Оновити
DELETE /api/tenant/{tenant}/loyalty/programs/{id}    - Видалити

GET    /api/tenant/{tenant}/clients/{id}/bonuses     - Бонуси клієнта
POST   /api/tenant/{tenant}/clients/{id}/bonuses/earn    - Нарахувати
POST   /api/tenant/{tenant}/clients/{id}/bonuses/redeem  - Списати
```

#### Бізнес-логіка

```php
namespace App\Services;

class LoyaltyService
{
    public function earnBonuses(Appointment $appointment): void
    {
        $program = $appointment->tenant->loyaltyProgram;
        if (!$program || !$program->is_active) return;

        $amount = $appointment->price * ($program->earn_rate / 100);

        $clientBonus = ClientBonus::firstOrCreate([
            'tenant_id' => $appointment->tenant_id,
            'client_id' => $appointment->client_id,
            'program_id' => $program->id,
        ]);

        $clientBonus->increment('balance', $amount);
        $clientBonus->increment('total_earned', $amount);

        BonusTransaction::create([
            'tenant_id' => $appointment->tenant_id,
            'client_bonus_id' => $clientBonus->id,
            'appointment_id' => $appointment->id,
            'type' => 'earn',
            'amount' => $amount,
            'description' => "Нарахування за візит {$appointment->appointment_date->format('d.m.Y')}",
        ]);
    }

    public function redeemBonuses(
        ClientBonus $clientBonus,
        float $amount,
        ?Appointment $appointment = null
    ): bool {
        if ($clientBonus->balance < $amount) {
            throw new InsufficientBonusException();
        }

        $program = $clientBonus->program;
        if ($program->min_redeem_amount && $amount < $program->min_redeem_amount) {
            throw new MinimumRedeemException($program->min_redeem_amount);
        }

        $clientBonus->decrement('balance', $amount);
        $clientBonus->increment('total_spent', $amount);

        BonusTransaction::create([
            'tenant_id' => $clientBonus->tenant_id,
            'client_bonus_id' => $clientBonus->id,
            'appointment_id' => $appointment?->id,
            'type' => 'redeem',
            'amount' => -$amount,
            'description' => 'Списання бонусів',
        ]);

        return true;
    }
}
```

#### UI Components (Admin)

1. **Сторінка налаштування програми лояльності**
   - Форма створення/редагування
   - Тип програми (dropdown)
   - Коефіцієнти нарахування/списання
   - Toggle активності

2. **Картка бонусів клієнта**
   - Поточний баланс
   - Історія транзакцій
   - Ручне нарахування/списання

3. **Віджет бонусів при створенні запису**
   - Показ балансу клієнта
   - Опція списання бонусів

---

### 1.2 Відгуки та рейтинги

#### Модель Review

```bash
php artisan make:model Review -m
```

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | PK |
| tenant_id | bigint | FK |
| appointment_id | bigint | FK, unique |
| client_id | bigint | FK |
| master_id | bigint | FK |
| service_id | bigint | FK |
| rating | tinyint | 1-5 |
| comment | text | Nullable |
| is_visible | boolean | Модерація |
| reply | text | Відповідь |
| replied_at | timestamp | |

#### Flow відгуків

```
1. Запис завершено (status = completed)
         ↓
2. Через 2 години - Telegram повідомлення
   "Як вам візит? Оцініть від 1 до 5"
         ↓
3. Клієнт ставить оцінку (inline кнопки)
         ↓
4. Бот: "Бажаєте залишити коментар?"
         ↓
5. Клієнт пише коментар (або /skip)
         ↓
6. Відгук збережено
         ↓
7. Перерахунок рейтингу мастера
```

#### Калькуляція рейтингу

```php
// Observer на Review

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->recalculateMasterRating($review->master_id);
    }

    public function updated(Review $review): void
    {
        $this->recalculateMasterRating($review->master_id);
    }

    protected function recalculateMasterRating(int $masterId): void
    {
        $avgRating = Review::where('master_id', $masterId)
            ->where('is_visible', true)
            ->avg('rating');

        User::where('id', $masterId)
            ->update(['rating' => round($avgRating, 2)]);
    }
}
```

#### UI Components

1. **Сторінка майстра (public)**
   - Середній рейтинг (зірки)
   - Кількість відгуків
   - Список останніх відгуків
   - Кнопка "Показати всі"

2. **Модерація відгуків (admin)**
   - Таблиця відгуків
   - Фільтр: новий/схвалено/приховано
   - Відповідь на відгук
   - Hide/Show toggle

---

### 1.3 Фінансовий модуль

#### Моделі

```bash
php artisan make:model FinancialTransaction -m
php artisan make:model ExpenseCategory -m
```

#### FinancialTransaction

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | PK |
| tenant_id | bigint | FK |
| appointment_id | bigint | FK, nullable |
| type | enum | income/expense/refund |
| category_id | bigint | FK, nullable |
| amount | decimal(10,2) | |
| payment_method | enum | cash/card/online/bonus |
| description | string | |
| created_by | bigint | FK to users |

#### Базові звіти

```php
class FinancialReportService
{
    public function getDailyReport(Tenant $tenant, Carbon $date): array
    {
        return [
            'income' => FinancialTransaction::forTenant($tenant)
                ->whereDate('created_at', $date)
                ->where('type', 'income')
                ->sum('amount'),

            'expenses' => FinancialTransaction::forTenant($tenant)
                ->whereDate('created_at', $date)
                ->where('type', 'expense')
                ->sum('amount'),

            'appointments' => Appointment::forTenant($tenant)
                ->whereDate('appointment_date', $date)
                ->count(),

            'completed' => Appointment::forTenant($tenant)
                ->whereDate('appointment_date', $date)
                ->where('status', 'completed')
                ->count(),
        ];
    }

    public function getMonthlyReport(Tenant $tenant, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return [
            'total_income' => FinancialTransaction::forTenant($tenant)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'income')
                ->sum('amount'),

            'total_expenses' => FinancialTransaction::forTenant($tenant)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'expense')
                ->sum('amount'),

            'by_category' => FinancialTransaction::forTenant($tenant)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('category_id, type, SUM(amount) as total')
                ->groupBy('category_id', 'type')
                ->with('category')
                ->get(),

            'by_payment_method' => FinancialTransaction::forTenant($tenant)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('type', 'income')
                ->selectRaw('payment_method, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get(),

            'daily' => FinancialTransaction::forTenant($tenant)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, type, SUM(amount) as total')
                ->groupBy('date', 'type')
                ->orderBy('date')
                ->get(),
        ];
    }
}
```

#### UI Components

1. **Каса (головна)**
   - Баланс за сьогодні
   - Швидке додавання: прихід/витрата
   - Останні операції

2. **Форма додавання операції**
   - Тип (income/expense)
   - Категорія
   - Сума
   - Спосіб оплати
   - Опис
   - Дата

3. **Звіти**
   - По днях (графік)
   - По категоріях (pie chart)
   - По способах оплати
   - Експорт Excel/PDF

---

### 1.4 Зарплата мастерів

#### Моделі

```bash
php artisan make:model SalarySettings -m
php artisan make:model SalaryCalculation -m
```

#### SalarySettings

| Field | Type | Description |
|-------|------|-------------|
| tenant_id | bigint | FK |
| master_id | bigint | FK |
| calculation_type | enum | fixed/percentage/mixed |
| fixed_amount | decimal | Фіксована частина |
| percentage | decimal | Відсоток від послуг |
| min_guarantee | decimal | Мінімальна гарантія |

#### SalaryCalculation

| Field | Type | Description |
|-------|------|-------------|
| tenant_id | bigint | FK |
| master_id | bigint | FK |
| period_start | date | |
| period_end | date | |
| total_services | decimal | Сума послуг |
| total_bonus | decimal | Бонуси |
| total_deductions | decimal | Утримання |
| calculated_amount | decimal | Підсумок |
| paid_amount | decimal | Виплачено |
| status | enum | draft/approved/paid |
| paid_at | timestamp | |

#### Калькулятор зарплати

```php
class SalaryCalculatorService
{
    public function calculate(
        Tenant $tenant,
        User $master,
        Carbon $periodStart,
        Carbon $periodEnd
    ): SalaryCalculation {
        $settings = SalarySettings::where([
            'tenant_id' => $tenant->id,
            'master_id' => $master->id,
        ])->firstOrFail();

        // Сума завершених послуг
        $totalServices = Appointment::where([
            'tenant_id' => $tenant->id,
            'master_id' => $master->id,
            'status' => 'completed',
        ])
            ->whereBetween('appointment_date', [$periodStart, $periodEnd])
            ->sum('price');

        // Розрахунок
        $calculated = match($settings->calculation_type) {
            'fixed' => $settings->fixed_amount,
            'percentage' => $totalServices * ($settings->percentage / 100),
            'mixed' => $settings->fixed_amount + ($totalServices * ($settings->percentage / 100)),
        };

        // Мінімальна гарантія
        if ($settings->min_guarantee && $calculated < $settings->min_guarantee) {
            $calculated = $settings->min_guarantee;
        }

        return SalaryCalculation::create([
            'tenant_id' => $tenant->id,
            'master_id' => $master->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_services' => $totalServices,
            'calculated_amount' => $calculated,
            'status' => 'draft',
        ]);
    }
}
```

#### UI Components

1. **Налаштування зарплати мастера**
   - Тип розрахунку
   - Відсоток/фіксована сума
   - Мінімальна гарантія

2. **Розрахунок зарплат**
   - Вибір періоду
   - Таблиця мастерів
   - Автоматичний розрахунок
   - Approve/Paid статуси

3. **Історія виплат**
   - Архів по мастерах
   - Експорт

---

## PHASE 2: TELEGRAM BOOKING BOT

### Duration: 3-4 тижні

---

### 2.1 Інфраструктура

#### Файлова структура

```
app/
├── Telegram/
│   ├── Contracts/
│   │   ├── StateHandlerInterface.php
│   │   └── KeyboardBuilderInterface.php
│   ├── Handlers/
│   │   ├── BaseHandler.php
│   │   ├── MainMenuHandler.php
│   │   ├── BookingHandler.php
│   │   ├── AppointmentsHandler.php
│   │   └── ReviewHandler.php
│   ├── Keyboards/
│   │   ├── InlineKeyboardBuilder.php
│   │   └── ReplyKeyboardBuilder.php
│   ├── Messages/
│   │   └── MessageTemplates.php
│   ├── States/
│   │   └── ConversationState.php
│   └── Services/
│       ├── TelegramBotService.php
│       └── ConversationManager.php
├── Http/
│   └── Controllers/
│       └── Telegram/
│           ├── WebhookController.php
│           └── MiniAppController.php
└── Jobs/
    └── SendTelegramMessage.php
```

#### ConversationManager

```php
namespace App\Telegram\Services;

class ConversationManager
{
    protected TelegramConversation $conversation;
    protected array $handlers;

    public function __construct()
    {
        $this->handlers = [
            'main_menu' => MainMenuHandler::class,
            'booking.*' => BookingHandler::class,
            'appointments.*' => AppointmentsHandler::class,
            'review.*' => ReviewHandler::class,
        ];
    }

    public function process(TelegramConversation $conversation, array $update): array
    {
        $this->conversation = $conversation;
        $state = $conversation->state;

        $handler = $this->resolveHandler($state);

        return $handler->handle($conversation, $update);
    }

    protected function resolveHandler(string $state): BaseHandler
    {
        foreach ($this->handlers as $pattern => $handlerClass) {
            if (fnmatch($pattern, $state)) {
                return new $handlerClass();
            }
        }

        return new MainMenuHandler();
    }
}
```

---

### 2.2 Booking Flow Implementation

#### BookingHandler

```php
namespace App\Telegram\Handlers;

class BookingHandler extends BaseHandler
{
    public function handle(TelegramConversation $conversation, array $update): array
    {
        $state = ConversationState::from($conversation->state);
        $callbackData = $update['callback_query']['data'] ?? null;
        $text = $update['message']['text'] ?? null;
        $contact = $update['message']['contact'] ?? null;

        return match($state) {
            ConversationState::BOOKING_SELECT_SERVICE => $this->handleSelectService($callbackData),
            ConversationState::BOOKING_SELECT_MASTER => $this->handleSelectMaster($callbackData),
            ConversationState::BOOKING_SELECT_DATE => $this->handleSelectDate($callbackData),
            ConversationState::BOOKING_SELECT_TIME => $this->handleSelectTime($callbackData),
            ConversationState::BOOKING_CONFIRM => $this->handleConfirm($callbackData),
            ConversationState::BOOKING_GET_CONTACT => $this->handleGetContact($contact),
            default => $this->showCurrentState(),
        };
    }

    protected function handleSelectService(?string $callback): array
    {
        if ($callback === 'back') {
            return $this->transitionTo(ConversationState::MAIN_MENU);
        }

        if (str_starts_with($callback, 'service_')) {
            $serviceId = (int) str_replace('service_', '', $callback);
            $this->setContext('service_id', $serviceId);

            $masters = $this->getMastersForService($serviceId);

            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_MASTER,
                MessageTemplates::selectMaster(),
                InlineKeyboardBuilder::masters($masters)
            );
        }

        return $this->showCurrentState();
    }

    protected function handleSelectMaster(?string $callback): array
    {
        if ($callback === 'back') {
            return $this->transitionTo(ConversationState::BOOKING_SELECT_SERVICE);
        }

        if ($callback === 'any_master') {
            // Знаходимо найближчий доступний слот серед всіх мастерів
            $serviceId = $this->getContext('service_id');
            $slot = $this->findNearestSlot($serviceId);

            if (!$slot) {
                return $this->sendMessage('На жаль, немає доступних слотів на найближчий час.');
            }

            $this->setContext('master_id', $slot['master_id']);
            $this->setContext('date', $slot['date']);
            $this->setContext('time', $slot['time']);

            return $this->transitionTo(
                ConversationState::BOOKING_CONFIRM,
                $this->buildConfirmMessage()
            );
        }

        if (str_starts_with($callback, 'master_')) {
            $masterId = (int) str_replace('master_', '', $callback);
            $this->setContext('master_id', $masterId);

            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_DATE,
                MessageTemplates::selectDate(),
                InlineKeyboardBuilder::dates(now(), $masterId)
            );
        }

        return $this->showCurrentState();
    }

    protected function handleSelectDate(?string $callback): array
    {
        if ($callback === 'back') {
            return $this->transitionTo(ConversationState::BOOKING_SELECT_MASTER);
        }

        if ($callback === 'next_week') {
            $weekStart = Carbon::parse($this->getContext('week_start', now()))->addWeek();
            $this->setContext('week_start', $weekStart->toDateString());

            return $this->showCurrentState();
        }

        if ($callback === 'prev_week') {
            $weekStart = Carbon::parse($this->getContext('week_start', now()))->subWeek();
            if ($weekStart->isPast()) {
                $weekStart = now()->startOfWeek();
            }
            $this->setContext('week_start', $weekStart->toDateString());

            return $this->showCurrentState();
        }

        if (str_starts_with($callback, 'date_')) {
            $date = str_replace('date_', '', $callback);
            $this->setContext('date', $date);

            $slots = $this->getAvailableSlots($date);

            if (empty($slots)) {
                return $this->sendMessage('На цей день немає вільних слотів. Оберіть інший день.');
            }

            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_TIME,
                MessageTemplates::selectTime(),
                InlineKeyboardBuilder::times($slots)
            );
        }

        return $this->showCurrentState();
    }

    protected function handleSelectTime(?string $callback): array
    {
        if ($callback === 'back') {
            return $this->transitionTo(ConversationState::BOOKING_SELECT_DATE);
        }

        if (str_starts_with($callback, 'time_')) {
            $time = str_replace('time_', '', $callback);
            $this->setContext('time', $time);

            return $this->transitionTo(
                ConversationState::BOOKING_CONFIRM,
                $this->buildConfirmMessage(),
                InlineKeyboardBuilder::confirm()
            );
        }

        return $this->showCurrentState();
    }

    protected function handleConfirm(?string $callback): array
    {
        if ($callback === 'cancel' || $callback === 'back') {
            $this->clearContext();
            return $this->transitionTo(ConversationState::MAIN_MENU);
        }

        if ($callback === 'confirm') {
            // Перевіряємо чи є клієнт
            $user = $this->findClientByChatId($this->conversation->chat_id);

            if (!$user || !$user->phone) {
                return $this->transitionTo(
                    ConversationState::BOOKING_GET_CONTACT,
                    MessageTemplates::requestContact(),
                    ReplyKeyboardBuilder::contactRequest()
                );
            }

            return $this->createBooking($user);
        }

        return $this->showCurrentState();
    }

    protected function handleGetContact(?array $contact): array
    {
        if (!$contact) {
            return $this->showCurrentState();
        }

        $user = $this->createOrUpdateClient([
            'phone' => $contact['phone_number'],
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
            'telegram_chat_id' => $this->conversation->chat_id,
        ]);

        return $this->createBooking($user);
    }

    protected function createBooking(User $client): array
    {
        $serviceId = $this->getContext('service_id');
        $masterId = $this->getContext('master_id');
        $date = $this->getContext('date');
        $time = $this->getContext('time');

        // Перевіряємо чи слот ще вільний
        $exists = Appointment::where([
            'tenant_id' => $this->conversation->tenant_id,
            'master_id' => $masterId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status' => 'scheduled',
        ])->exists();

        if ($exists) {
            $this->clearContext();
            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_TIME,
                "На жаль, цей час щойно зайняли. Оберіть інший:",
                InlineKeyboardBuilder::times($this->getAvailableSlots($date))
            );
        }

        $masterService = MasterService::where([
            'master_id' => $masterId,
            'service_id' => $serviceId,
        ])->first();

        $appointment = Appointment::create([
            'tenant_id' => $this->conversation->tenant_id,
            'client_id' => $client->id,
            'master_id' => $masterId,
            'service_id' => $serviceId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'duration' => $masterService->duration,
            'price' => $masterService->price,
            'status' => 'scheduled',
        ]);

        // Повідомлення мастеру
        dispatch(new SendMasterTelegramNotification($appointment));

        $this->clearContext();

        return [
            'method' => 'sendMessage',
            'text' => MessageTemplates::bookingSuccess($appointment),
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
        ];
    }

    protected function getAvailableSlots(string $date): array
    {
        $masterId = $this->getContext('master_id');
        $serviceId = $this->getContext('service_id');

        $master = User::find($masterId);
        $service = MasterService::where([
            'master_id' => $masterId,
            'service_id' => $serviceId,
        ])->first();

        $dayName = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $hours = $master->getWorkingHours($dayName);

        if (!$hours) return [];

        $slots = [];
        $start = Carbon::parse($date . ' ' . $hours['start']);
        $end = Carbon::parse($date . ' ' . $hours['end']);

        // Отримуємо зайняті слоти
        $bookedSlots = Appointment::where([
            'tenant_id' => $this->conversation->tenant_id,
            'master_id' => $masterId,
            'appointment_date' => $date,
            'status' => 'scheduled',
        ])->get()->map(function ($a) {
            return [
                'start' => Carbon::parse($a->appointment_time),
                'end' => Carbon::parse($a->appointment_time)->addMinutes($a->duration),
            ];
        });

        while ($start->copy()->addMinutes($service->duration)->lte($end)) {
            $slotEnd = $start->copy()->addMinutes($service->duration);

            // Перевіряємо чи не перетинається з існуючими
            $isAvailable = true;
            foreach ($bookedSlots as $booked) {
                if ($start->lt($booked['end']) && $slotEnd->gt($booked['start'])) {
                    $isAvailable = false;
                    break;
                }
            }

            // Перевіряємо чи не в минулому
            if ($start->isPast()) {
                $isAvailable = false;
            }

            if ($isAvailable) {
                $slots[] = $start->format('H:i');
            }

            $start->addMinutes(30); // Крок 30 хвилин
        }

        return $slots;
    }
}
```

---

## PHASE 3: PAYMENTS & ANALYTICS

### Duration: 4-5 тижнів

---

### 3.1 Онлайн-оплата

#### LiqPay Integration

```php
namespace App\Services\Payment;

use LiqPay;

class LiqPayService
{
    protected LiqPay $liqpay;

    public function __construct()
    {
        $this->liqpay = new LiqPay(
            config('services.liqpay.public_key'),
            config('services.liqpay.private_key')
        );
    }

    public function createPayment(Appointment $appointment): array
    {
        $orderId = 'apt_' . $appointment->id . '_' . time();

        $params = [
            'version' => '3',
            'action' => 'pay',
            'amount' => $appointment->price,
            'currency' => 'UAH',
            'description' => "Оплата запису: {$appointment->service->name}",
            'order_id' => $orderId,
            'result_url' => route('tenant.payment.result', [
                'tenant' => $appointment->tenant->slug,
                'appointment' => $appointment->id,
            ]),
            'server_url' => route('webhook.liqpay'),
        ];

        $data = base64_encode(json_encode($params));
        $signature = $this->liqpay->cnb_signature($params);

        // Зберігаємо платіж
        Payment::create([
            'tenant_id' => $appointment->tenant_id,
            'appointment_id' => $appointment->id,
            'amount' => $appointment->price,
            'provider' => 'liqpay',
            'provider_payment_id' => $orderId,
            'status' => 'pending',
        ]);

        return [
            'data' => $data,
            'signature' => $signature,
            'checkout_url' => 'https://www.liqpay.ua/api/3/checkout',
        ];
    }

    public function handleCallback(array $data): void
    {
        $decodedData = json_decode(base64_decode($data['data']), true);
        $orderId = $decodedData['order_id'];

        $payment = Payment::where('provider_payment_id', $orderId)->first();

        if (!$payment) {
            Log::warning('LiqPay callback: payment not found', ['order_id' => $orderId]);
            return;
        }

        $status = match($decodedData['status']) {
            'success', 'sandbox' => 'completed',
            'failure' => 'failed',
            'processing' => 'processing',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'completed' ? now() : null,
            'metadata' => $decodedData,
        ]);

        if ($status === 'completed') {
            // Створюємо фінансову операцію
            FinancialTransaction::create([
                'tenant_id' => $payment->tenant_id,
                'appointment_id' => $payment->appointment_id,
                'type' => 'income',
                'amount' => $payment->amount,
                'payment_method' => 'online',
                'description' => 'Онлайн-оплата LiqPay',
                'created_by' => $payment->appointment->client_id,
            ]);

            // Нарахування бонусів
            app(LoyaltyService::class)->earnBonuses($payment->appointment);
        }
    }
}
```

#### Monobank Integration

```php
namespace App\Services\Payment;

class MonobankService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.monobank.token');
    }

    public function createInvoice(Appointment $appointment): array
    {
        $response = Http::withHeaders([
            'X-Token' => $this->token,
        ])->post('https://api.monobank.ua/api/merchant/invoice/create', [
            'amount' => (int) ($appointment->price * 100), // в копійках
            'ccy' => 980, // UAH
            'merchantPaymInfo' => [
                'reference' => 'apt_' . $appointment->id,
                'destination' => "Оплата запису: {$appointment->service->name}",
            ],
            'redirectUrl' => route('tenant.payment.result', [
                'tenant' => $appointment->tenant->slug,
                'appointment' => $appointment->id,
            ]),
            'webHookUrl' => route('webhook.monobank'),
        ]);

        $data = $response->json();

        Payment::create([
            'tenant_id' => $appointment->tenant_id,
            'appointment_id' => $appointment->id,
            'amount' => $appointment->price,
            'provider' => 'monobank',
            'provider_payment_id' => $data['invoiceId'],
            'status' => 'pending',
        ]);

        return [
            'invoice_id' => $data['invoiceId'],
            'page_url' => $data['pageUrl'],
        ];
    }

    public function handleWebhook(array $data): void
    {
        $invoiceId = $data['invoiceId'];

        $payment = Payment::where('provider_payment_id', $invoiceId)->first();

        if (!$payment) {
            Log::warning('Monobank webhook: payment not found', ['invoice_id' => $invoiceId]);
            return;
        }

        $status = match($data['status']) {
            'success' => 'completed',
            'failure' => 'failed',
            'processing' => 'processing',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'completed' ? now() : null,
            'metadata' => $data,
        ]);

        if ($status === 'completed') {
            FinancialTransaction::create([
                'tenant_id' => $payment->tenant_id,
                'appointment_id' => $payment->appointment_id,
                'type' => 'income',
                'amount' => $payment->amount,
                'payment_method' => 'online',
                'description' => 'Онлайн-оплата Monobank',
                'created_by' => $payment->appointment->client_id,
            ]);

            app(LoyaltyService::class)->earnBonuses($payment->appointment);
        }
    }
}
```

---

### 3.2 Розширена аналітика

#### Analytics Aggregation Job

```php
namespace App\Jobs;

class AggregateAnalytics implements ShouldQueue
{
    public function handle(): void
    {
        $yesterday = now()->subDay()->toDateString();

        Tenant::active()->each(function ($tenant) use ($yesterday) {
            $this->aggregateForTenant($tenant, $yesterday);
        });
    }

    protected function aggregateForTenant(Tenant $tenant, string $date): void
    {
        $appointments = Appointment::where('tenant_id', $tenant->id)
            ->whereDate('appointment_date', $date)
            ->get();

        $newClients = User::whereHas('tenants', fn($q) => $q->where('tenant_id', $tenant->id))
            ->where('role', 'client')
            ->whereDate('created_at', $date)
            ->count();

        $completedAppointments = $appointments->where('status', 'completed');

        $revenue = $completedAppointments->sum('price');
        $averageCheck = $completedAppointments->count() > 0
            ? $revenue / $completedAppointments->count()
            : 0;

        // Найпопулярніша послуга
        $mostPopularService = $completedAppointments
            ->groupBy('service_id')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->keys()
            ->first();

        // Найзавантаженіший мастер
        $mostBusyMaster = $completedAppointments
            ->groupBy('master_id')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->keys()
            ->first();

        AnalyticsDaily::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'date' => $date,
            ],
            [
                'appointments_total' => $appointments->count(),
                'appointments_completed' => $completedAppointments->count(),
                'appointments_cancelled' => $appointments->where('status', 'cancelled')->count(),
                'new_clients' => $newClients,
                'returning_clients' => $completedAppointments->unique('client_id')->count() - $newClients,
                'revenue' => $revenue,
                'average_check' => $averageCheck,
                'most_popular_service_id' => $mostPopularService,
                'most_busy_master_id' => $mostBusyMaster,
            ]
        );
    }
}
```

#### Dashboard Charts Data

```php
namespace App\Services;

class AnalyticsService
{
    public function getDashboardData(Tenant $tenant, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        $dailyData = AnalyticsDaily::where('tenant_id', $tenant->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        return [
            'summary' => [
                'total_revenue' => $dailyData->sum('revenue'),
                'total_appointments' => $dailyData->sum('appointments_completed'),
                'new_clients' => $dailyData->sum('new_clients'),
                'average_check' => $dailyData->avg('average_check'),
                'completion_rate' => $this->calculateCompletionRate($dailyData),
            ],
            'charts' => [
                'revenue' => $dailyData->map(fn($d) => [
                    'date' => $d->date->format('d.m'),
                    'value' => $d->revenue,
                ]),
                'appointments' => $dailyData->map(fn($d) => [
                    'date' => $d->date->format('d.m'),
                    'completed' => $d->appointments_completed,
                    'cancelled' => $d->appointments_cancelled,
                ]),
                'clients' => $dailyData->map(fn($d) => [
                    'date' => $d->date->format('d.m'),
                    'new' => $d->new_clients,
                    'returning' => $d->returning_clients,
                ]),
            ],
            'top_services' => $this->getTopServices($tenant, $startDate),
            'top_masters' => $this->getTopMasters($tenant, $startDate),
        ];
    }

    protected function getTopServices(Tenant $tenant, Carbon $startDate): Collection
    {
        return Appointment::where('tenant_id', $tenant->id)
            ->where('appointment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->selectRaw('service_id, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(5)
            ->with('service')
            ->get();
    }

    protected function getTopMasters(Tenant $tenant, Carbon $startDate): Collection
    {
        return Appointment::where('tenant_id', $tenant->id)
            ->where('appointment_date', '>=', $startDate)
            ->where('status', 'completed')
            ->selectRaw('master_id, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('master_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('master')
            ->get();
    }
}
```

---

## PHASE 4: TELEGRAM MINI APP

### Duration: 4-5 тижнів

---

### 4.1 Frontend Stack

```
Vue.js 3 + Vite + TypeScript
Tailwind CSS
Telegram Web App SDK
```

#### Project Structure

```
resources/js/telegram-mini-app/
├── src/
│   ├── main.ts
│   ├── App.vue
│   ├── router/
│   │   └── index.ts
│   ├── stores/
│   │   ├── auth.ts
│   │   ├── booking.ts
│   │   └── profile.ts
│   ├── api/
│   │   └── client.ts
│   ├── components/
│   │   ├── ServiceCard.vue
│   │   ├── MasterCard.vue
│   │   ├── DatePicker.vue
│   │   ├── TimePicker.vue
│   │   └── AppointmentCard.vue
│   ├── views/
│   │   ├── HomeView.vue
│   │   ├── BookingView.vue
│   │   ├── AppointmentsView.vue
│   │   ├── ProfileView.vue
│   │   └── BonusesView.vue
│   └── utils/
│       └── telegram.ts
└── vite.config.ts
```

#### Telegram Web App Integration

```typescript
// src/utils/telegram.ts

declare global {
  interface Window {
    Telegram: {
      WebApp: TelegramWebApp;
    };
  }
}

interface TelegramWebApp {
  initData: string;
  initDataUnsafe: {
    user?: {
      id: number;
      first_name: string;
      last_name?: string;
      username?: string;
    };
  };
  ready(): void;
  close(): void;
  expand(): void;
  MainButton: {
    text: string;
    show(): void;
    hide(): void;
    onClick(callback: () => void): void;
  };
  BackButton: {
    show(): void;
    hide(): void;
    onClick(callback: () => void): void;
  };
  HapticFeedback: {
    impactOccurred(style: 'light' | 'medium' | 'heavy'): void;
    notificationOccurred(type: 'error' | 'success' | 'warning'): void;
  };
  themeParams: {
    bg_color?: string;
    text_color?: string;
    hint_color?: string;
    button_color?: string;
    button_text_color?: string;
  };
}

export const tg = window.Telegram?.WebApp;

export function initTelegram(): void {
  if (tg) {
    tg.ready();
    tg.expand();
  }
}

export function closeMiniApp(): void {
  tg?.close();
}

export function hapticFeedback(type: 'success' | 'error' | 'warning' | 'light'): void {
  if (type === 'success' || type === 'error' || type === 'warning') {
    tg?.HapticFeedback.notificationOccurred(type);
  } else {
    tg?.HapticFeedback.impactOccurred('light');
  }
}
```

#### Auth Store

```typescript
// src/stores/auth.ts

import { defineStore } from 'pinia';
import { ref } from 'vue';
import { tg } from '@/utils/telegram';
import api from '@/api/client';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const isAuthenticated = ref(false);
  const isLoading = ref(true);

  async function init() {
    isLoading.value = true;

    try {
      if (!tg?.initData) {
        throw new Error('No init data from Telegram');
      }

      const response = await api.post('/auth/telegram', {
        init_data: tg.initData,
      });

      if (response.data.valid) {
        user.value = response.data.user;
        isAuthenticated.value = true;
      }
    } catch (error) {
      console.error('Auth error:', error);
    } finally {
      isLoading.value = false;
    }
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    init,
  };
});
```

#### Booking View

```vue
<!-- src/views/BookingView.vue -->

<template>
  <div class="booking-container">
    <h1 class="text-xl font-bold mb-4">Новий запис</h1>

    <!-- Step 1: Service -->
    <section v-if="step === 1" class="step">
      <h2 class="text-lg mb-3">Оберіть послугу</h2>
      <ServiceCard
        v-for="service in services"
        :key="service.id"
        :service="service"
        :selected="selectedService?.id === service.id"
        @click="selectService(service)"
      />
    </section>

    <!-- Step 2: Master -->
    <section v-else-if="step === 2" class="step">
      <h2 class="text-lg mb-3">Оберіть майстра</h2>
      <button
        class="w-full p-3 bg-gray-100 rounded-lg mb-2"
        @click="selectAnyMaster"
      >
        🎲 Будь-який майстер
      </button>
      <MasterCard
        v-for="master in masters"
        :key="master.id"
        :master="master"
        :selected="selectedMaster?.id === master.id"
        @click="selectMaster(master)"
      />
    </section>

    <!-- Step 3: Date -->
    <section v-else-if="step === 3" class="step">
      <h2 class="text-lg mb-3">Оберіть дату</h2>
      <DatePicker
        :master-id="selectedMaster.id"
        :selected-date="selectedDate"
        @select="selectDate"
      />
    </section>

    <!-- Step 4: Time -->
    <section v-else-if="step === 4" class="step">
      <h2 class="text-lg mb-3">Оберіть час</h2>
      <TimePicker
        :slots="availableSlots"
        :selected-time="selectedTime"
        @select="selectTime"
      />
    </section>

    <!-- Step 5: Confirm -->
    <section v-else-if="step === 5" class="step">
      <h2 class="text-lg mb-3">Підтвердження</h2>
      <div class="bg-gray-50 rounded-lg p-4">
        <p><strong>Послуга:</strong> {{ selectedService.name }}</p>
        <p><strong>Майстер:</strong> {{ selectedMaster.name }}</p>
        <p><strong>Дата:</strong> {{ formatDate(selectedDate) }}</p>
        <p><strong>Час:</strong> {{ selectedTime }}</p>
        <p><strong>Вартість:</strong> {{ selectedPrice }} грн</p>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '@/stores/booking';
import { hapticFeedback, tg } from '@/utils/telegram';
import ServiceCard from '@/components/ServiceCard.vue';
import MasterCard from '@/components/MasterCard.vue';
import DatePicker from '@/components/DatePicker.vue';
import TimePicker from '@/components/TimePicker.vue';

const router = useRouter();
const bookingStore = useBookingStore();

const step = ref(1);
const selectedService = ref(null);
const selectedMaster = ref(null);
const selectedDate = ref(null);
const selectedTime = ref(null);

// ... implementation
</script>
```

---

## PHASE 5: SAAS INFRASTRUCTURE

### Duration: 3-4 тижні

---

### 5.1 Stripe Billing

```php
namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Checkout\Session;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan): Session
    {
        $customer = $this->getOrCreateCustomer($tenant);

        return Session::create([
            'customer' => $customer->id,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url' => route('tenant.billing.success', ['tenant' => $tenant->slug]),
            'cancel_url' => route('tenant.billing.cancel', ['tenant' => $tenant->slug]),
            'metadata' => [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
            ],
        ]);
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['type'];
        $data = $payload['data']['object'];

        match($event) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($data),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($data),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($data),
            'invoice.payment_failed' => $this->handlePaymentFailed($data),
            default => null,
        };
    }

    protected function handleCheckoutCompleted(array $session): void
    {
        $tenantId = $session['metadata']['tenant_id'];
        $planId = $session['metadata']['plan_id'];

        $tenant = Tenant::find($tenantId);
        $plan = Plan::find($planId);

        // Отримуємо дані підписки
        $stripeSubscription = Subscription::retrieve($session['subscription']);

        // Створюємо підписку
        \App\Models\Subscription::create([
            'tenant_id' => $tenant->id,
            'stripe_subscription_id' => $stripeSubscription->id,
            'stripe_price_id' => $plan->stripe_price_id,
            'status' => 'active',
            'quantity' => 1,
            'current_period_start' => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end' => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        ]);

        // Оновлюємо tenant
        $tenant->update([
            'stripe_customer_id' => $session['customer'],
            'trial_ends_at' => null,
        ]);
    }
}
```

### 5.2 Usage Limits

```php
namespace App\Services;

class UsageLimitsService
{
    public function checkLimit(Tenant $tenant, string $feature): bool
    {
        $plan = $tenant->subscription?->plan ?? Plan::free();
        $limits = $plan->limits;

        return match($feature) {
            'masters' => $tenant->masterCount() < ($limits['masters'] ?? 1),
            'appointments' => $this->getMonthlyAppointments($tenant) < ($limits['appointments_per_month'] ?? 50),
            'widgets' => $tenant->widgets()->count() < ($limits['widgets'] ?? 1),
            'telegram_bot' => $limits['telegram_bot'] ?? false,
            'mini_app' => $limits['mini_app'] ?? false,
            'online_payment' => $limits['online_payment'] ?? false,
            'loyalty' => $limits['loyalty'] ?? false,
            'salary' => $limits['salary'] ?? false,
            'api' => $limits['api'] ?? false,
            default => false,
        };
    }

    public function getUsageStats(Tenant $tenant): array
    {
        $plan = $tenant->subscription?->plan ?? Plan::free();
        $limits = $plan->limits;

        return [
            'masters' => [
                'used' => $tenant->masterCount(),
                'limit' => $limits['masters'] ?? 1,
            ],
            'appointments_this_month' => [
                'used' => $this->getMonthlyAppointments($tenant),
                'limit' => $limits['appointments_per_month'] ?? 50,
            ],
            'storage_mb' => [
                'used' => $this->getStorageUsage($tenant),
                'limit' => $limits['storage_mb'] ?? 100,
            ],
        ];
    }
}
```

---

## TESTING STRATEGY

### Unit Tests

```php
// tests/Unit/Services/LoyaltyServiceTest.php

class LoyaltyServiceTest extends TestCase
{
    public function test_earn_bonuses_calculates_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $program = LoyaltyProgram::factory()->create([
            'tenant_id' => $tenant->id,
            'earn_rate' => 5.00, // 5%
        ]);
        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 1000,
        ]);

        $service = new LoyaltyService();
        $service->earnBonuses($appointment);

        $this->assertDatabaseHas('bonus_transactions', [
            'amount' => 50.00, // 5% від 1000
            'type' => 'earn',
        ]);
    }
}
```

### Feature Tests

```php
// tests/Feature/Telegram/BookingBotTest.php

class BookingBotTest extends TestCase
{
    public function test_start_command_shows_main_menu(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->postJson("/webhook/telegram/{$tenant->slug}/secret", [
            'message' => [
                'chat' => ['id' => '12345'],
                'text' => '/start',
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('telegram_conversations', [
            'chat_id' => '12345',
            'tenant_id' => $tenant->id,
            'state' => 'main_menu',
        ]);
    }

    public function test_booking_flow_creates_appointment(): void
    {
        // ... full booking flow test
    }
}
```

---

**Document Version:** 1.0
**Last Updated:** January 2026
