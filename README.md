# Open AI Bot
Проект для работы телеграм бота с Open AI API.

## Установка
**Требования**:
- Домен с установленным SSL сертификатом.

**Шаги:**
1. Выполнить команду для создания базы данных ```php artisan create:database_sqlite```.
2. Выполнить миграции ```php artisan migrate```.
3. На основе .env.example создать .env файл и заполнить TELEGRAM_BOT_TOKEN, TELEGRAM_WEBHOOK_SLUG, OPENAI_API_KEY.
4. Подключить бота к Telegram Webhook, перейдя по адресу https://api.telegram.org/bot<bot_token>/setWebhook.
5. Добавить в бота кнопку с названием метода ```/clear_context```
