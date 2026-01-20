# Telegram Bot Technical Specification

## 1. АРХІТЕКТУРА TELEGRAM БОТІВ

### 1.1 Типи ботів у системі

```
┌─────────────────────────────────────────────────────────────────┐
│                        BOT TYPES                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. PLATFORM MASTER BOT (Один на платформу)                    │
│     @BookOnUABot                                                │
│     ├── Реєстрація нових тенантів                              │
│     ├── Маршрутизація клієнтів до тенант-ботів                 │
│     ├── Глобальна підтримка                                    │
│     └── Статистика платформи                                   │
│                                                                 │
│  2. TENANT BOOKING BOTS (Один на тенанта)                      │
│     @SalonABot, @BarberBBot, @ClinicCBot...                    │
│     ├── Запис клієнтів                                         │
│     ├── Перегляд записів                                       │
│     ├── Скасування/перенесення                                 │
│     ├── Нагадування                                            │
│     └── Відгуки                                                │
│                                                                 │
│  3. NOTIFICATION USERBOT (MadelineProto)                       │
│     ├── Bulk розсилки                                          │
│     ├── Резолв телефонів                                       │
│     └── Відправка від імені реального акаунта                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 1.2 Варіанти реалізації Tenant Bots

#### Варіант A: Один бот з multi-tenant routing (РЕКОМЕНДОВАНО)

```
Переваги:
+ Один webhook endpoint
+ Простіше управління
+ Менше ботів для створення
+ Клієнт обирає салон через /start {tenant_slug}

Недоліки:
- Менш персоналізовано
- Спільна назва бота
```

```php
// Приклад: @BookOnBot
// /start salon_aurora -> показує меню Salon Aurora
// /start barber_ivan -> показує меню Barber Ivan

class TelegramWebhookController
{
    public function handle(Request $request)
    {
        $update = $request->all();

        // Визначаємо tenant з context
        $chatId = $update['message']['chat']['id'] ?? null;
        $text = $update['message']['text'] ?? '';

        // Перевіряємо /start з параметром
        if (preg_match('/^\/start\s+(.+)$/', $text, $matches)) {
            $tenantSlug = $matches[1];
            $tenant = Tenant::where('slug', $tenantSlug)->first();

            if ($tenant) {
                TelegramConversation::updateOrCreate(
                    ['chat_id' => $chatId],
                    ['tenant_id' => $tenant->id, 'state' => 'main_menu']
                );
            }
        }

        // Отримуємо tenant з збереженого стану
        $conversation = TelegramConversation::where('chat_id', $chatId)->first();

        if (!$conversation || !$conversation->tenant_id) {
            return $this->sendSelectTenantMessage($chatId);
        }

        return $this->processMessage($conversation, $update);
    }
}
```

#### Варіант B: Окремий бот для кожного тенанта

```
Переваги:
+ Повна персоналізація (@MySalonBot)
+ Брендинг
+ Ізоляція

Недоліки:
- Потрібно створювати бота вручну
- Окремий webhook для кожного
- Управління токенами
```

```php
// Модель для зберігання ботів тенантів
class TenantTelegramBot extends Model
{
    protected $fillable = [
        'tenant_id',
        'bot_token',
        'bot_username',
        'webhook_secret',
        'is_active',
        'settings', // JSON: {welcome_message, ...}
    ];

    public function setWebhook(): bool
    {
        $webhookUrl = route('telegram.webhook', [
            'tenant' => $this->tenant->slug,
            'secret' => $this->webhook_secret,
        ]);

        $response = Http::post(
            "https://api.telegram.org/bot{$this->bot_token}/setWebhook",
            ['url' => $webhookUrl]
        );

        return $response->successful();
    }
}
```

### 1.3 Рекомендація

**Для MVP: Варіант A** (один бот з multi-tenant routing)
- Швидше реалізувати
- Простіше підтримувати
- Можна мігрувати на Варіант B пізніше

**Для Enterprise: Варіант B** (окремі боти)
- Повний брендинг
- Додаткова плата за функціонал

---

## 2. FINITE STATE MACHINE (FSM)

### 2.1 Стани діалогу

```php
namespace App\Telegram;

enum ConversationState: string
{
    // Головні стани
    case INITIAL = 'initial';
    case MAIN_MENU = 'main_menu';

