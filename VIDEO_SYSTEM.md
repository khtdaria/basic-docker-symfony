# Система відео: інструкція з використання

## Архітектура

```
Admin завантажує відео → зберігається в storage/videos/{uuid}/original.ext
                        → VideoStatus: pending
                        → ProcessVideoMessage диспетчується

Worker (messenger:consume) → ProcessVideoMessageHandler
  → ffmpeg HLS tranскодування (max 1080p, veryfast)    → storage/videos/{uuid}/hls/playlist.m3u8
  → ffmpeg preview frame                               → storage/videos/{uuid}/preview.jpg
  → ffprobe metadata (тривалість, кодек)
  → VideoStatus: ready
  → nginx роздає /storage/ статично

VR пристрій → GET /api/v1/videos → HLS URL для стрімінгу
```

---

## Запуск проекту

```bash
# Запустити DDEV (перший раз або після зупинки)
ddev start

# Перевірити статус усіх сервісів
ddev describe

# Зупинити
ddev stop

# Перезапустити (після змін у конфіги, php.ini, .env)
ddev restart
```

Після `ddev start` автоматично стартують:
- nginx + php-fpm (веб-сервер)
- MariaDB 11.8 (база даних)
- Messenger worker (обробка відео у фоні)
- Mailpit (пошта у дев)

---

## Як завантажити відео

1. Відкрити **https://basic-docker-symfony.ddev.site/admin/**
2. Увійти як адмін
3. Розділ **Videos** → кнопка **Create**
4. Заповнити Title, Description, вибрати файл (mp4, mov, avi, mkv, webm, до 4 ГБ)
5. **Save** — відео збережеться зі статусом `pending`, далі воркер обробить його асинхронно

> **Примітка:** Обробка запускається у фоні. Великий файл (~1 ГБ, 8 хв) займе 3–5 хвилин.
> Оновіть сторінку через кілька хвилин або перевірте статус вручну.

---

## Статуси відео

| Статус       | Опис |
|--------------|------|
| `pending`    | Файл завантажено, чекає на обробку в черзі |
| `processing` | Воркер почав транскодування через ffmpeg |
| `ready`      | Транскодування завершено, доступне для VR-пристроїв |
| `failed`     | Помилка під час обробки (деталі в полі `processingError`) |

### Що означає кожен статус у EasyAdmin

У списку відео статус відображається кольоровим бейджем:
- 🟡 **pending** — у черзі
- 🔵 **processing** — обробляється зараз
- 🟢 **ready** — готово до стрімінгу
- 🔴 **failed** — деталі помилки — натисніть Detail на відео

---

## Перегляд причини помилки

### Через EasyAdmin (UI)

1. Admin → Videos → знайдіть відео зі статусом `failed`
2. Натисніть іконку **Detail** (👁)
3. Поле **Processing Error** містить повне повідомлення про помилку (зокрема stderr від ffmpeg)

### Через базу даних

```bash
ddev mysql -e "SELECT HEX(id) as id, status, LEFT(processing_error, 500) as error FROM videos WHERE status='failed'"
```

### Через логи застосунку

```bash
ddev worker-logs 200
# або
ddev exec tail -200 /var/www/html/var/log/dev.log
```

---

## Логи воркера

### Метод 1: Логи Symfony (Monolog) — основний спосіб

```bash
# Останні 100 рядків
ddev worker-logs

# Останні N рядків
ddev worker-logs 300

# Стежити в реальному часі
ddev exec tail -f /var/www/html/var/log/dev.log
```

Тут видно: початок/кінець обробки, помилки ffmpeg, попередження.

### Метод 2: Docker логи контейнера (stdout воркера з -vv)

```bash
# Усі логи контейнера (включно з виводом -vv від messenger:consume)
ddev logs

# Реального часу
ddev logs -f

# Тільки останні N рядків
ddev logs --tail 200
```

### Метод 3: Supervisor статус

```bash
# Перевірити чи запущений воркер
ddev worker-status

# Перезапустити воркер (якщо завис або після змін у коді)
ddev worker-restart
```

---

## Повтор обробки відео (retry)

Якщо відео має статус `failed`, можна спробувати знову:

