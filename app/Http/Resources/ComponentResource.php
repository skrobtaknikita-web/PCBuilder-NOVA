<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;use Illuminate\Http\Resources\Json\JsonResource;
class ComponentResource extends JsonResource {public function toArray(Request $request):array{return ['id'=>$this->id,'category_id'=>$this->category_id,'name'=>$this->name,'brand'=>$this->brand,'price'=>$this->price,'stock'=>$this->stock,'power_watts'=>$this->power_watts,'socket'=>$this->socket,'ram_type'=>$this->ram_type,'form_factor'=>$this->form_factor,'wattage'=>$this->wattage,'length_mm'=>$this->length_mm,'max_gpu_length_mm'=>$this->max_gpu_length_mm,'is_active'=>$this->is_active,'category'=>$this->whenLoaded('category')];}}