    // Бронювання
    case BOOKING_SELECT_SERVICE = 'booking.select_service';
    case BOOKING_SELECT_MASTER = 'booking.select_master';
    case BOOKING_SELECT_DATE = 'booking.select_date';
    case BOOKING_SELECT_TIME = 'booking.select_time';
    case BOOKING_CONFIRM = 'booking.confirm';
    case BOOKING_GET_CONTACT = 'booking.get_contact';
    case BOOKING_SUCCESS = 'booking.success';

    // Мої записи
    case MY_APPOINTMENTS_LIST = 'appointments.list';
    case MY_APPOINTMENTS_DETAILS = 'appointments.details';
    case MY_APPOINTMENTS_CANCEL_CONFIRM = 'appointments.cancel_confirm';

    // Відгуки
    case REVIEW_RATE = 'review.rate';
    case REVIEW_COMMENT = 'review.comment';

    // Профіль
    case PROFILE_VIEW = 'profile.view';
    case PROFILE_EDIT_NAME = 'profile.edit_name';
}
```

### 2.2 State Machine Implementation

```php
namespace App\Telegram;

class BookingStateMachine
{
    protected TelegramConversation $conversation;
    protected array $context;

    public function __construct(TelegramConversation $conversation)
    {
        $this->conversation = $conversation;
        $this->context = $conversation->context ?? [];
    }

    public function process(array $update): array
    {
        $state = ConversationState::from($this->conversation->state);

        return match($state) {
            ConversationState::INITIAL => $this->handleInitial($update),
            ConversationState::MAIN_MENU => $this->handleMainMenu($update),
            ConversationState::BOOKING_SELECT_SERVICE => $this->handleSelectService($update),
            ConversationState::BOOKING_SELECT_MASTER => $this->handleSelectMaster($update),
            ConversationState::BOOKING_SELECT_DATE => $this->handleSelectDate($update),
            ConversationState::BOOKING_SELECT_TIME => $this->handleSelectTime($update),
            ConversationState::BOOKING_CONFIRM => $this->handleConfirm($update),
            ConversationState::BOOKING_GET_CONTACT => $this->handleGetContact($update),
            default => $this->handleUnknown($update),
        };
    }

    protected function handleMainMenu(array $update): array
    {
        $callbackData = $update['callback_query']['data'] ?? null;
        $text = $update['message']['text'] ?? null;

        if ($callbackData === 'book') {
            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_SERVICE,
                $this->buildServiceKeyboard()
            );
        }

        if ($callbackData === 'my_appointments') {
            return $this->transitionTo(
                ConversationState::MY_APPOINTMENTS_LIST,
                $this->buildAppointmentsKeyboard()
            );
        }

