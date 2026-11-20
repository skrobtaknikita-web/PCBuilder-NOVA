<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;use Illuminate\Http\Resources\Json\JsonResource;
class ConfigurationResource extends JsonResource {public function toArray(Request $request):array{return ['id'=>$this->id,'name'=>$this->name,'status'=>$this->status,'total_price'=>$this->total_price,'items'=>$this->whenLoaded('items',fn()=>$this->items->map(fn($i)=>['id'=>$i->id,'quantity'=>$i->quantity,'component'=>$i->component])),'created_at'=>$this->created_at];}}
