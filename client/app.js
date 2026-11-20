const API='http://127.0.0.1:8000/api';let token=sessionStorage.getItem('token');let currentUser=null;let catalog=[];let chosen=new Map();let page=1;let catalogRequest=0;let categoriesCache=[];
const $=id=>document.getElementById(id);const msg=s=>$('message').textContent=s;
async function request(path,options={}){let headers={'Accept':'application/json',...(options.body?{'Content-Type':'application/json'}:{}),...(token?{'Authorization':'Bearer '+token}:{})};let response;try{response=await fetch(API+path,{...options,headers});}catch(e){throw Error('Сервер недоступен. Запустите Laravel.')}if(response.status===401&&path!='/login'){sessionStorage.removeItem('token');token=null;show('auth');throw Error('Войдите заново');}if(response.status===204)return null;const data=await response.json();if(!response.ok)throw Error(data.message+(data.errors?' — '+Object.values(data.errors).flat().join('; '):''));return data;}
function show(id){['auth','catalog','builder','admin','ai','favorites','compare','novaInfoPage','readyBuilds'].forEach(x=>$(x).hidden=x!==id);msg('');}
async function loadCategories(){let r=await request('/categories');categoriesCache=r.data;for(let select of [$('category'),$('adminCategory')]){select.innerHTML=select.id==='category'?'<option value="">Все категории</option>':'';r.data.forEach(c=>{let opt=document.createElement('option');opt.value=c.id;opt.textContent=c.name;select.appendChild(opt);});}renderCategoryChips();}
async function loadCatalog(){const seq=++catalogRequest;$('resultsCount').textContent='Загрузка…';const q=new URLSearchParams({page,per_page:24,sort:$('sort').value,order:$('order').value});if($('maxPrice').value)q.set('max_price',$('maxPrice').value);if($('search').value)q.set('search',$('search').value);if($('category').value)q.set('category_id',$('category').value);const r=await request('/components?'+q);if(seq!==catalogRequest)return;catalog=r.data;const count=r.meta?.total??catalog.length;$('resultsCount').textContent='Найдено товаров: '+Number(count).toLocaleString('ru-RU');$('catalogCount').textContent=Number(count).toLocaleString('ru-RU')+'+';$('products').replaceChildren();for(const c of catalog.filter(c=>!$('onlyStock').checked||Number(c.stock)>0)){const el=document.createElement('article');el.className='card';const visual=document.createElement('div');visual.className='product-visual';visual.innerHTML=productArt(c.category?.slug,c.name,c.brand);visual.setAttribute('aria-label',c.category?.name||'Комплектующее');const h=document.createElement('h3');h.textContent=c.name;const badge=document.createElement('span');badge.className='stock-badge';badge.textContent=Number(c.stock)>0?'● Демо-остаток':'○ Нет в наличии';const brand=document.createElement('p');brand.className='muted';brand.textContent=c.brand+' · '+(c.category?.name||'');const price=document.createElement('p');price.className='price';price.textContent=Number(c.price).toLocaleString('ru-RU')+' ₸';const btn=document.createElement('button');btn.textContent='＋ В сборку';btn.disabled=Number(c.stock)<=0;btn.onclick=()=>{chosen.set(c.category_id,c);renderChosen();msg('Добавлено: '+c.name);btn.textContent='✓ Добавлено';};const specs=document.createElement('p');specs.className='specs';specs.textContent=[c.socket,c.ram_type,c.form_factor,c.wattage?c.wattage+' Вт':null,c.length_mm?c.length_mm+' мм':null].filter(Boolean).join(' · ')||'В наличии: '+c.stock;const actions=document.createElement('div');actions.className='product-actions';const more=document.createElement('button');more.className='ghost';more.textContent='Подробнее';more.onclick=()=>openProductModal(c);const fav=document.createElement('button');fav.className='ghost mini-action';fav.title='В избранное';fav.textContent=favoriteIds.has(String(c.id))?'♥':'♡';fav.onclick=()=>{toggleFavorite(c);fav.textContent=favoriteIds.has(String(c.id))?'♥':'♡'};const cmp=document.createElement('button');cmp.className='ghost mini-action';cmp.title='Сравнить';cmp.textContent=compareIds.has(String(c.id))?'✓':'⇄';cmp.onclick=()=>{toggleCompare(c);cmp.textContent=compareIds.has(String(c.id))?'✓':'⇄'};actions.append(btn,more,fav,cmp);el.append(visual,badge,h,brand,specs,price,actions);$('products').append(el);}const p=$('pagination');p.replaceChildren();const prev=document.createElement('button');prev.textContent='Назад';prev.disabled=page<=1;prev.onclick=()=>{page--;loadCatalog()};const next=document.createElement('button');next.textContent='Вперёд';next.disabled=page>=Math.max(1,r.meta?.last_page||1);next.onclick=()=>{page++;loadCatalog()};p.append(prev,document.createTextNode(' Страница '+page+' из '+Math.max(1,r.meta?.last_page||1)+' '),next);}
function renderChosen(){let box=$('selected');box.replaceChildren();let total=0;for(const [category,c] of chosen){total+=Number(c.price);const row=document.createElement('div');row.className='selected-item';row.textContent=c.name+' — '+Number(c.price).toLocaleString('ru-RU')+' ₸ ';const remove=document.createElement('button');remove.textContent='Удалить';remove.onclick=()=>{chosen.delete(category);renderChosen()};row.append(remove);box.append(row);}$('total').textContent='Итого: '+total.toLocaleString('ru-RU')+' ₸';updateBuildDashboard(total);checkCompatibility();}
async function auth(register){try{const body={email:$('email').value,password:$('password').value};if(register){body.name=$('name').value;body.password_confirmation=body.password;}const r=await request(register?'/register':'/login',{method:'POST',body:JSON.stringify(body)});token=r.token;sessionStorage.setItem('token',token);await me();show('catalog');msg('Вход выполнен');}catch(e){msg(e.message)}}
async function me(){if(!token)return;try{let r=await request('/user');currentUser=r.data;$('loginBtn').textContent=currentUser.name+' ('+currentUser.role+')';$('logoutBtn').hidden=false;$('adminBtn').hidden=currentUser.role!=='admin';}catch(e){msg(e.message)}}
async function save(){if(!token){show('auth');return}try{let r=await request('/configurations',{method:'POST',body:JSON.stringify({name:$('buildName').value,items:[...chosen.values()].map(c=>({component_id:c.id,quantity:1}))})});msg('Сборка сохранена! ID: '+r.data.id);await builds();}catch(e){msg(e.message)}}
async function builds(){if(!token)return;let r=await request('/configurations');$('myBuilds').replaceChildren();for(const c of r.data){let el=document.createElement('div');el.className='selected-item';el.textContent=c.name+' — '+Number(c.total_price).toLocaleString('ru-RU')+' ₸ ('+c.status+')';$('myBuilds').append(el)}}
$('doLogin').onclick=()=>auth(false);$('doRegister').onclick=()=>auth(true);$('loginBtn').onclick=()=>show('auth');$('catalogBtn').onclick=()=>{show('catalog');loadCatalog().catch(e=>msg(e.message))};$('buildBtn').onclick=()=>{show('builder');builds().catch(e=>msg(e.message))};$('apply').onclick=()=>{page=1;loadCatalog().catch(e=>msg(e.message))};$('saveBuild').onclick=save;
$('adminBtn').onclick=()=>show('admin');$('logoutBtn').onclick=async()=>{try{await request('/logout',{method:'POST'})}catch(e){}token=null;currentUser=null;sessionStorage.removeItem('token');$('logoutBtn').hidden=true;$('adminBtn').hidden=true;$('loginBtn').textContent='Вход';show('catalog')};
$('adminCreate').onclick=async()=>{try{await request('/components',{method:'POST',body:JSON.stringify({name:$('adminName').value,brand:$('adminBrand').value,price:Number($('adminPrice').value),stock:Number($('adminStock').value),category_id:Number($('adminCategory').value)})});msg('Комплектующее создано');}catch(e){msg(e.message)}};
loadCategories().then(()=>{ $('category').value=''; page=1; renderCategoryChips(); return loadCatalog(); }).catch(e=>msg(e.message));me();

