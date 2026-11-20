<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $available = (bool) $this->is_active
            && (int) $this->stock > 0;

        $stockStatus = match (true) {
            !$this->is_active => 'inactive',
            $this->stock <= 0 => 'out_of_stock',
            $this->stock <= 5 => 'low_stock',
            default => 'in_stock',
        };

        $specifications = array_filter([
            'power_watts' => $this->power_watts,
            'socket' => $this->socket,
            'ram_type' => $this->ram_type,
            'form_factor' => $this->form_factor,
            'wattage' => $this->wattage,
            'length_mm' => $this->length_mm,
            'max_gpu_length_mm' => $this->max_gpu_length_mm,
        ], static fn ($value) => $value !== null && $value !== '');

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'brand' => $this->brand,
            'price' => $this->price,
            'stock' => $this->stock,

            'power_watts' => $this->power_watts,
            'socket' => $this->socket,
            'ram_type' => $this->ram_type,
            'form_factor' => $this->form_factor,
            'wattage' => $this->wattage,
            'length_mm' => $this->length_mm,
            'max_gpu_length_mm' => $this->max_gpu_length_mm,

            'is_active' => $this->is_active,

            // Новые поля для карточек товаров
            'is_available' => $available,
            'stock_status' => $stockStatus,
            'specifications' => $specifications,

            'category' => $this->whenLoaded('category'),
        ];
    }
}
