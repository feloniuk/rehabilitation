<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Обробка навігації по тижнях
        $weekOffset = 0;
        $selectedDateIndex = null;

        if ($request->has('week')) {
            if ($request->week === 'previous') {
                $weekOffset = $request->session()->get('week_offset', 0) - 1;
            } elseif ($request->week === 'next') {
                $weekOffset = $request->session()->get('week_offset', 0) + 1;
            } else {
                $weekOffset = (int) $request->week;
            }

            // Збереження offset в сесію
            $request->session()->put('week_offset', $weekOffset);

            // Обробка вибраної дати при навігації
            if ($request->has('reset_date_index')) {
                // Для кнопки "Сьогодні" - не встановлюємо індекс, його встановимо після обчислення todayIndex
                $request->session()->forget('selected_date_index');
            } elseif ($request->has('selected_date_index')) {
                // Для стрілочок навігації - встановлюємо переданий індекс
                $selectedDateIndex = (int) $request->input('selected_date_index');
                $request->session()->put('selected_date_index', $selectedDateIndex);
            }

            // Редірект без параметру, щоб избежать повторної обробки при перезагрузці
            return redirect()->route('admin.dashboard');
        }

        // Отримуємо offset з сесії (якщо він є)
        $weekOffset = $request->session()->get('week_offset', 0);

        $stats = $this->getStats($user);
        $calendar = $this->getCalendarData($user, $weekOffset);

        // Отримуємо збережену дату або використовуємо індекс сьогодні
        $selectedDateIndex = $request->session()->get('selected_date_index', $calendar['todayIndex']);

        return view('admin.dashboard', compact('calendar', 'stats', 'selectedDateIndex'));
    }

    /**
     * AJAX endpoint для збереження вибраної дати
     */
    public function selectDate(Request $request)
    {
        $request->session()->put('selected_date_index', (int) $request->input('date_index', 0));

        return response()->json(['success' => true]);
    }

    /**
     * AJAX endpoint для завантаження календаря при навігації
     */
    public function loadCalendar(Request $request)
    {
        $user = auth()->user();

        // Обробка навігації по тижнях
        $direction = $request->input('direction'); // 'previous', 'next', або 'today'
        $currentWeekOffset = (int) $request->input('week_offset', 0);

        $weekOffset = $currentWeekOffset;
        $selectedDateIndex = null;

        if ($direction === 'previous') {
            $weekOffset = $currentWeekOffset - 1;
            $selectedDateIndex = 6; // Воскресенье
        } elseif ($direction === 'next') {
            $weekOffset = $currentWeekOffset + 1;
            $selectedDateIndex = 0; // Понедельник
        } elseif ($direction === 'today') {
            $weekOffset = 0;
            $selectedDateIndex = null; // Будет использовано todayIndex
        }

        // Збереження offset в сесію
        $request->session()->put('week_offset', $weekOffset);

        if ($selectedDateIndex !== null) {
            $request->session()->put('selected_date_index', $selectedDateIndex);
        } else {
            $request->session()->forget('selected_date_index');
        }

        $calendar = $this->getCalendarData($user, $weekOffset);
        $finalSelectedDateIndex = $request->session()->get('selected_date_index', $calendar['todayIndex']);

        // Преобразуємо weekDates в строки формату Y-m-d для JSON
        $calendar['weekDates'] = collect($calendar['weekDates'])->map(fn($date) => $date->format('Y-m-d'))->toArray();

        return response()->json([
            'success' => true,
            'calendar' => $calendar,
            'selectedDateIndex' => $finalSelectedDateIndex,
            'weekOffset' => $weekOffset,
        ]);
    }

    private function getStats($user)
    {
        $baseQuery = $user->isAdmin()
            ? Appointment::query()
            : Appointment::where('master_id', $user->id);

        return [
            'today' => (clone $baseQuery)->whereDate('appointment_date', today())->count(),
            'week' => (clone $baseQuery)->whereBetween('appointment_date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'month' => (clone $baseQuery)->whereBetween('appointment_date', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count(),
            'upcoming' => (clone $baseQuery)->where('appointment_date', '>=', today())->count(),
        ];
    }

    private function getCalendarData($user, $weekOffset = 0)
    {
        // Отримуємо потрібний тиждень
        $startDate = now()->addWeeks($weekOffset)->startOfWeek();
        $endDate = now()->addWeeks($weekOffset)->endOfWeek();

        // Отримуємо всіх активних майстрів
        $mastersQuery = User::where('role', 'master')
            ->where('is_active', true);

        if ($user->isMaster()) {
            $mastersQuery->where('id', $user->id);
        }

        $masters = $mastersQuery->orderBy('name')->get();

        // Отримуємо всі записи на цей тиждень
        $appointmentsQuery = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->with(['client', 'service', 'master'])
            ->where('status', 'scheduled');

        if ($user->isMaster()) {
            $appointmentsQuery->where('master_id', $user->id);
        }

        $appointments = $appointmentsQuery->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        // Групуємо записи по майстрах і датах
        $scheduleByMaster = [];
        foreach ($masters as $master) {
            $scheduleByMaster[$master->id] = [
                'master' => $master,
                'appointments_by_date' => [],
            ];
        }

        foreach ($appointments as $appointment) {
            $dateKey = $appointment->appointment_date->format('Y-m-d');

            if (! isset($scheduleByMaster[$appointment->master_id]['appointments_by_date'][$dateKey])) {
                $scheduleByMaster[$appointment->master_id]['appointments_by_date'][$dateKey] = [];
            }

            $scheduleByMaster[$appointment->master_id]['appointments_by_date'][$dateKey][] = [
                'id' => $appointment->id,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'duration' => (int) $appointment->duration,
                'client_name' => $appointment->client->name,
                'client_telegram' => $appointment->client->telegram_username,
                'service_name' => $appointment->service->name,
                'price' => $appointment->price,
                'status' => $appointment->status,
                'telegram_notification_sent' => $appointment->telegram_notification_sent,
                'is_confirmed' => $appointment->is_confirmed,
            ];
        }

        // Генеруємо фіксовані часові слоти з 8:00 до 22:00 з кроком 30 хвилин
        $timeSlots = $this->generateTimeSlots('08:00', '22:00', 30);

        // Генеруємо дати тижня
        $weekDates = [];
        $currentDate = $startDate->copy();
        $todayIndex = null;

        while ($currentDate <= $endDate) {
            $weekDates[] = $currentDate->copy();

            if ($currentDate->isToday()) {
                $todayIndex = count($weekDates) - 1;
            }

            $currentDate->addDay();
        }

        return [
            'masters' => $masters,
            'scheduleByMaster' => $scheduleByMaster,
            'timeSlots' => $timeSlots,
            'weekDates' => $weekDates,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'todayIndex' => $todayIndex ?? 0,
        ];
    }

    /**
     * Генерує часові слоти з заданим кроком
     *
     * @param  string  $startTime  Початковий час (формат H:i)
     * @param  string  $endTime  Кінцевий час (формат H:i)
     * @param  int  $stepMinutes  Крок у хвилинах
     */
    private function generateTimeSlots(string $startTime, string $endTime, int $stepMinutes): array
    {
        $slots = [];
        $current = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        while ($current->lte($end)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($stepMinutes);
        }

        return $slots;
    }
}
