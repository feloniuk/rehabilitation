<!-- Cookie Consent Notice -->
<div id="cookie-notice" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white px-4 py-4 shadow-2xl z-40 hidden">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-sm md:text-base flex-1">
            <p class="mb-2 font-medium">
                <i class="fas fa-shield-alt mr-2"></i> Використання cookies
            </p>
            <p class="text-gray-300 leading-relaxed">
                Цей сайт використовує cookies для покращення користувацького досвіду та сбору аналітичних даних.
                Ми збираємо ваші дані лише при добровільному заповненні форм для записи та контакту з нами.
                Натискаючи «Я розумію», ви даєте згоду на використання cookies відповідно до нашої
                <a href="{{ route('pages.show', 'privacy-policy') }}" class="text-pink-400 hover:text-pink-300 underline">Політики конфіденційності</a>.
            </p>
        </div>
        <div class="flex gap-3 flex-shrink-0">
            <button id="cookie-accept-btn"
                    class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-lg font-medium transition-colors whitespace-nowrap">
                Я розумію
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Перевіряємо наявність cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        // Встановлюємо cookie на максимальний період (400 днів)
        function setCookie(name, value, days = 400) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = `expires=${date.toUTCString()}`;
            document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
        }

        // Показуємо баннер якщо cookies не прийняті
        const cookieNotice = document.getElementById('cookie-notice');
        const acceptBtn = document.getElementById('cookie-accept-btn');

        if (!getCookie('cookie_consent')) {
            cookieNotice.classList.remove('hidden');
            // Додаємо анімацію появи
            setTimeout(() => {
                cookieNotice.style.animation = 'slideUp 0.5s ease-out';
            }, 100);
        }

        // Обробник кнопки прийняття
        acceptBtn.addEventListener('click', function() {
            setCookie('cookie_consent', 'accepted', 400);
            // Гладко приховуємо баннер
            cookieNotice.style.opacity = '0';
            cookieNotice.style.transform = 'translateY(100%)';
            cookieNotice.style.transition = 'all 0.5s ease-out';
            setTimeout(() => {
                cookieNotice.classList.add('hidden');
            }, 500);
        });
    });
</script>

<style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(100%);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
