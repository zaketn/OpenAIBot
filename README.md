# Open AI Bot
Проект для работы телеграм бота с Open AI API на Laravel.
### Возможности
- Чат с ботом. Бот запоминает предыдущее сообщения и отвечает на следующее сообщение с учетом предыдущего.
- Рерайтинг текстов. Боту отправляется файл и число, обозначающее сколько раз нужно переписать текст. Бот переписывает текст и отправляет новые версии текста в виде файла. Есть возможность пропуска участков текста, заранее помеченные ```{{ фигурными скобками }}```.
- Выбор модели AI
## Установка
### Требования:
- Ключ OpenAI API
- Домен с установленным SSL сертификатом.

### Шаги:
1. Создать базу данных и выполнить миграции ```php artisan migrate```.
2. На основе .env.example создать .env файл и заполнить данные для подключения к БД, APP_URL, TELEGRAM_BOT_TOKEN, OPENAI_API_KEY.
3. Подключить бота к Telegram Webhook, перейдя в браузере по адресу https://api.telegram.org/bot<bot_token>/setWebhook.
4. Добавить в бота кнопки ```/clear_context```, ```/rewrite```, ```/model```, ```/info```

## Настройка OpenAI
Настройка производится в файле ```config/openai-models.php```, можно задавать настройки для моделей генерации OpenAI, а также максимально возможного числа токенов которое расходуется за одну генерацию. По умолчанию установлены пределы моделей. Предел считается по формуле ```размер входящего сообщения + размер контекста + максимальный размер ответа = предел модели```, размеры, в данном случае исчисляются в токенах.

## Дополнительно
- Обратите внимание на ограничение времени выполнения PHP на Вашем сервере, если оно установлено слишком маленьким, Open AI API не будет успевать генерировать ответ, особенно характерно для рерайтинга. 
- При отправке боту достаточно длинного сообщения для рерайтинга, он будет обрабатывать его довольно долго, по моему опыту, максимум было 20 минут.
- Генерация сообщений реализовала через Laravel jobs, имеется возможность подключить очереди.
