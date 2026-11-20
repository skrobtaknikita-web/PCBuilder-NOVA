<?php
namespace App\Services;
use App\Models\Component;
class ComponentService {public function search(array $f){return Component::query()->with('category')->when(isset($f['category_id']),fn($q)=>$q->where('category_id',$f['category_id']))->when(isset($f['brand']),fn($q)=>$q->where('brand','like','%'.$f['brand'].'%'))->when(isset($f['search']),fn($q)=>$q->where('name','like','%'.$f['search'].'%'))->when(isset($f['min_price']),fn($q)=>$q->where('price','>=',$f['min_price']))->when(isset($f['max_price']),fn($q)=>$q->where('price','<=',$f['max_price']))->orderBy($f['sort']??'name',$f['order']??'asc')->paginate($f['per_page']??10)->withQueryString();}}