### Через консоль Symfony

```bash
# Показати всі повідомлення в черзі failed
ddev exec php bin/console messenger:failed:show

# Повторити конкретне повідомлення з failed-черги
ddev exec php bin/console messenger:failed:retry <id>

# Повторити всі failed повідомлення
ddev exec php bin/console messenger:failed:retry --all
```

### Вручну через SQL + командний рядок

```bash
# Змінити статус відео на pending і відправити нове повідомлення
ddev exec php bin/console app:reprocess-video <video-uuid>
```

> Якщо команда `app:reprocess-video` ще не реалізована, змініть статус у БД:
> ```bash
> ddev mysql -e "UPDATE videos SET status='pending' WHERE id=UNHEX('<hex-id>')"
> ```
> Потім вручну відправте повідомлення через EasyAdmin (редагувавши та зберігши відео повторно, або завантаживши новий файл).

---

## Перевірка обробки відео в реальному часі

```bash
# 1. Перевірити статус у БД
ddev mysql -e "SELECT HEX(id) as id, status, hls_path, LEFT(processing_error,100) as err FROM videos ORDER BY created_at DESC LIMIT 10"

# 2. Стежити за логами в реальному часі
ddev exec tail -f /var/www/html/var/log/dev.log

# 3. Перевірити, чи з'явились HLS-файли
ls storage/videos/
ls storage/videos/<uuid>/hls/ 2>/dev/null || echo "HLS ще не готовий"
```

---

## Тестування API (VR-пристрої)

```bash
# 1. Отримати JWT токен
curl -s -X POST https://basic-docker-symfony.ddev.site/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"identifier":"device@example.com","password":"password"}' | jq .

# 2. Отримати список готових відео
curl -s https://basic-docker-symfony.ddev.site/api/v1/videos \
  -H "Authorization: Bearer <TOKEN>" | jq .

# 3. Отримати конкретне відео
curl -s https://basic-docker-symfony.ddev.site/api/v1/videos/<id> \
  -H "Authorization: Bearer <TOKEN>" | jq .
```

---

## Корисні команди

```bash
# Міграції БД
ddev exec php bin/console doctrine:migrations:migrate --no-interaction

# Очистити кеш Symfony
ddev exec php bin/console cache:clear

# Завантажити фікстури (тестові дані)
ddev exec php bin/console doctrine:fixtures:load --no-interaction

# Перевірити черги повідомлень
ddev mysql -e "SELECT queue_name, COUNT(*) FROM messenger_messages GROUP BY queue_name"

# Перевірити, чи ffmpeg встановлений у контейнері
ddev exec which ffmpeg
ddev exec ffmpeg -version
```

---

## Структура файлів відео

```
storage/
└── videos/
    └── {uuid}/                  ← директорія відео (uuid відповідає id у БД)
        ├── original.mp4         ← оригінальний файл (завантажений)
        ├── preview.jpg          ← фрейм-превью (3-я секунда)
        └── hls/
            ├── playlist.m3u8    ← HLS-плейлист (цей URL йде до VR-пристроїв)
            ├── playlist0.ts     ← відео-чанки (по 10 секунд)
            ├── playlist1.ts
            └── ...
```

URL для стрімінгу: `https://basic-docker-symfony.ddev.site/storage/videos/{uuid}/hls/playlist.m3u8`

---

## Технічні деталі обробки

ffmpeg виконує:
- **Декодування** оригінального формату (AV1, H264, HEVC, VP9, тощо)
- **Масштабування** до max 1080p (якщо вхідний файл вищого розширення — зменшується; якщо нижчого — не збільшується)
- **Кодування** у H264 (`libx264`, preset `veryfast`, CRF 23)
- **Аудіо** AAC 128k
- **Упакування** у HLS по 10-секундних чанках

Орієнтовний час обробки:

| Роздільна здатність | Тривалість | Час обробки |
|---------------------|------------|-------------|
| 4K (AV1/HEVC)       | 8 хв       | ~4–6 хв     |
| 4K (H264)           | 8 хв       | ~2–3 хв     |
| 1080p               | 8 хв       | ~1–2 хв     |
| 720p                | 8 хв       | < 1 хв      |
