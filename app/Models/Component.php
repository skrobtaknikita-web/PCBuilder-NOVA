<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Component extends Model { use HasFactory; protected $fillable=['category_id','name','brand','price','stock','power_watts','socket','ram_type','form_factor','wattage','length_mm','max_gpu_length_mm','is_active']; protected function casts():array{return ['price'=>'decimal:2','is_active'=>'boolean'];} public function category(){return $this->belongsTo(Category::class);} public function configurations(){return $this->belongsToMany(Configuration::class,'configuration_items')->withPivot('quantity')->withTimestamps();} }
