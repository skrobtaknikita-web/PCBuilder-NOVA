<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ConfigurationItem extends Model { use HasFactory; protected $fillable=['configuration_id','component_id','quantity'];  public function configuration(){return $this->belongsTo(Configuration::class);} public function component(){return $this->belongsTo(Component::class);} }
