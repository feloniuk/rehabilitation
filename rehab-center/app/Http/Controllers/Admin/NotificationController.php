<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private TelegramNotificationService $telegramService;

    public function __construct(TelegramNotificationService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Головна сторінка модуля розсилок
     */
    public function index()
    {

        if (! $this->telegramService->isConfigured()) {
            return view('admin.notifications.index')
                ->with('error', 'Telegram не налаштовано. Додайте TELEGRAM_API_ID та TELEGRAM_API_HASH в .env файл.')
                ->with('templates', [])
                ->with('upcomingAppointments', collect())
                ->with('stats', [
                    'total_sent' => 0,
                    'total_failed' => 0,
                    'sent_today' => 0,
                ]);
        }

        $templates = NotificationTemplate::where('is_active', true)->get();

        // Майбутні записи для розсилки
        $upcomingAppointments = Appointment::with(['client', 'master', 'service'])
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(20)
            ->withQueryString();

        // Статистика розсилок
        $stats = [
            'total_sent' => NotificationLog::where('status', 'sent')->count(),
            'total_failed' => NotificationLog::where('status', 'failed')->count(),
            'sent_today' => NotificationLog::where('status', 'sent')
                ->whereDate('sent_at', today())
                ->count(),
        ];

        return view('admin.notifications.index', compact(
            'templates',
            'upcomingAppointments',
            'stats'
        ));
    }

    /**
     * Відправка розсилки
     */
    public function send(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'exists:appointments,id',
        ]);

        $template = NotificationTemplate::findOrFail($request->template_id);

        try {
            $results = $this->telegramService->sendBulkNotifications(
                $request->appointment_ids,
                $template
            );

            return redirect()->route('admin.notifications.index')
                ->with('success', "Розсилку завершено. Успішно: {$results['success']}, Помилки: {$results['failed']}");
        } catch (\Exception $e) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'Помилка розсилки: '.$e->getMessage());
        }
    }

    /**
     * Історія розсилок
     */
    public function logs()
    {
        $logs = NotificationLog::with(['appointment.client', 'appointment.service', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('admin.notifications.logs', compact('logs'));
    }

    /**
     * Управління шаблонами
     */
    public function templates()
    {
        $templates = NotificationTemplate::orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        $placeholders = NotificationTemplate::getAvailablePlaceholders();

        return view('admin.notifications.templates', compact('templates', 'placeholders'));
    }

    /**
     * Створення шаблону
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        NotificationTemplate::create([
            'name' => $request->name,
            'message' => $request->message,
            'is_active' => true,
        ]);

        return redirect()->route('admin.notifications.templates')
            ->with('success', 'Шаблон успішно створено');
    }

    /**
     * Оновлення шаблону
     */
    public function updateTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $template->update([
            'name' => $request->name,
            'message' => $request->message,
        ]);

        return redirect()->route('admin.notifications.templates')
            ->with('success', 'Шаблон оновлено');
    }

    /**
     * Видалення шаблону
     */
    public function deleteTemplate($id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.notifications.templates')
            ->with('success', 'Шаблон видалено');
    }

    /**
     * Попередній перегляд шаблону
     */
    public function previewTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $template = NotificationTemplate::findOrFail($request->template_id);
        $appointment = Appointment::with(['client', 'master', 'service'])
            ->findOrFail($request->appointment_id);

        $preview = $template->render($appointment);

        return response()->json(['preview' => $preview]);
    }

    /**
     * Отримати текст нагадування для копіювання
     */
    public function getReminderText($appointmentId)
    {
        try {
            $appointment = Appointment::with(['client', 'master', 'service'])
                ->findOrFail($appointmentId);

            $template = NotificationTemplate::find(1);

            if (! $template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон "на завтра" не знайдено (ID: 1)',
                ], 404);
            }

            $text = $template->render($appointment);

            return response()->json([
                'success' => true,
                'text' => $text,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Швидке нагадування "на завтра"
     */
    public function quickReminder(Request $request, $appointmentId)
    {
        try {
            $appointment = Appointment::with(['client', 'master', 'service'])
                ->findOrFail($appointmentId);

            // Використовуємо шаблон з ID 1
            $template = NotificationTemplate::find(1);

            if (! $template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон "на завтра" не знайдено (ID: 1)',
                ], 404);
            }

            // Відправка повідомлення
            $results = $this->telegramService->sendBulkNotifications(
                [$appointment->id],
                $template
            );

            if ($results['success'] > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Нагадування успішно надіслано!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Помилка відправки. Перевірте налаштування Telegram.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка: '.$e->getMessage(),
            ], 500);
        }
    }
}