        // Показуємо головне меню
        return $this->showMainMenu();
    }

    protected function handleSelectService(array $update): array
    {
        $callbackData = $update['callback_query']['data'] ?? null;

        if ($callbackData === 'back') {
            return $this->transitionTo(ConversationState::MAIN_MENU);
        }

        if (str_starts_with($callbackData, 'service_')) {
            $serviceId = (int) str_replace('service_', '', $callbackData);

            $this->setContext('service_id', $serviceId);

            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_MASTER,
                $this->buildMasterKeyboard($serviceId)
            );
        }

        return $this->showCurrentState();
    }

    protected function handleSelectMaster(array $update): array
    {
        $callbackData = $update['callback_query']['data'] ?? null;

        if ($callbackData === 'back') {
            return $this->transitionTo(ConversationState::BOOKING_SELECT_SERVICE);
        }

        if ($callbackData === 'any_master') {
            // Логіка вибору будь-якого доступного мастера
            $serviceId = $this->getContext('service_id');
            $master = $this->findAvailableMaster($serviceId);
            $this->setContext('master_id', $master->id);
        } elseif (str_starts_with($callbackData, 'master_')) {
            $masterId = (int) str_replace('master_', '', $callbackData);
            $this->setContext('master_id', $masterId);
        }

        return $this->transitionTo(
            ConversationState::BOOKING_SELECT_DATE,
            $this->buildDateKeyboard()
        );
    }

    protected function handleSelectDate(array $update): array
    {
        $callbackData = $update['callback_query']['data'] ?? null;

        if ($callbackData === 'back') {
            return $this->transitionTo(ConversationState::BOOKING_SELECT_MASTER);
        }

        if ($callbackData === 'next_week') {
            $currentWeekStart = $this->getContext('week_start', now());
            $this->setContext('week_start', $currentWeekStart->addWeek());
            return $this->showCurrentState();
        }

        if ($callbackData === 'prev_week') {
            $currentWeekStart = $this->getContext('week_start', now());
            if ($currentWeekStart->isAfter(now())) {
                $this->setContext('week_start', $currentWeekStart->subWeek());
            }
            return $this->showCurrentState();
        }

        if (str_starts_with($callbackData, 'date_')) {
            $date = str_replace('date_', '', $callbackData);
            $this->setContext('date', $date);

            return $this->transitionTo(
                ConversationState::BOOKING_SELECT_TIME,
                $this->buildTimeKeyboard($date)
            );
        }

        return $this->showCurrentState();
    }

    protected function handleConfirm(array $update): array
    {
        $callbackData = $update['callback_query']['data'] ?? null;

        if ($callbackData === 'confirm') {
            // Перевіряємо чи є контакт клієнта
            $user = $this->findUserByChatId($this->conversation->chat_id);

            if (!$user || !$user->phone) {
                return $this->transitionTo(
                    ConversationState::BOOKING_GET_CONTACT,
                    $this->buildContactRequest()
                );
            }

            // Створюємо запис
            return $this->createAppointment($user);
        }

        if ($callbackData === 'cancel') {
            $this->clearContext();
            return $this->transitionTo(ConversationState::MAIN_MENU);
        }

        return $this->showCurrentState();
    }

    protected function handleGetContact(array $update): array
    {
        $contact = $update['message']['contact'] ?? null;

        if ($contact) {
            // Зберігаємо або оновлюємо клієнта
            $user = $this->createOrUpdateClient($contact);

            // Створюємо запис
            return $this->createAppointment($user);
        }

        return $this->showCurrentState();
    }

    protected function createAppointment(User $client): array
    {
        $serviceId = $this->getContext('service_id');
        $masterId = $this->getContext('master_id');
        $date = $this->getContext('date');
        $time = $this->getContext('time');

        $masterService = MasterService::where('master_id', $masterId)
            ->where('service_id', $serviceId)
            ->first();

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

        // Відправляємо повідомлення мастеру
        dispatch(new SendMasterTelegramNotification($appointment));

        $this->clearContext();

        return $this->transitionTo(
            ConversationState::BOOKING_SUCCESS,
            $this->buildSuccessMessage($appointment)
        );
    }

    // Helper methods

    protected function transitionTo(ConversationState $state, ?array $response = null): array
    {
        $this->conversation->update([
            'state' => $state->value,
            'context' => $this->context,
        ]);

        return $response ?? $this->showCurrentState();
    }

    protected function setContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    protected function getContext(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    protected function clearContext(): void
    {
        $this->context = [];
    }
}
```

---

## 3. KEYBOARD BUILDERS

### 3.1 Inline Keyboards

```php
namespace App\Telegram\Keyboards;

class InlineKeyboardBuilder
{
    protected array $rows = [];
    protected array $currentRow = [];

    public function button(string $text, string $callbackData): self
    {
        $this->currentRow[] = [
            'text' => $text,
            'callback_data' => $callbackData,
        ];

        return $this;
    }

    public function url(string $text, string $url): self
    {
        $this->currentRow[] = [
            'text' => $text,
            'url' => $url,
        ];

        return $this;
    }

    public function webApp(string $text, string $url): self
    {
        $this->currentRow[] = [
            'text' => $text,
            'web_app' => ['url' => $url],
        ];

        return $this;
    }

    public function row(): self
    {
        if (!empty($this->currentRow)) {
            $this->rows[] = $this->currentRow;
            $this->currentRow = [];
        }

        return $this;
    }

    public function build(): array
    {
        if (!empty($this->currentRow)) {
            $this->row();
        }

        return ['inline_keyboard' => $this->rows];
    }

    // Preset keyboards

    public static function mainMenu(Tenant $tenant): array
    {
        $builder = new self();

        return $builder
            ->button('📅 Записатись', 'book')
            ->button('📋 Мої записи', 'my_appointments')
            ->row()
            ->button('ℹ️ Про нас', 'about')
            ->button('📞 Контакти', 'contacts')
            ->row()
            ->build();
    }

    public static function services(Collection $services): array
    {
        $builder = new self();

        foreach ($services as $service) {
            $builder
                ->button(
                    $service->name . ' - від ' . number_format($service->price_from) . ' грн',
                    'service_' . $service->id
                )
                ->row();
        }

        $builder->button('◀️ Назад', 'back')->row();

        return $builder->build();
    }

