# Физическая модель (MySQL 8)
Основная диаграмма: `ER.dbml` (импорт в dbdiagram.io). Все таблицы содержат `id BIGINT UNSIGNED PRIMARY KEY`, `created_at` и `updated_at` (кроме служебных таблиц Laravel). Промежуточная таблица `configuration_items` содержит уникальную пару `(configuration_id, component_id)`.

| Таблица | Поля, типы, методы Laravel | Ограничения |
|---|---|---|
| users | name VARCHAR(255) string, email VARCHAR(255) string, password VARCHAR(255) string, role VARCHAR(255) string, remember_token VARCHAR(100) rememberToken | email UNIQUE, role DEFAULT user |
| categories | name VARCHAR(255) string, slug VARCHAR(255) string, description TEXT text | slug UNIQUE, description NULL |
| components | category_id BIGINT UNSIGNED foreignId, name/brand VARCHAR string, price DECIMAL(10,2) decimal, stock INT UNSIGNED unsignedInteger, socket/ram_type/form_factor VARCHAR nullable, power_watts/wattage/length_mm/max_gpu_length_mm INT UNSIGNED nullable, is_active BOOLEAN boolean | category_id RESTRICT, price > 0 проверяется Form Request, индекс category_id+price, brand |
| configurations | user_id BIGINT UNSIGNED foreignId, name VARCHAR string, status VARCHAR string, total_price DECIMAL(12,2) decimal | user_id CASCADE, status DEFAULT draft, индекс user_id+status |
| configuration_items | configuration_id/component_id BIGINT UNSIGNED foreignId, quantity INT UNSIGNED unsignedInteger | configuration_id CASCADE, component_id RESTRICT, UNIQUE (configuration_id,component_id) |
| compatibility_rules | name VARCHAR string, description TEXT text, is_active BOOLEAN boolean | name UNIQUE |

Связи: User hasMany Configuration; Configuration belongsTo User; Category hasMany Component; Component belongsTo Category; Configuration belongsToMany Component через configuration_items; Configuration hasMany ConfigurationItem; ConfigurationItem belongsTo Component и Configuration. Для обязательных связей внешние ключи NOT NULL. CASCADE применяется к личным сборкам при удалении пользователя, RESTRICT защищает справочник от удаления используемых позиций.

3НФ: атрибуты зависят от первичного ключа своей таблицы; категории вынесены отдельно; элементы сборок отделены от самих сборок; повторяющихся групп полей нет. `total_price` является намеренно сохраняемым вычисленным снимком стоимости, который обновляется транзакционно; для строгой нормализации его можно вычислять из элементов, но сохранённый снимок полезен для истории цен.

Enum: UserRole = user, admin; ConfigurationStatus = draft, valid, invalid. Поиск: индекс `(category_id,price)` и индекс `brand`; уникальный индекс email также ускоряет вход.
