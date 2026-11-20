(()=>{
const menu={
shop:[['Каталог','ПК в наличии','Игровые ПК','Эксклюзивные','Профессиональные','Аксессуары'],['Информация','Этапы заказа','Доставка','Оплата','Программа трейд-ин'],['Гарантия','Условия гарантии','Гарантийные пакеты','Политика возврата и обмена']],
gaming:[['Игровые компьютеры','Все модели','Серия PLAY','Серия LUMEN','Готовые в наличии','Конфигуратор'],['Помощник по подбору','По цене','По игре','По сценарию','По характеристикам']],
exclusive:[['Концепт компьютеры','Кастом ателье','Серия CONCEPT','Проекты','Готовые в наличии'],['Технологии','Моддинг','Премиальная упаковка']],
pro:[['Рабочие станции','Все модели','Рабочие станции','Серверы','Проекты','Конфигуратор'],['Решения по индустриям','Искусственный интеллект','Архитектура и BIM','3D и анимация','Монтаж и цветокор','Разработка'],['ИИ задачи','Машинное обучение','Обучение LLM']],
services:[['Частным лицам','Все услуги','Апгрейд','Обслуживание','Кастомизация','Трейд-ин'],['Для бизнеса','Оборудование компьютерных клубов','Наши заказчики']],
support:[['Помощь','Связаться с нами','Запись на сервис','Частые вопросы'],['Сервис','База знаний','Центр загрузок'],['Гарантия','Условия гарантии','Гарантийные пакеты','Политика возврата и обмена']],
company:[['О NOVA PC','Контакты','О компании','Наша команда','Производство','Портфолио','Карьера'],['Сотрудничество','Поддержка киберспорта'],['Блог','Новости','Статьи','Акции']]
};
const routes={'ПК в наличии':'catalog','Игровые ПК':'catalog','Аксессуары':'catalog','Все модели':'catalog','Готовые в наличии':'catalog','Конфигуратор':'builder','По цене':'builder','По игре':'ai','По сценарию':'ai','По характеристикам':'catalog','Рабочие станции':'catalog','Серверы':'catalog','Связаться с нами':'contact','Контакты':'contact','Все услуги':'contact','Запись на сервис':'contact','Апгрейд':'contact','Обслуживание':'contact','Кастомизация':'contact','Сравнение':'compare','Вход':'auth'};
const shell=document.querySelector('.nova-mega-shell'),panel=document.getElementById('novaMegaPanel'),cols=document.getElementById('novaMegaColumns');let active=null;
function close(){active=null;panel.hidden=true;document.querySelectorAll('[data-menu]').forEach(b=>{b.classList.remove('active');b.setAttribute('aria-expanded','false')})}
function open(name){if(active===name){close();return}active=name;cols.replaceChildren();menu[name].forEach((group,i)=>{let section=document.createElement('div');section.className='nova-mega-group'+(i===0?' featured':'');let cap=document.createElement('div');cap.className='nova-mega-caption';cap.textContent=group[0];section.append(cap);group.slice(1).forEach(item=>{let btn=document.createElement('button');btn.type='button';btn.className='nova-mega-link';btn.textContent=item;btn.addEventListener('click',()=>navigate(item));section.append(btn)});cols.append(section)});panel.hidden=false;document.querySelectorAll('[data-menu]').forEach(b=>{b.classList.toggle('active',b.dataset.menu===name);b.setAttribute('aria-expanded',String(b.dataset.menu===name))})}
const details={
'Этапы заказа':['Выбор комплектующих','Подберите компоненты в каталоге, проверьте совместимость, сохраните конфигурацию и свяжитесь с нами для уточнения заказа. Автоматическое оформление заказа пока не подключено.'],
'Доставка':['Доставка','Условия, сроки и стоимость доставки уточняются по контактам NOVA. Онлайн-расчёт доставки пока недоступен.'],
'Оплата':['Оплата','Для согласования способов оплаты свяжитесь с NOVA. Приём платежей на сайте не подключён.'],
'Программа трейд-ин':['Трейд-ин','Расскажите о вашем старом ПК, его состоянии и желаемом обновлении. Оценка проводится вручную.'],
'Условия гарантии':['Гарантия','Условия гарантии зависят от конкретного товара и продавца. Уточните документы и сроки до покупки.'],
'Гарантийные пакеты':['Гарантийные пакеты','Пакеты расширенного обслуживания не подключены. Запросите актуальные условия по контактам.'],
'Политика возврата и обмена':['Возврат и обмен','Условия возврата определяются применимым законодательством и условиями конкретной продажи. Уточните порядок до заказа.'],
'Частые вопросы':['Частые вопросы','Как подобрать ПК? Откройте конфигуратор и задайте бюджет. Как проверить совместимость? Добавьте детали в сборку и запустите проверку. Где узнать цену? В каталоге цены демонстрационные.'],
'База знаний':['База знаний','Сокет процессора должен соответствовать материнской плате; тип DDR4/DDR5 должен совпадать; учитывайте длину видеокарты, высоту кулера и запас мощности БП.'],
'Центр загрузок':['Центр загрузок','Драйверы скачивайте с официальных сайтов производителей компонентов. Уточните модель оборудования перед установкой.'],
'О компании':['О NOVA','NOVA — учебный проект конфигуратора компьютеров. Демонстрационные цены не являются публичным предложением.'],
'Наша команда':['Команда','Информация о команде пока не опубликована. Для связи используйте указанные контакты.'],
'Производство':['Производство','Информация о производстве не подтверждена. Для вопросов по сборке и обслуживанию свяжитесь с нами.'],
'Карьера':['Карьера','Отправьте запрос о сотрудничестве по электронной почте.'],
'Новости':['Новости','Новости проекта пока не опубликованы.'],
'Статьи':['Статьи','Материалы о подборе компонентов и совместимости доступны в разделе «База знаний».'],
'Акции':['Акции','Подтверждённых акций сейчас нет. Цены каталога демонстрационные.']
};
function infoPage(item){const section=document.getElementById('novaInfoPage');if(!section)return;const data=details[item]||[item,'Раздел NOVA: для консультации, подбора или индивидуального запроса свяжитесь с нами. Возможности коммерческого сервиса пока не подключены.'];section.querySelector('h2').textContent=data[0];section.querySelector('.nova-info-text').textContent=data[1];section.querySelector('.nova-info-email').href='mailto:hiki9164@gmail.com?subject='+encodeURIComponent('NOVA: '+item);section.querySelector('.nova-info-email').textContent='Написать по теме «'+item+'»';if(typeof show==='function')show('novaInfoPage');else document.querySelectorAll('main section').forEach(x=>x.hidden=x!==section);document.querySelector('main')?.scrollIntoView({behavior:'smooth'});}
function navigate(item){close();shell.classList.remove('mobile-open');let target=routes[item];if(target==='contact'){infoPage(item);return}if(!target){infoPage(item);return}let b={catalog:'catalogBtn',builder:'buildBtn',ai:'aiBtn',compare:'compareBtn',auth:'loginBtn'}[target];document.getElementById(b)?.click();document.querySelector('main')?.scrollIntoView({behavior:'smooth'})}
document.querySelectorAll('[data-menu]').forEach(b=>{b.setAttribute('aria-expanded','false');b.addEventListener('click',()=>open(b.dataset.menu))});document.querySelectorAll('[data-go]').forEach(b=>b.addEventListener('click',e=>{e.preventDefault();navigate(({catalog:'Все модели',builder:'Конфигуратор',compare:'Сравнение',auth:'Вход'})[b.dataset.go]||'Все модели')}));document.getElementById('novaMobileToggle')?.addEventListener('click',()=>shell.classList.toggle('mobile-open'));document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});document.addEventListener('click',e=>{if(!shell.contains(e.target))close()});
})();