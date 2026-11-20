<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ComponentRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['category_id'=>'required|exists:categories,id','name'=>'required|string|max:255','brand'=>'required|string|max:255','price'=>'required|numeric|gt:0','stock'=>'required|integer|min:0','power_watts'=>'nullable|integer|min:0','socket'=>'nullable|string|max:255','ram_type'=>'nullable|string|max:255','form_factor'=>'nullable|string|max:255','wattage'=>'nullable|integer|min:0','length_mm'=>'nullable|integer|min:0','max_gpu_length_mm'=>'nullable|integer|min:0','is_active'=>'sometimes|boolean'];}}