    public static function masters(Collection $masters): array
    {
        $builder = new self();

        $builder->button('🎲 Будь-який майстер', 'any_master')->row();

        foreach ($masters as $master) {
            $rating = $master->rating ? '⭐' . number_format($master->rating, 1) : '';
            $builder
                ->button("👤 {$master->name} {$rating}", 'master_' . $master->id)
                ->row();
        }

        $builder->button('◀️ Назад', 'back')->row();

        return $builder->build();
    }

    public static function dates(Carbon $weekStart, int $masterId): array
    {
        $builder = new self();
        $master = User::find($masterId);

        $dayNames = [
            'monday' => 'Пн',
            'tuesday' => 'Вт',
            'wednesday' => 'Ср',
            'thursday' => 'Чт',
            'friday' => 'Пт',
            'saturday' => 'Сб',
            'sunday' => 'Нд',
        ];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dayName = strtolower($date->englishDayOfWeek);

            if ($date->isPast() || !$master->isWorkingOnDay($dayName)) {
                continue;
            }

            $builder
                ->button(
                    "📅 {$dayNames[$dayName]} {$date->format('d.m')}",
                    'date_' . $date->format('Y-m-d')
                )
                ->row();
        }

        // Навігація по тижнях
        $builder
            ->button('◀️ Попередній', 'prev_week')
            ->button('▶️ Наступний', 'next_week')
            ->row()
            ->button('◀️ Назад', 'back')
            ->row();

        return $builder->build();
    }

    public static function times(array $slots): array
    {
        $builder = new self();
        $chunks = array_chunk($slots, 3);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $slot) {
                $builder->button('🕐 ' . $slot, 'time_' . $slot);
            }
            $builder->row();
        }

        $builder->button('◀️ Назад', 'back')->row();

        return $builder->build();
    }

    public static function confirm(): array
    {
        return (new self())
            ->button('✅ Підтвердити', 'confirm')
            ->button('❌ Скасувати', 'cancel')
            ->row()
            ->build();
    }
}
```

### 3.2 Reply Keyboards (для контакту)

```php
namespace App\Telegram\Keyboards;

class ReplyKeyboardBuilder
{
    protected array $rows = [];
    protected array $currentRow = [];
    protected bool $oneTime = false;
    protected bool $resize = true;

    public function button(string $text, bool $requestContact = false, bool $requestLocation = false): self
    {
        $button = ['text' => $text];

        if ($requestContact) {
            $button['request_contact'] = true;
        }

        if ($requestLocation) {
            $button['request_location'] = true;
        }

        $this->currentRow[] = $button;

        return $this;
    }

    public function row(): self
    {
        if (!empty($this->currentRow)) {
            $this->rows[] = $this->currentRow;
            $this->currentRow = [];
        }

        return $this;
    }

    public function oneTime(bool $oneTime = true): self
    {
        $this->oneTime = $oneTime;
        return $this;
    }

    public function resize(bool $resize = true): self
    {
        $this->resize = $resize;
        return $this;
    }

    public function build(): array
    {
        if (!empty($this->currentRow)) {
            $this->row();
        }

        return [
            'keyboard' => $this->rows,
            'one_time_keyboard' => $this->oneTime,
            'resize_keyboard' => $this->resize,
        ];
    }

    public static function contactRequest(): array
    {
        return (new self())
            ->button('📱 Надіслати контакт', requestContact: true)
            ->row()
            ->oneTime()
            ->build();
    }

    public static function remove(): array
    {
        return ['remove_keyboard' => true];
    }
}
```

---

## 4. MESSAGE TEMPLATES

### 4.1 Шаблони повідомлень

```php
namespace App\Telegram\Messages;

class MessageTemplates
{
    public static function welcome(Tenant $tenant, ?User $user = null): string
    {
        $greeting = $user ? "Вітаємо, {$user->name}!" : 'Вітаємо!';
        $centerName = $tenant->getSetting('center_name', $tenant->name);

        return <<<MSG
{$greeting}

Ви у боті {$centerName}.

Оберіть дію:
MSG;
    }

    public static function selectService(): string
    {
        return "💆 Оберіть послугу:";
    }

    public static function selectMaster(): string
    {
        return "👤 Оберіть майстра:";
    }

    public static function selectDate(): string
    {
        return "📅 Оберіть дату:";
    }

