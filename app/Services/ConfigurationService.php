<?php
namespace App\Services;
use App\Exceptions\BusinessException;
use App\Models\{Component,Configuration};
use Illuminate\Support\Facades\{DB,Log};
class ConfigurationService {
 public function save(Configuration $configuration,array $items):Configuration {
 return DB::transaction(function()use($configuration,$items){
 $ids=array_column($items,'component_id');$components=Component::whereIn('id',$ids)->lockForUpdate()->get()->keyBy('id');
 $selected=[];$total=0;
 foreach($items as $item){$c=$components->get($item['component_id']);if(!$c||!$c->is_active)throw new BusinessException('Комплектующее недоступно');if($c->stock<$item['quantity'])throw new BusinessException('Недостаточно товара: '.$c->name);$selected[]=$c;$total+=(float)$c->price*$item['quantity'];}
 $byCategory=[];foreach($selected as $c){$slug=$c->category->slug;if(isset($byCategory[$slug]))throw new BusinessException('В одной сборке допускается только одно комплектующее категории '.$slug);$byCategory[$slug]=$c;}
 $cpu=$byCategory['cpu']??null;$mb=$byCategory['motherboard']??null;$ram=$byCategory['ram']??null;$psu=$byCategory['psu']??null;$gpu=$byCategory['gpu']??null;$case=$byCategory['case']??null;
 if($cpu&&$mb&&$cpu->socket!==$mb->socket)throw new BusinessException('Сокет процессора не совпадает с материнской платой');
 if($ram&&$mb&&$ram->ram_type!==$mb->ram_type)throw new BusinessException('Тип памяти не поддерживается материнской платой');
 $power=array_sum(array_map(fn($c)=>(int)$c->power_watts,$selected));if($psu&&$psu->wattage<$power*1.2)throw new BusinessException('Недостаточная мощность блока питания (необходим запас 20%)');
 if($gpu&&$case&&$gpu->length_mm&&$case->max_gpu_length_mm&&$gpu->length_mm>$case->max_gpu_length_mm)throw new BusinessException('Видеокарта не помещается в корпус');
 $configuration->update(['total_price'=>$total,'status'=>'valid']);$configuration->components()->sync(collect($items)->mapWithKeys(fn($i)=>[$i['component_id']=>['quantity'=>$i['quantity']]])->all());
 Log::info('Configuration saved',['configuration_id'=>$configuration->id,'user_id'=>$configuration->user_id]);return $configuration->load('items.component');
 });}
}
