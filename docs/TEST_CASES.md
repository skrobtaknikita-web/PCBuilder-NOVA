# ПР10 — Тест-кейсы
Фактический результат: **не выполнялось в данной среде** (Composer и MySQL недоступны). После запуска вписать реальный результат, приложить скриншоты Postman и `php artisan test`.

| № | Запрос | Входные данные | Ожидаемый результат | Фактический |
|---|---|---|---|---|
| 1 | GET /api/health | — | 200, database=ok | Не проверено |
| 2 | POST /api/register | корректные поля | 201, токен | Не проверено |
| 3 | POST /api/register | email=bad | 422 | Не проверено |
| 4 | POST /api/login | правильный пароль | 200, токен | Не проверено |
| 5 | POST /api/login | неправильный пароль | 401 | Не проверено |
| 6 | GET /api/components | — | 200, каталог | Не проверено |
| 7 | GET /api/components?search=Ryzen | — | 200, отфильтровано | Не проверено |
| 8 | GET /api/components?per_page=5 | — | meta.per_page=5 | Не проверено |
| 9 | GET /api/components/999999 | — | 404 | Не проверено |
| 10 | POST /api/configurations | без токена | 401 | Не проверено |
| 11 | POST /api/configurations | имя сборки, токен | 201 | Не проверено |
| 12 | GET /api/configurations/{id} | чужая запись | 403 | Не проверено |
| 13 | POST /api/components | токен пользователя | 403 | Не проверено |
| 14 | POST /api/components | токен админа | 201 | Не проверено |
| 15 | POST /api/components | price=-1 | 422 | Не проверено |
| 16 | POST /api/configurations | CPU AM5 + MB AM4 | 409 | Не проверено |
| 17 | POST /api/configurations | DDR4 + MB DDR5 | 409 | Не проверено |
| 18 | POST /api/configurations | слабый БП | 409 | Не проверено |
| 19 | POST /api/configurations | длинная GPU, короткий корпус | 409 | Не проверено |
| 20 | DELETE /api/components/{id} | используется в сборке | 409 | Не проверено |