function checkCompatibility(){
 const all=[...chosen.values()],by=Object.fromEntries(all.map(c=>[c.category?.slug,c]));const issues=[],notes=[];
 if(by.cpu&&by.motherboard&&by.cpu.socket!==by.motherboard.socket)issues.push('Сокет процессора и материнской платы не совпадает');
 if(by.ram&&by.motherboard&&by.ram.ram_type!==by.motherboard.ram_type)issues.push('Тип RAM не поддерживается материнской платой');
 if(by.gpu&&by.case&&by.gpu.length_mm>by.case.max_gpu_length_mm)issues.push('Видеокарта не помещается в корпус');
 const power=all.reduce((sum,c)=>sum+(Number(c.power_watts)||0),0);
 if(by.psu&&by.psu.wattage<power*1.2)issues.push('Недостаточная мощность БП — нужен запас минимум 20%');
 if(!by.cpu||!by.motherboard||!by.ram||!by.gpu||!by.psu||!by.case)notes.push('Для полной проверки выберите CPU, плату, RAM, GPU, БП и корпус');
 const el=$('compatibility');el.className='compatibility '+(issues.length?'danger':'good');el.textContent=issues.length?'⚠ '+issues.join(' • '):'✓ Критических конфликтов среди выбранных компонентов не найдено';
 if(notes.length)el.textContent+=' · '+notes.join('');
 if(by.psu)el.textContent+=' · Оценка потребления: '+power+' Вт / БП '+by.psu.wattage+' Вт';
}
$('clearBuild').onclick=()=>{chosen.clear();renderChosen();msg('Сборка очищена')};
$('heroStart').onclick=()=>{show('builder');window.scrollTo({top:500,behavior:'smooth'})};
$('heroCatalog').onclick=()=>{show('catalog');window.scrollTo({top:500,behavior:'smooth'})};
$('search').addEventListener('keydown',e=>{if(e.key==='Enter')$('apply').click()});
checkCompatibility();

