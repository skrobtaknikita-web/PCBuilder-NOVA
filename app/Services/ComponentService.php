<?php

namespace App\Services;

use App\Models\Component;

class ComponentService
{
    public function search(array $filters)
    {
        $query = Component::query()->with('category');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand'])) {
            $query->where(
                'brand',
                'like',
                '%' . $filters['brand'] . '%'
            );
        }

        if (isset($filters['search'])) {
            $query->searchByName($filters['search']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Показывать только доступные товары
        if ($filters['available'] ?? false) {
            $query->available();
        }

        $sort = $filters['sort'] ?? 'name';
        $order = $filters['order'] ?? 'asc';

        $query->orderBy($sort, $order);

        return $query
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
