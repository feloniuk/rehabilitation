<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Нормалізує телефонний номер до формату +380XXXXXXXXX
     *
     * Приклади вхідних даних:
     * - 0663417595 → +380663417595
     * - +380 66 341 75 95 → +380663417595
     * - 380663417595 → +380663417595
     * - 066 341 75 95 → +380663417595
     * - +38(066)341-75-95 → +380663417595
     */
    public static function normalize(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Видаляємо всі символи крім цифр і +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Видаляємо + з будь-якого місця крім початку
        if (str_contains($phone, '+')) {
            $phone = '+'.str_replace('+', '', $phone);
        }

        // Якщо починається з +380 - вже добре
        if (str_starts_with($phone, '+380')) {
            // Перевіряємо довжину (має бути +380 + 9 цифр = 13 символів)
            if (strlen($phone) === 13) {
                return $phone;
            }
        }

        // Якщо починається з 380 (без +)
        if (str_starts_with($phone, '380') && strlen($phone) === 12) {
            return '+'.$phone;
        }

        // Якщо починається з 0 (локальний формат)
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+38'.$phone;
        }

        // Якщо тільки 9 цифр (без коду країни і без 0)
        if (strlen($phone) === 9 && ! str_starts_with($phone, '0')) {
            return '+380'.$phone;
        }

        // Повертаємо як є, якщо не вдалося розпізнати формат
        return $phone;
    }

    /**
     * Форматує телефон для відображення: +380 XX XXX XX XX
     */
    public static function format(?string $phone): ?string
    {
        $normalized = self::normalize($phone);

        if (empty($normalized) || strlen($normalized) !== 13) {
            return $phone; // Повертаємо оригінал якщо не вдалося нормалізувати
        }

        // +380 XX XXX XX XX
        return substr($normalized, 0, 4).' '.
               substr($normalized, 4, 2).' '.
               substr($normalized, 6, 3).' '.
               substr($normalized, 9, 2).' '.
               substr($normalized, 11, 2);
    }

    /**
     * Порівнює два номери телефонів
     */
    public static function equals(?string $phone1, ?string $phone2): bool
    {
        return self::normalize($phone1) === self::normalize($phone2);
    }
}