    public static function selectTime(): string
    {
        return "🕐 Оберіть час:";
    }

    public static function confirmBooking(
        Service $service,
        User $master,
        string $date,
        string $time,
        float $price,
        Tenant $tenant
    ): string {
        $centerName = $tenant->getSetting('center_name', $tenant->name);
        $formattedDate = Carbon::parse($date)->format('d.m.Y');
        $formattedPrice = number_format($price, 0, ',', ' ');

        return <<<MSG
✅ *Підтвердіть запис:*

📍 {$centerName}
💆 {$service->name}
👤 Майстер: {$master->name}
📅 Дата: {$formattedDate}
🕐 Час: {$time}
💰 Вартість: {$formattedPrice} грн

Бажаєте підтвердити?
MSG;
    }

    public static function requestContact(): string
    {
        return <<<MSG
📱 Для завершення запису, будь ласка, надішліть свій контакт.

Натисніть кнопку нижче:
MSG;
    }

    public static function bookingSuccess(Appointment $appointment): string
    {
        $date = $appointment->appointment_date->format('d.m.Y');
        $time = substr($appointment->appointment_time, 0, 5);

        return <<<MSG
🎉 *Запис підтверджено!*

📅 {$date} о {$time}
💆 {$appointment->service->name}
👤 Майстер: {$appointment->master->name}

Нагадаємо вам за 24 години до візиту.

_Щоб скасувати запис, перейдіть у "Мої записи"_
MSG;
    }

    public static function appointmentsList(Collection $appointments): string
    {
        if ($appointments->isEmpty()) {
            return "📋 У вас немає активних записів.";
        }

        $text = "📋 *Ваші записи:*\n\n";

        foreach ($appointments as $i => $appointment) {
            $date = $appointment->appointment_date->format('d.m');
            $time = substr($appointment->appointment_time, 0, 5);
            $status = $appointment->status === 'scheduled' ? '🟢' : '🔴';

            $text .= "{$status} {$date} о {$time}\n";
            $text .= "   {$appointment->service->name}\n";
            $text .= "   Майстер: {$appointment->master->name}\n\n";
        }

        return $text;
    }

    public static function appointmentDetails(Appointment $appointment): string
    {
        $date = $appointment->appointment_date->format('d.m.Y');
        $time = substr($appointment->appointment_time, 0, 5);
        $price = number_format($appointment->price, 0, ',', ' ');
        $statusText = match($appointment->status) {
            'scheduled' => '🟢 Заплановано',
            'completed' => '✅ Завершено',
            'cancelled' => '🔴 Скасовано',
            default => $appointment->status,
        };

        return <<<MSG
📋 *Деталі запису:*

📅 Дата: {$date}
🕐 Час: {$time}
💆 Послуга: {$appointment->service->name}
👤 Майстер: {$appointment->master->name}
💰 Вартість: {$price} грн
📊 Статус: {$statusText}
MSG;
    }

    public static function cancelConfirm(Appointment $appointment): string
    {
        return <<<MSG
⚠️ *Ви впевнені, що хочете скасувати запис?*

📅 {$appointment->appointment_date->format('d.m.Y')} о {substr($appointment->appointment_time, 0, 5)}
💆 {$appointment->service->name}
MSG;
    }

    public static function cancelSuccess(): string
    {
        return "✅ Запис успішно скасовано.";
    }

    public static function reminder24h(Appointment $appointment, Tenant $tenant): string
    {
        $centerName = $tenant->getSetting('center_name', $tenant->name);
        $time = substr($appointment->appointment_time, 0, 5);
        $address = $tenant->getSetting('center_address', '');

        return <<<MSG
🔔 *Нагадування про запис!*

Завтра о {$time} вас чекає {$appointment->master->name}.

💆 {$appointment->service->name}
📍 {$centerName}
{$address}

Якщо ви не зможете прийти, будь ласка, скасуйте запис заздалегідь.
MSG;
    }

    public static function reviewRequest(Appointment $appointment): string
    {
        return <<<MSG
⭐ *Як вам візит?*

{$appointment->service->name} у {$appointment->master->name}

Будь ласка, оцініть вашу задоволеність:
MSG;
    }

    public static function about(Tenant $tenant): string
    {
        $name = $tenant->getSetting('center_name', $tenant->name);
        $description = $tenant->getSetting('center_description', '');

        return <<<MSG
ℹ️ *{$name}*

{$description}
MSG;
    }