// NOVA AI: cloud model via Laravel, transparent offline rules fallback.
$('aiBtn').onclick=()=>show('ai');
function aiLine(who,content){const d=document.createElement('div');d.className='ai-line '+(who==='Вы'?'ai-user':'ai-assistant');const b=document.createElement('strong');b.textContent=who;const t=document.createElement('p');t.textContent=content;d.append(b,t);$('aiHistory').append(d);d.scrollIntoView({block:'nearest',behavior:'smooth'});}
function offlineAdvice(question){const all=[...chosen.values()],by=Object.fromEntries(all.map(c=>[c.category?.slug,c]));const q=question.toLowerCase();let response='Локальный справочный режим (без облачной модели). ';if(/совмест|проверк|сборк/.test(q)){const issues=[];if(by.cpu&&by.motherboard&&by.cpu.socket!==by.motherboard.socket)issues.push('сокет CPU и платы не совпадает');if(by.ram&&by.motherboard&&by.ram.ram_type!==by.motherboard.ram_type)issues.push('тип памяти несовместим');if(by.gpu&&by.case&&Number(by.gpu.length_mm)>Number(by.case.max_gpu_length_mm))issues.push('GPU длиннее ограничения корпуса');const draw=all.reduce((v,c)=>v+(Number(c.power_watts)||0),0);if(by.psu&&Number(by.psu.wattage)<draw*1.2)issues.push('недостаточный запас мощности БП');response+=issues.length?'Обнаружены конфликты: '+issues.join('; ')+'.':'В выбранных компонентах очевидных конфликтов по доступным полям не найдено.';response+=' Проверьте также BIOS, крепление кулера, разъёмы питания и физические зазоры.';}else if(/fps|игр|видеокарт|производительн/.test(q)){response+='Для игр обычно важнее видеокарта; при ограниченном бюджете сравните GPU и CPU, учитывая разрешение монитора. Точные FPS без бенчмарков назвать нельзя.';}else if(/бюджет|цен|тенге|₸/.test(q)){response+='Для игровой сборки можно ориентировочно выделить 35–45% бюджета на GPU, 15–25% на CPU и плату, оставив средства на память, SSD, БП, корпус и охлаждение.';}else{response+='Могу проверить базовую совместимость выбранных деталей, подсказать распределение бюджета и приоритеты для игрового ПК. Для свободных ответов подключите облачный ИИ.';}return response;}
async function askAI(){const q=$('aiQuestion').value.trim();if(q.length<3){msg('Напишите вопрос длиной от 3 символов');return;}aiLine('Вы',q);$('aiQuestion').value='';$('aiSend').disabled=true;$('aiSend').textContent='Думаю…';try{const build=[...chosen.values()].map(c=>c.name);const r=await request('/ai/advise',{method:'POST',body:JSON.stringify({question:q,build})});aiLine(r.mode==='local'?'NOVA помощник (локальный)':'NOVA AI (облако)',r.answer);}catch(e){aiLine('NOVA помощник',offlineAdvice(q)+' (Облачный ИИ недоступен: '+e.message+')');}finally{$('aiSend').disabled=false;$('aiSend').textContent='Спросить ИИ';}}
$('aiSend').onclick=askAI;
document.querySelectorAll('[data-question]').forEach(b=>b.onclick=()=>{$('aiQuestion').value=b.dataset.question;askAI();});

