<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User,Category,Component,CompatibilityRule};
class DatabaseSeeder extends Seeder {
 public function run():void {
  User::firstOrCreate(['email'=>'admin@example.test'],['name'=>'Администратор','password'=>Hash::make('Password123!'),'role'=>'admin']);
  User::firstOrCreate(['email'=>'user@example.test'],['name'=>'Демо пользователь','password'=>Hash::make('Password123!'),'role'=>'user']);
  foreach ([['gamer@example.test','Геймер'],['creator@example.test','Контент-мейкер'],['student@example.test','Студент'],['builder@example.test','Сборщик ПК']] as [$email,$name]) { User::firstOrCreate(['email'=>$email],['name'=>$name,'password'=>Hash::make('Password123!'),'role'=>'user']); }
  $names=['cpu'=>'Процессоры','motherboard'=>'Материнские платы','ram'=>'Оперативная память','gpu'=>'Видеокарты','psu'=>'Блоки питания','case'=>'Корпуса','storage'=>'Накопители','cooler'=>'Охлаждение','fan'=>'Вентиляторы'];
  $ids=[];foreach($names as $slug=>$name)$ids[$slug]=Category::updateOrCreate(['slug'=>$slug],['name'=>$name])->id;
  $rows=json_decode(file_get_contents(__DIR__.'/catalog_nova.json'),true,512,JSON_THROW_ON_ERROR);
  foreach($rows as $row){$slug=$row['category'];unset($row['category']);$row['category_id']=$ids[$slug];Component::updateOrCreate(['category_id'=>$row['category_id'],'name'=>$row['name']],$row);}
  foreach(['socket'=>'Совпадение сокета CPU и платы','ram'=>'Соответствие типа RAM','power'=>'Запас БП не менее 20%','gpu_length'=>'Длина видеокарты относительно корпуса'] as $name=>$description)CompatibilityRule::updateOrCreate(['name'=>$name],['description'=>$description]);
 }
}