    public static function contacts(Tenant $tenant): string
    {
        $phone = $tenant->getSetting('center_phone', '');
        $address = $tenant->getSetting('center_address', '');
        $workingHours = $tenant->getSetting('working_hours', '');

        return <<<MSG
📞 *Контакти*

📱 Телефон: {$phone}
📍 Адреса: {$address}
🕐 Графік роботи:
{$workingHours}
MSG;
    }
}
```

---

## 5. WEBHOOK HANDLER

### 5.1 Controller

```php
namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TelegramConversation;
use App\Telegram\BookingStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, string $tenant, string $secret)
    {
        // Верифікація секрету
        $tenantModel = Tenant::where('slug', $tenant)->first();

        if (!$tenantModel) {
            Log::warning('Telegram webhook: tenant not found', ['tenant' => $tenant]);
            return response('OK');
        }

        $bot = $tenantModel->telegramBot;

        if (!$bot || $bot->webhook_secret !== $secret) {
            Log::warning('Telegram webhook: invalid secret', ['tenant' => $tenant]);
            return response('OK');
        }

        $update = $request->all();

        Log::info('Telegram webhook received', [
            'tenant' => $tenant,
            'update_id' => $update['update_id'] ?? null,
        ]);

        try {
            $this->processUpdate($tenantModel, $bot, $update);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'tenant' => $tenant,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response('OK');
    }

    protected function processUpdate(Tenant $tenant, $bot, array $update): void
    {
        $chatId = $this->extractChatId($update);

        if (!$chatId) {
            return;
        }

        // Отримуємо або створюємо conversation
        $conversation = TelegramConversation::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'chat_id' => $chatId,
            ],
            [
                'state' => 'initial',
                'context' => [],
            ]
        );

        $conversation->update(['last_message_at' => now()]);

        // Обробляємо через State Machine
        $stateMachine = new BookingStateMachine($conversation);
        $response = $stateMachine->process($update);

        // Відправляємо відповідь
        $this->sendResponse($bot->bot_token, $chatId, $response);
    }

    protected function extractChatId(array $update): ?string
    {
        if (isset($update['message']['chat']['id'])) {
            return (string) $update['message']['chat']['id'];
        }

        if (isset($update['callback_query']['message']['chat']['id'])) {
            return (string) $update['callback_query']['message']['chat']['id'];
        }

        return null;
    }

    protected function sendResponse(string $token, string $chatId, array $response): void
    {
        $method = $response['method'] ?? 'sendMessage';

        $params = array_merge([
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
        ], $response);

        unset($params['method']);

        Http::post("https://api.telegram.org/bot{$token}/{$method}", $params);
    }
}
```

### 5.2 Routes

```php
// routes/web.php

