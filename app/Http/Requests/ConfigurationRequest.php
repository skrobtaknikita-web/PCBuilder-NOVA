<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ConfigurationRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['name'=>'required|string|max:255','items'=>'sometimes|array','items.*.component_id'=>'required|distinct|exists:components,id','items.*.quantity'=>'required|integer|min:1|max:16'];}}
