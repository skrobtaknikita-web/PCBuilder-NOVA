<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ComponentFilterRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['category_id'=>'sometimes|integer|exists:categories,id','search'=>'sometimes|string|max:100','brand'=>'sometimes|string|max:100','min_price'=>'sometimes|numeric|min:0','max_price'=>'sometimes|numeric|min:0','sort'=>['sometimes',Rule::in(['price','name','created_at'])],'order'=>['sometimes',Rule::in(['asc','desc'])],'per_page'=>'sometimes|integer|min:1|max:100','page'=>'sometimes|integer|min:1'];}}