Route::post('/webhook/telegram/{tenant}/{secret}', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook')
    ->withoutMiddleware(['csrf']);
```

---

## 6. MINI APP INTEGRATION

### 6.1 Налаштування Mini App

```php
// В боті додаємо кнопку Web App
public static function mainMenuWithMiniApp(Tenant $tenant): array
{
    $miniAppUrl = route('telegram.mini-app', ['tenant' => $tenant->slug]);

    return (new InlineKeyboardBuilder())
        ->webApp('📱 Відкрити додаток', $miniAppUrl)
        ->row()
        ->button('📅 Швидкий запис', 'quick_book')
        ->row()
        ->button('📋 Мої записи', 'my_appointments')
        ->row()
        ->build();
}
```

### 6.2 Mini App Controller

```php
namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class MiniAppController extends Controller
{
    public function index(Request $request, string $tenant)
    {
        $tenantModel = Tenant::where('slug', $tenant)->first();

        if (!$tenantModel) {
            abort(404);
        }

        // Повертаємо SPA для Mini App
        return view('telegram.mini-app', [
            'tenant' => $tenantModel,
            'initData' => $request->query('tgWebAppData'),
        ]);
    }

    public function validate(Request $request)
    {
        // Валідація initData від Telegram
        $initData = $request->input('init_data');
        $botToken = config('services.telegram_bot.token');

        // Парсимо initData
        parse_str($initData, $params);

        $hash = $params['hash'] ?? '';
        unset($params['hash']);

        ksort($params);
        $dataCheckString = collect($params)
            ->map(fn($v, $k) => "{$k}={$v}")
            ->join("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        if (!hash_equals($calculatedHash, $hash)) {
            return response()->json(['valid' => false], 401);
        }

        // Отримуємо дані користувача
        $user = json_decode($params['user'] ?? '{}', true);

        return response()->json([
            'valid' => true,
            'user' => $user,
        ]);
    }
}
```

---

## 7. АВТОМАТИЧНІ НАГАДУВАННЯ

### 7.1 Scheduler

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule): void
{
    // Надсилаємо нагадування за 24 години
    $schedule->job(new SendAppointmentReminders(hours: 24))
        ->hourly()
        ->between('9:00', '21:00');

    // Надсилаємо нагадування за 2 години
    $schedule->job(new SendAppointmentReminders(hours: 2))
        ->everyFifteenMinutes()
        ->between('8:00', '22:00');

    // Запитуємо відгуки через 2 години після візиту
    $schedule->job(new RequestReviewsJob())
        ->hourly()
        ->between('10:00', '20:00');
}
```

### 7.2 Reminder Job

```php
namespace App\Jobs;

use App\Models\Appointment;
use App\Models\ScheduledReminder;
use App\Telegram\Messages\MessageTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendAppointmentReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $hours
    ) {}

    public function handle(): void
    {
        $targetTime = now()->addHours($this->hours);

        // Знаходимо записи, які потребують нагадування
        $appointments = Appointment::query()
            ->where('status', 'scheduled')
            ->whereDate('appointment_date', $targetTime->toDateString())
            ->whereRaw("TIME(appointment_time) BETWEEN ? AND ?", [
                $targetTime->format('H:i'),
                $targetTime->addHour()->format('H:i'),
            ])
            ->whereDoesntHave('scheduledReminders', function ($q) {
                $q->where('trigger_hours', $this->hours)
                  ->whereIn('status', ['sent', 'scheduled']);
            })
            ->with(['client', 'master', 'service', 'tenant'])
            ->get();

        foreach ($appointments as $appointment) {
            $this->sendReminder($appointment);
        }
    }

    protected function sendReminder(Appointment $appointment): void
    {
        $client = $appointment->client;

        if (!$client->telegram_chat_id) {
            return;
        }

        $message = MessageTemplates::reminder24h(
            $appointment,
            $appointment->tenant
        );

        $bot = $appointment->tenant->telegramBot;

        if (!$bot) {
            return;
        }

        $response = Http::post(
            "https://api.telegram.org/bot{$bot->bot_token}/sendMessage",
            [
                'chat_id' => $client->telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]
        );

        ScheduledReminder::create([
            'tenant_id' => $appointment->tenant_id,
            'appointment_id' => $appointment->id,
            'trigger_hours' => $this->hours,
            'scheduled_at' => now(),
            'sent_at' => $response->successful() ? now() : null,
            'status' => $response->successful() ? 'sent' : 'failed',
            'error_message' => $response->failed() ? $response->body() : null,
        ]);
    }
}
```

---

## 8. DEPLOYMENT CHECKLIST

### 8.1 Налаштування бота

```bash
# 1. Створити бота через @BotFather
# 2. Отримати токен
# 3. Налаштувати .env

TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_API_ID=your_api_id
TELEGRAM_API_HASH=your_api_hash

# 4. Встановити webhook
php artisan telegram:set-webhook

# 5. Перевірити webhook
php artisan telegram:webhook-info
```

### 8.2 Artisan Commands

```php
// app/Console/Commands/TelegramSetWebhook.php

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {tenant?}';
    protected $description = 'Set Telegram webhook for tenant bot';

    public function handle(): int
    {
        $tenantSlug = $this->argument('tenant');

        if ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
            $this->setWebhookForTenant($tenant);
        } else {
            // Для всіх тенантів з ботами
            Tenant::whereHas('telegramBot')->each(function ($tenant) {
                $this->setWebhookForTenant($tenant);
            });
        }

        return Command::SUCCESS;
    }

    protected function setWebhookForTenant(Tenant $tenant): void
    {
        $bot = $tenant->telegramBot;

        if (!$bot) {
            $this->warn("No bot configured for tenant: {$tenant->slug}");
            return;
        }

        if ($bot->setWebhook()) {
            $this->info("Webhook set for: {$tenant->slug}");
        } else {
            $this->error("Failed to set webhook for: {$tenant->slug}");
        }
    }
}
```

---

**Document Version:** 1.0
**Last Updated:** January 2026
