<?php

namespace App\Services;

use App\Models\Component;
use Illuminate\Support\Collection;

class ReadyBuildService
{
    private const PRESETS = [
        'office' => [
            'name' => 'Офисный ПК',
            'description' => 'Для учебы, работы и повседневных задач',
            'budget' => 250000,
            'categories' => ['cpu', 'motherboard', 'ram', 'storage', 'psu', 'case'],
        ],
        'gaming' => [
            'name' => 'Игровой ПК',
            'description' => 'Для современных игр в Full HD',
            'budget' => 500000,
            'categories' => ['cpu', 'motherboard', 'ram', 'gpu', 'storage', 'psu', 'case'],
        ],
        'gaming-pro' => [
            'name' => 'Мощный игровой ПК',
            'description' => 'Для игр в 1440p и высоких настроек',
            'budget' => 850000,
            'categories' => ['cpu', 'motherboard', 'ram', 'gpu', 'storage', 'psu', 'case'],
        ],
        'workstation' => [
            'name' => 'Рабочая станция',
            'description' => 'Для программирования, монтажа и 3D',
            'budget' => 1200000,
            'categories' => ['cpu', 'motherboard', 'ram', 'gpu', 'storage', 'psu', 'case'],
        ],
    ];

    public function all(): array
    {
        return collect(self::PRESETS)
            ->map(fn ($preset, $key) => [
                'slug' => $key,
                'name' => $preset['name'],
                'description' => $preset['description'],
                'target_budget' => $preset['budget'],
                'currency' => 'KZT',
            ])
            ->values()
            ->all();
    }

    public function find(string $slug): ?array
    {
        $preset = self::PRESETS[$slug] ?? null;

        if (!$preset) {
            return null;
        }

        $catalog = Component::query()
            ->with('category')
            ->available()
            ->orderBy('price')
            ->get()
            ->groupBy(fn ($component) => $component->category?->slug);

        $selected = collect();

        foreach ($preset['categories'] as $category) {
            $candidates = $catalog->get($category, collect());

            $component = $this->choose(
                $category,
                $candidates,
                $selected
            );

            if (!$component) {
                return [
                    'slug' => $slug,
                    'name' => $preset['name'],
                    'description' => $preset['description'],
                    'target_budget' => $preset['budget'],
                    'currency' => 'KZT',
                    'available' => false,
                    'message' => 'Недостаточно совместимых комплектующих в каталоге',
                    'components' => [],
                    'total_price' => null,
                ];
            }

            $selected->put($category, $component);
        }

        $total = $selected->sum(
            fn ($component) => (float) $component->price
        );

        return [
            'slug' => $slug,
            'name' => $preset['name'],
            'description' => $preset['description'],
            'target_budget' => $preset['budget'],
            'currency' => 'KZT',
            'available' => true,
            'within_budget' => $total <= $preset['budget'],
            'total_price' => round($total, 2),
            'components' => $selected->map(
                fn ($component) => [
                    'id' => $component->id,
                    'category' => $component->category->slug,
                    'name' => $component->name,
                    'brand' => $component->brand,
                    'price' => $component->price,
                    'quantity' => 1,
                ]
            )->values()->all(),
        ];
    }

    private function choose(
        string $category,
        Collection $candidates,
        Collection $selected
    ): ?Component {
        return $candidates->first(function ($component) use (
            $category,
            $selected
        ) {
            $cpu = $selected->get('cpu');
            $motherboard = $selected->get('motherboard');
            $gpu = $selected->get('gpu');

            if (
                $category === 'motherboard' &&
                $cpu &&
                $cpu->socket !== $component->socket
            ) {
                return false;
            }

            if (
                $category === 'ram' &&
                $motherboard &&
                $motherboard->ram_type !== $component->ram_type
            ) {
                return false;
            }

            if ($category === 'case' && $gpu) {
                if (
                    $gpu->length_mm &&
                    $component->max_gpu_length_mm &&
                    $gpu->length_mm > $component->max_gpu_length_mm
                ) {
                    return false;
                }
            }

            if ($category === 'psu') {
                $power = $selected->sum(
                    fn ($item) => (int) $item->power_watts
                );

                if ((float) $component->wattage < $power * 1.2) {
                    return false;
                }
            }

            return true;
        });
    }
}
