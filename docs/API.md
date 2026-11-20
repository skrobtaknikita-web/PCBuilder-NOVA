# ПР6–ПР10 — REST API
Базовый адрес: `http://127.0.0.1:8000/api`. Все запросы отправляют `Accept: application/json`; закрытые маршруты — `Authorization: Bearer <token>`.

| Метод | URL | Контроллер / действие | Доступ | Успех | Ошибки |
|---|---|---|---|---|---|
| GET | /health | Closure | Все | 200 | 503 |
| POST | /register | AuthController@register | Гость | 201 | 422 |
| POST | /login | AuthController@login | Гость | 200 | 401,422 |
| GET | /user | AuthController@me | Пользователь | 200 | 401 |
| POST | /logout | AuthController@logout | Пользователь | 204 | 401 |
| GET | /categories | CategoryController@index | Все | 200 | 500 |
| GET | /categories/{category} | CategoryController@show | Все | 200 | 404 |
| POST/PATCH/DELETE | /categories[/{category}] | CategoryController@store/update/destroy | Администратор | 201/200/204 | 401,403,409,422 |
| GET | /components | ComponentController@index | Все | 200 | 422 |
| GET | /components/{component} | ComponentController@show | Все | 200 | 404 |
| POST/PATCH/DELETE | /components[/{component}] | ComponentController@store/update/destroy | Администратор | 201/200/204 | 401,403,404,409,422 |
| GET | /configurations | ConfigurationController@index | Пользователь | 200 | 401 |
| GET | /configurations/{configuration} | ConfigurationController@show | Владелец | 200 | 401,403,404 |
| POST | /configurations | ConfigurationController@store | Пользователь | 201 | 401,409,422 |
| PUT/PATCH | /configurations/{configuration} | ConfigurationController@update | Владелец | 200 | 401,403,409,422 |
| DELETE | /configurations/{configuration} | ConfigurationController@destroy | Владелец | 204 | 401,403,404 |
| GET/POST/PUT/PATCH/DELETE | /compatibility-rules[/{compatibilityRule}] | CompatibilityRuleController CRUD | Администратор | 200/201/204 | 401,403,404,422 |
| GET | /reports/summary | ReportController@summary | Администратор | 200 | 401,403 |

Тела: регистрация `{ "name":"Иван", "email":"ivan@example.test", "password":"Password123!", "password_confirmation":"Password123!" }`; вход `{ "email":"ivan@example.test", "password":"Password123!" }`; создание сборки `{ "name":"Игровой ПК", "items":[{"component_id":1,"quantity":1},{"component_id":3,"quantity":1}] }`; создание товара `{ "category_id":1,"name":"CPU","brand":"AMD","price":95000,"stock":10 }`. Список: `/components?category_id=1&search=Ryzen&sort=price&order=desc&page=1&per_page=10`.

Ошибки: `{"message":"Ошибка валидации","errors":{"price":["..." ]}}` (422); `{"message":"Сокет процессора не совпадает с материнской платой"}` (409). Успешная выдача одного ресурса обёрнута в `data`.