// NOVA 4: reactive categories, safer paging, export, automatic budget suggestions.
const artIcons={cpu:'▣',gpu:'◈',ram:'▥',motherboard:'▦',storage:'▤',psu:'⏚',case:'▯',cooler:'❄',fan:'✺'};
// Exact-SKU photography only. No generic stock photography masquerading as a product.
const novaVerifiedPhotos={};
fetch('assets/verified-photos.json',{cache:'no-store'}).then(r=>r.ok?r.json():{}).then(data=>{Object.assign(novaVerifiedPhotos,data);novaWatchPhotos();document.querySelectorAll('.component-art').forEach(n=>novaPhotoObserver.observe(n))}).catch(()=>{});
function productArt(slug,name='',brand=''){
 const safe=Object.prototype.hasOwnProperty.call(artIcons,slug)?slug:'case';
 const search=encodeURIComponent([brand,name].filter(Boolean).join(' '));
 return `<div class="component-art art-${safe}" data-nova-name="${encodeURIComponent(name)}" data-nova-brand="${encodeURIComponent(brand)}" data-nova-slug="${safe}"><img src="assets/${safe}.svg" loading="lazy" alt="Фотография модели пока не подтверждена"/><div class="art-shade"></div><span class="art-caption">ФОТО МОДЕЛИ НЕ НАЙДЕНО</span><a class="nova-find-photo" href="https://www.google.com/search?tbm=isch&q=${search}" target="_blank" rel="noopener noreferrer" title="Найти реальные фотографии точной модели">Найти фото ↗</a></div>`;
}
function renderCategoryChips(){const box=$('categoryChips');box.replaceChildren();const entries=[{id:'',name:'Все компоненты'},...categoriesCache];for(const c of entries){const b=document.createElement('button');b.className='chip'+(String($('category').value)===String(c.id)?' active':'');b.textContent=c.name;b.onclick=()=>{$('category').value=c.id;page=1;renderCategoryChips();loadCatalog().catch(e=>msg(e.message))};box.append(b)}}
$('category').addEventListener('change',()=>{page=1;renderCategoryChips();loadCatalog().catch(e=>msg(e.message))});
$('sort').addEventListener('change',()=>{page=1;loadCatalog().catch(e=>msg(e.message))});
$('order').addEventListener('change',()=>{page=1;loadCatalog().catch(e=>msg(e.message))});
let searchTimer;$('search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>{page=1;loadCatalog().catch(e=>msg(e.message))},350)});
$('openCatalog').onclick=()=>{show('catalog');loadCatalog().catch(e=>msg(e.message))};
function buildExport(){const items=[...chosen.values()];return {project:'PCBuilder NOVA 9.0',name:$('buildName').value,created_at:new Date().toISOString(),currency:'KZT',total:items.reduce((s,x)=>s+Number(x.price),0),items:items.map(x=>({id:x.id,name:x.name,category:x.category?.name,price:Number(x.price),brand:x.brand}))};}
$('exportBuild').onclick=()=>{const blob=new Blob([JSON.stringify(buildExport(),null,2)],{type:'application/json'});const url=URL.createObjectURL(blob);const a=document.createElement('a');a.href=url;a.download='nova-build.json';a.click();setTimeout(()=>URL.revokeObjectURL(url),1000)};
$('shareBuild').onclick=async()=>{const d=buildExport();const content=d.name+'\n'+d.items.map(x=>x.category+': '+x.name+' — '+x.price.toLocaleString('ru-RU')+' ₸').join('\n')+'\nИтого: '+d.total.toLocaleString('ru-RU')+' ₸';try{await navigator.clipboard.writeText(content);msg('Список скопирован')}catch(e){msg('Копирование недоступно в этом браузере')}};
async function getCategoryProducts(slug){const category=categoriesCache.find(x=>x.slug===slug);if(!category)return [];const q=new URLSearchParams({category_id:category.id,per_page:100,sort:'price',order:'asc'});const response=await request('/components?'+q);return response.data.filter(x=>Number(x.stock)>0)}
function pickUnder(items,limit,filter=()=>true){return [...items].filter(x=>Number(x.price)<=limit&&filter(x)).sort((a,b)=>Number(b.price)-Number(a.price))[0]||null}
$('autoBuild').onclick=async()=>{const button=$('autoBuild'),budget=Number($('budget').value),gaming=$('purpose').value==='gaming';if(!Number.isFinite(budget)||budget<100000){msg('Укажите бюджет от 100 000 ₸');return}button.disabled=true;button.textContent='Подбираем…';try{
 const slugs=['cpu','motherboard','ram','gpu','storage','psu','case','cooler'];const lists=await Promise.all(slugs.map(getCategoryProducts));const data=Object.fromEntries(slugs.map((s,i)=>[s,lists[i]]));const ratio=gaming?{cpu:.19,motherboard:.13,ram:.10,gpu:.34,storage:.09,psu:.08,case:.05,cooler:.02}:{cpu:.26,motherboard:.17,ram:.15,gpu:.10,storage:.13,psu:.10,case:.06,cooler:.03};
 let result=new Map();const cpu=pickUnder(data.cpu,budget*ratio.cpu);if(!cpu)throw Error('Не найден процессор в указанном бюджете');result.set('cpu',cpu);
 const mb=pickUnder(data.motherboard,budget*ratio.motherboard,x=>x.socket===cpu.socket);if(!mb)throw Error('Не найдена материнская плата под сокет '+cpu.socket);result.set('motherboard',mb);
 const ram=pickUnder(data.ram,budget*ratio.ram,x=>x.ram_type===mb.ram_type);if(!ram)throw Error('Не найдена подходящая оперативная память');result.set('ram',ram);
 for(const slug of ['gpu','storage','psu','case','cooler']){let list=data[slug];let item=pickUnder(list,budget*ratio[slug]);if(item)result.set(slug,item)}
 const by=Object.fromEntries(result);if(by.gpu&&by.case&&Number(by.gpu.length_mm)>Number(by.case.max_gpu_length_mm)){const candidate=pickUnder(data.case,budget*ratio.case,x=>Number(x.max_gpu_length_mm)>=Number(by.gpu.length_mm));if(candidate)result.set('case',candidate);else result.delete('gpu')}
 let total=[...result.values()].reduce((s,x)=>s+Number(x.price),0);if(total>budget)throw Error('Подбор вышел за бюджет — попробуйте увеличить сумму');
 const power=[...result.values()].reduce((s,x)=>s+Number(x.power_watts||0),0);const psu=pickUnder(data.psu,Math.max(0,budget-total+Number(result.get('psu')?.price||0)),x=>Number(x.wattage)>=power*1.2);if(psu)result.set('psu',psu);else result.delete('psu');
 chosen=new Map([...result.values()].map(x=>[x.category_id,x]));renderChosen();show('builder');msg('Предварительная сборка подобрана. Проверьте комплектность, BIOS, разъёмы и физические размеры.');
 }catch(e){msg(e.message)}finally{button.disabled=false;button.textContent='Подобрать'}};

// NOVA 8: client-side favourites, comparison and detailed cards. No backend schema changes.
const favoriteIds=new Set(JSON.parse(localStorage.getItem('nova-favorites')||'[]'));
const compareIds=new Set(JSON.parse(localStorage.getItem('nova-compare')||'[]'));
const savedProducts=new Map(JSON.parse(localStorage.getItem('nova-products')||'[]'));
function rememberProduct(c){savedProducts.set(String(c.id),c);localStorage.setItem('nova-products',JSON.stringify([...savedProducts].slice(-300)));}
function updateCounts(){ $('favoritesCount').textContent=favoriteIds.size;$('compareCount').textContent=compareIds.size; }
function toggleFavorite(c){const id=String(c.id);rememberProduct(c);favoriteIds.has(id)?favoriteIds.delete(id):favoriteIds.add(id);localStorage.setItem('nova-favorites',JSON.stringify([...favoriteIds]));updateCounts();}
function toggleCompare(c){const id=String(c.id);if(!compareIds.has(id)&&compareIds.size>=4){msg('Для сравнения можно выбрать не больше 4 товаров');return;}rememberProduct(c);compareIds.has(id)?compareIds.delete(id):compareIds.add(id);localStorage.setItem('nova-compare',JSON.stringify([...compareIds]));updateCounts();}
const escapeHTML=s=>String(s??'—').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
const formatMoney=n=>Number(n||0).toLocaleString('ru-RU')+' ₸';
function detailRows(c){return [['Категория',c.category?.name],['Бренд',c.brand],['Цена',formatMoney(c.price)],['Наличие',Number(c.stock)>0?'В наличии':'Нет в наличии'],['Сокет',c.socket],['Тип RAM',c.ram_type],['Форм-фактор',c.form_factor],['Потребление',c.power_watts?c.power_watts+' Вт':null],['Мощность БП',c.wattage?c.wattage+' Вт':null],['Длина',c.length_mm?c.length_mm+' мм':null],['Макс. длина GPU',c.max_gpu_length_mm?c.max_gpu_length_mm+' мм':null]].filter(([k,v])=>v!==null&&v!==undefined&&v!=='');}
function openProductModal(c){const modal=$('productModal');$('modalBody').innerHTML=`<div class="modal-visual">${productArt(c.category?.slug,c.name,c.brand)}</div><span class="eyebrow">${escapeHTML(c.brand)}</span><h2 id="modalTitle">${escapeHTML(c.name)}</h2><p class="price">${formatMoney(c.price)}</p><div class="detail-grid">${detailRows(c).map(([k,v])=>`<div><span>${escapeHTML(k)}</span><strong>${escapeHTML(v)}</strong></div>`).join('')}</div><p class="muted">Изображение иллюстрирует тип комплектующего и может отличаться от конкретной модели.</p>`;const b=document.createElement('button');b.textContent='＋ Добавить в сборку';b.onclick=()=>{chosen.set(c.category_id,c);renderChosen();modal.hidden=true;msg('Добавлено: '+c.name)};$('modalBody').append(b);modal.hidden=false;}
$('modalClose').onclick=()=>$('productModal').hidden=true;
$('productModal').onclick=e=>{if(e.target===$('productModal'))$('productModal').hidden=true;};
document.addEventListener('keydown',e=>{if(e.key==='Escape')$('productModal').hidden=true;});
function renderFavorites(){const box=$('favoriteItems');box.replaceChildren();for(const id of favoriteIds){const c=savedProducts.get(id);if(!c)continue;const el=document.createElement('article');el.className='card';el.innerHTML=productArt(c.category?.slug,c.name,c.brand)+`<h3>${escapeHTML(c.name)}</h3><p class="muted">${escapeHTML(c.brand)}</p><p class="price">${formatMoney(c.price)}</p>`;const add=document.createElement('button');add.textContent='＋ В сборку';add.onclick=()=>{chosen.set(c.category_id,c);renderChosen();msg('Добавлено: '+c.name)};const del=document.createElement('button');del.className='ghost';del.textContent='Удалить ♡';del.onclick=()=>{toggleFavorite(c);renderFavorites()};el.append(add,del);box.append(el);}if(!box.children.length)box.textContent='Пока пусто. Добавь товары из каталога кнопкой ♡.';}
function renderCompare(){const box=$('compareItems');const products=[...compareIds].map(id=>savedProducts.get(id)).filter(Boolean);if(!products.length){box.textContent='Пока нечего сравнивать. Добавь товары через ⇄ в каталоге.';return;}const fields=[['Модель',c=>c.name],['Бренд',c=>c.brand],['Категория',c=>c.category?.name],['Цена',c=>formatMoney(c.price)],['Сокет',c=>c.socket],['RAM',c=>c.ram_type],['Форм-фактор',c=>c.form_factor],['Потребление, Вт',c=>c.power_watts],['Мощность БП, Вт',c=>c.wattage],['Длина, мм',c=>c.length_mm],['Макс. GPU, мм',c=>c.max_gpu_length_mm]];box.innerHTML='<table><tbody>'+fields.map(([name,get])=>'<tr><th>'+escapeHTML(name)+'</th>'+products.map(c=>'<td>'+escapeHTML(get(c))+'</td>').join('')+'</tr>').join('')+'</tbody></table>';const clear=document.createElement('button');clear.className='ghost';clear.textContent='Очистить сравнение';clear.onclick=()=>{compareIds.clear();localStorage.setItem('nova-compare','[]');updateCounts();renderCompare()};box.append(clear);}
$('favoritesBtn').onclick=()=>{show('favorites');renderFavorites()};
$('compareBtn').onclick=()=>{show('compare');renderCompare()};
$('onlyStock').onchange=()=>loadCatalog().catch(e=>msg(e.message));
$('resetFilters').onclick=()=>{$('search').value='';$('category').value='';$('maxPrice').value='';$('sort').value='name';$('order').value='asc';$('onlyStock').checked=false;page=1;renderCategoryChips();loadCatalog().catch(e=>msg(e.message));};
updateCounts();

// NOVA 9: import/export, printable quotation, budget dashboard, autosave and restoration.
function updateBuildDashboard(total){
 const items=[...chosen.values()],budget=Number($('budget').value)||0;
 $('buildItemsCount').textContent=items.length+' / '+Math.max(9,categoriesCache.length);
 $('buildPower').textContent=items.reduce((sum,c)=>sum+Number(c.power_watts||0),0).toLocaleString('ru-RU')+' Вт';
 $('buildBudgetStatus').textContent=budget?formatMoney(budget-total)+(total>budget?' (превышен)':' осталось'):'Не указан';
 $('buildBudgetStatus').style.color=budget&&total>budget?'#ff6262':'';
 $('budgetFill').style.width=budget?Math.min(100,total/budget*100)+'%':'0%';
 $('budgetFill').style.background=budget&&total>budget?'#ff4c4c':'';
 try{localStorage.setItem('nova-current-build',JSON.stringify({name:$('buildName').value,ids:items.map(c=>c.id)}));}catch(e){}
}
$('budget').addEventListener('input',()=>updateBuildDashboard([...chosen.values()].reduce((n,c)=>n+Number(c.price),0)));
$('buildName').addEventListener('input',()=>updateBuildDashboard([...chosen.values()].reduce((n,c)=>n+Number(c.price),0)));
$('printBuild').onclick=()=>{
 if(!chosen.size){msg('Сначала добавь комплектующие в сборку');return;}
 const data=buildExport(),rows=data.items.map(x=>'<tr><td>'+escapeHTML(x.category)+'</td><td>'+escapeHTML(x.name)+'</td><td>'+formatMoney(x.price)+'</td></tr>').join('');
 const win=window.open('','_blank');if(!win){msg('Разреши всплывающие окна для печати');return;}
 win.document.write('<!doctype html><html lang="ru"><meta charset="utf-8"><title>NOVA — '+escapeHTML(data.name)+'</title><style>body{font:15px Arial;padding:36px;color:#171717}h1{border-bottom:3px solid #db262b;padding-bottom:15px}table{border-collapse:collapse;width:100%}td,th{padding:12px;border-bottom:1px solid #ddd;text-align:left}footer{margin-top:35px;color:#666}</style><h1>NOVA / '+escapeHTML(data.name)+'</h1><table><tr><th>Категория</th><th>Модель</th><th>Цена</th></tr>'+rows+'</table><h2>Итого: '+formatMoney(data.total)+'</h2><footer>Учебные цены. Совместимость и наличие необходимо уточнять.</footer></html>');win.document.close();win.focus();setTimeout(()=>win.print(),350);
};
$('importBuild').onclick=()=>$('importBuildFile').click();
$('importBuildFile').onchange=async e=>{
 const file=e.target.files?.[0];if(!file)return;
 try{
  if(file.size>100000)throw Error('Файл слишком большой');
  const data=JSON.parse(await file.text());
  if(!Array.isArray(data.items)||data.items.length>30)throw Error('Некорректный формат сборки');
  const ids=[...new Set(data.items.map(x=>Number(x.id)).filter(Number.isSafeInteger))];
  const products=await Promise.all(ids.map(async id=>{try{const r=await request('/components/'+id);return r.data||r}catch(e){return null}}));
  const valid=products.filter(x=>x&&x.id&&x.category_id);
  if(!valid.length)throw Error('Комплектующие из файла не найдены в каталоге');
  chosen=new Map(valid.map(c=>[c.category_id,c]));$('buildName').value=String(data.name||'Импортированная сборка').slice(0,100);
  renderChosen();show('builder');msg('Импортировано '+valid.length+' комплектующих. Цены обновлены из каталога.');
 }catch(err){msg('Ошибка импорта: '+err.message)}finally{e.target.value=''}
};
(async()=>{
 try{
  const raw=localStorage.getItem('nova-current-build');if(!raw)return;
  const data=JSON.parse(raw);if(!Array.isArray(data.ids)||!data.ids.length||data.ids.length>30)return;
  const products=await Promise.all(data.ids.map(async id=>{try{const r=await request('/components/'+Number(id));return r.data||r}catch(e){return null}}));
  const valid=products.filter(c=>c&&c.id&&c.category_id);if(!valid.length)return;
  chosen=new Map(valid.map(c=>[c.category_id,c]));$('buildName').value=String(data.name||'Мой компьютер').slice(0,100);renderChosen();
 }catch(e){}
})();


// A verified mapping is the only source automatically presented as an exact SKU image.
// Wikimedia candidates can be viewed, but never silently labelled as verified.
const novaPhotoCache=new Map();let novaPhotoPending=0;
function novaPhotoKey(name,brand){return (brand+' '+name).toLowerCase().replace(/[^\p{L}\p{N}]+/gu,' ').trim().replace(/\s+/g,' ')}
async function novaLookupPhoto(name,brand){
 const key=novaPhotoKey(name,brand);if(novaPhotoCache.has(key))return novaPhotoCache.get(key);
 const verified=novaVerifiedPhotos[key];
 if(verified&&typeof verified.url==='string'&&/^https:\/\//.test(verified.url)){
  const result={url:verified.url,source:verified.source||verified.url,verified:true};novaPhotoCache.set(key,result);return result;
 }
 // Search for candidate images, but do not assume a Wikimedia title proves exact model identity.
 const query=(brand+' '+name).replace(/[^\p{L}\p{N} +.\-]/gu,' ').slice(0,100);
 const url='https://commons.wikimedia.org/w/api.php?'+new URLSearchParams({action:'query',generator:'search',gsrsearch:'filetype:bitmap '+query,gsrnamespace:'6',gsrlimit:'5',prop:'imageinfo',iiprop:'url|mime',iiurlwidth:'450',format:'json',origin:'*'});
 try{const response=await fetch(url,{signal:AbortSignal.timeout(6500)});if(!response.ok)throw Error('Photo lookup unavailable');const data=await response.json();const pages=Object.values(data.query?.pages||{});const tokens=name.toLowerCase().split(/[^\p{L}\p{N}]+/u).filter(x=>x.length>=4);const scored=pages.map(p=>({p,score:tokens.filter(t=>p.title.toLowerCase().includes(t)).length})).sort((x,y)=>y.score-x.score);const match=scored[0];const info=match?.p?.imageinfo?.[0];const found=match?.score>=2&&info?.thumburl&&/^image\/(jpeg|png|webp)$/.test(info.mime||'')?{url:info.thumburl,source:info.descriptionurl||info.url,verified:false}:null;novaPhotoCache.set(key,found);return found;}catch(e){novaPhotoCache.set(key,null);return null;}
}
const novaPhotoObserver=new IntersectionObserver(entries=>{for(const entry of entries){if(!entry.isIntersecting)continue;const node=entry.target;novaPhotoObserver.unobserve(node);const name=decodeURIComponent(node.dataset.novaName||''),brand=decodeURIComponent(node.dataset.novaBrand||'');if(!name)continue;const run=async()=>{novaPhotoPending++;try{const result=await novaLookupPhoto(name,brand);if(!result||!node.isConnected)return;
 const img=node.querySelector('img');if(!img)return;
 if(result.verified){img.src=result.url;img.alt='Фото модели '+name;img.onerror=()=>{img.onerror=null;img.src='assets/'+(node.dataset.novaSlug||'case')+'.svg'};node.querySelector('.art-caption').textContent='ФОТО МОДЕЛИ';}
 else {const link=document.createElement('a');link.className='nova-photo-candidate';link.href=result.source;link.target='_blank';link.rel='noopener noreferrer';link.textContent='Возможное фото на Wikimedia ↗';node.append(link)}
 }finally{novaPhotoPending--;}};const wait=()=>{if(novaPhotoPending<3)run();else setTimeout(wait,250)};wait();}}, {rootMargin:'250px'});
function novaWatchPhotos(){document.querySelectorAll('.component-art:not([data-nova-watched])').forEach(node=>{node.dataset.novaWatched='1';novaPhotoObserver.observe(node)})}
new MutationObserver(novaWatchPhotos).observe(document.body,{subtree:true,childList:true});document.addEventListener('DOMContentLoaded',novaWatchPhotos);

// NOVA 12: presets are computed from actual backend catalog using existing smart builder.
const novaPresets=[{"name": "STUDY 200", "category": "Учёба и интернет", "budget": 200000, "purpose": "office", "desc": "Офисные задачи и дистанционное обучение"}, {"name": "HOME 250", "category": "Домашний ПК", "budget": 250000, "purpose": "office", "desc": "Браузер, фильмы и документы"}, {"name": "START 300", "category": "Универсальный", "budget": 300000, "purpose": "office", "desc": "Работа, учёба и повседневные задачи"}, {"name": "OFFICE 350", "category": "Офис+", "budget": 350000, "purpose": "office", "desc": "Многозадачность и офисные приложения"}, {"name": "PLAY 350", "category": "Игровой старт", "budget": 350000, "purpose": "gaming", "desc": "Базовые игровые задачи"}, {"name": "PLAY 400", "category": "Full HD старт", "budget": 400000, "purpose": "gaming", "desc": "Игры с умеренными настройками"}, {"name": "PLAY 450", "category": "Киберспорт", "budget": 450000, "purpose": "gaming", "desc": "Соревновательные игры"}, {"name": "PLAY 500", "category": "Full HD", "budget": 500000, "purpose": "gaming", "desc": "Сбалансированный игровой ПК"}, {"name": "PLAY 550", "category": "Full HD+", "budget": 550000, "purpose": "gaming", "desc": "Дополнительный запас бюджета"}, {"name": "STREAM 600", "category": "Игры и стриминг", "budget": 600000, "purpose": "gaming", "desc": "Игры и трансляции"}, {"name": "BOOST 650", "category": "Игровой BOOST", "budget": 650000, "purpose": "gaming", "desc": "Приоритет графики"}, {"name": "BOOST 700", "category": "Gaming+", "budget": 700000, "purpose": "gaming", "desc": "Универсальная игровая система"}, {"name": "BOOST 750", "category": "Игровой 750", "budget": 750000, "purpose": "gaming", "desc": "Подбор для современных игр"}, {"name": "BOOST 800", "category": "Gaming 800", "budget": 800000, "purpose": "gaming", "desc": "Увеличенный бюджет на видеокарту"}, {"name": "CREATOR 850", "category": "Работа и монтаж", "budget": 850000, "purpose": "office", "desc": "Монтаж и многозадачность"}, {"name": "BOOST 900", "category": "Gaming 900", "budget": 900000, "purpose": "gaming", "desc": "Игровая система высокого класса"}, {"name": "ULTRA 1000", "category": "Игровой миллион", "budget": 1000000, "purpose": "gaming", "desc": "Игровая сборка за миллион тенге"}, {"name": "ULTRA 1100", "category": "Gaming 1.1M", "budget": 1100000, "purpose": "gaming", "desc": "Запас на более мощные комплектующие"}, {"name": "CREATOR 1200", "category": "Рабочая станция", "budget": 1200000, "purpose": "office", "desc": "Создание контента и рабочие задачи"}, {"name": "ULTRA 1250", "category": "Gaming 1.25M", "budget": 1250000, "purpose": "gaming", "desc": "Игры и тяжёлые проекты"}, {"name": "ULTRA 1400", "category": "Gaming 1.4M", "budget": 1400000, "purpose": "gaming", "desc": "Производительная игровая система"}, {"name": "EXTREME 1500", "category": "Extreme Gaming", "budget": 1500000, "purpose": "gaming", "desc": "Высокий игровой бюджет"}, {"name": "CREATOR 1600", "category": "Монтаж PRO", "budget": 1600000, "purpose": "office", "desc": "Рабочая система с запасом ресурсов"}, {"name": "EXTREME 1750", "category": "Gaming 1.75M", "budget": 1750000, "purpose": "gaming", "desc": "Флагманский бюджет"}, {"name": "EXTREME 2000", "category": "Gaming 2M", "budget": 2000000, "purpose": "gaming", "desc": "Максимум возможностей каталога"}, {"name": "WORK 2100", "category": "Workstation 2.1M", "budget": 2100000, "purpose": "office", "desc": "Рабочие нагрузки и создание контента"}, {"name": "EXTREME 2300", "category": "Gaming 2.3M", "budget": 2300000, "purpose": "gaming", "desc": "Премиальный игровой компьютер"}, {"name": "CREATOR 2500", "category": "Creator 2.5M", "budget": 2500000, "purpose": "office", "desc": "Профессиональные задачи"}, {"name": "EXTREME 2750", "category": "Gaming 2.75M", "budget": 2750000, "purpose": "gaming", "desc": "Большой запас на комплектующие"}, {"name": "FLAGSHIP 3000", "category": "Flagship 3M", "budget": 3000000, "purpose": "gaming", "desc": "Максимальный бюджет для игрового ПК"}];
function novaRenderReadyBuilds(){
 const root=document.getElementById('readyBuildCards');if(!root)return;
 const kind=document.getElementById('readyKind')?.value||'all';
 const cap=Number(document.getElementById('readyMax')?.value||0);
 const sort=document.getElementById('readySort')?.value||'asc';
 let items=novaPresets.filter(p=>(kind==='all'||p.purpose===kind)&&(!cap||p.budget<=cap));
 items.sort((a,b)=>sort==='desc'?b.budget-a.budget:a.budget-b.budget);
 const count=document.getElementById('readyCount');if(count)count.textContent='Найдено вариантов: '+items.length;
 root.replaceChildren();
 for(const preset of items){
 const card=document.createElement('article');card.className='ready-build-card';
 const tag=document.createElement('span');tag.className='eyebrow';tag.textContent=preset.purpose==='gaming'?'GAMING / NOVA':'WORK / NOVA';
 const name=document.createElement('h3');name.textContent=preset.name;
 const category=document.createElement('div');category.className='ready-category';category.textContent=preset.category;
 const desc=document.createElement('p');desc.className='muted';desc.textContent=preset.desc;
 const price=document.createElement('strong');price.className='ready-price';price.textContent='Бюджет '+Number(preset.budget).toLocaleString('ru-RU')+' ₸';
 const note=document.createElement('small');note.className='muted';note.textContent='Состав и итоговая цена рассчитываются после подбора';
 const button=document.createElement('button');button.textContent='Подобрать комплектующие →';
 button.onclick=()=>{document.getElementById('budget').value=preset.budget;document.getElementById('purpose').value=preset.purpose;document.getElementById('buildName').value=preset.name;show('builder');document.getElementById('autoBuild').click();};
 card.append(tag,name,category,desc,price,note,button);root.append(card);
 }
}
['readyKind','readyMax','readySort'].forEach(id=>document.getElementById(id)?.addEventListener('change',novaRenderReadyBuilds));
novaRenderReadyBuilds();
const novaReadyNav=document.createElement('button');novaReadyNav.textContent='Готовые ПК';novaReadyNav.onclick=()=>{show('readyBuilds');document.getElementById('readyBuilds').scrollIntoView({behavior:'smooth',block:'start'});};document.querySelector('.nova-old-header nav')?.prepend(novaReadyNav);
