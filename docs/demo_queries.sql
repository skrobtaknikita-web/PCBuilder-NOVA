-- Данные для демонстрации: основной источник — php artisan migrate:fresh --seed
SELECT c.id,c.name,cat.name AS category,c.price FROM components c JOIN categories cat ON cat.id=c.category_id ORDER BY c.id LIMIT 10;
SELECT u.name,cf.name AS configuration,cf.status,cf.total_price FROM configurations cf JOIN users u ON u.id=cf.user_id ORDER BY cf.id LIMIT 10;
SELECT cf.name AS configuration,c.name AS component,ci.quantity FROM configuration_items ci JOIN configurations cf ON cf.id=ci.configuration_id JOIN components c ON c.id=ci.component_id ORDER BY cf.id;
SELECT cat.name,COUNT(c.id) AS components_count FROM categories cat LEFT JOIN components c ON c.category_id=cat.id GROUP BY cat.id,cat.name ORDER BY components_count DESC;
SELECT u.name,COUNT(cf.id) AS configurations_count FROM users u LEFT JOIN configurations cf ON cf.user_id=u.id GROUP BY u.id,u.name;
