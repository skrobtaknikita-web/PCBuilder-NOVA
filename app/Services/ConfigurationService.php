<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\{Component, Configuration};
use Illuminate\Support\Facades\{DB, Log};

class ConfigurationService
{
    public function save(
        Configuration $configuration,
        array $items
    ): Configuration {
        return DB::transaction(function () use ($configuration, $items) {

            $ids = array_column($items, 'component_id');

            $components = Component::query()
                ->with('category')
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $selected = [];
            $total = 0;
            $power = 0;

            foreach ($items as $item) {
                $component = $components->get($item['component_id']);

                if (!$component || !$component->is_active) {
                    throw new BusinessException(
                        'Комплектующее недоступно'
                    );
                }

                if ($component->stock < $item['quantity']) {
                    throw new BusinessException(
                        'Недостаточно товара: ' . $component->name
                    );
                }

                $selected[] = $component;

                $total += (float) $component->price
                    * (int) $item['quantity'];

                $power += (int) $component->power_watts
                    * (int) $item['quantity'];
            }

            $byCategory = [];

            foreach ($selected as $component) {
                $slug = $component->category->slug;

                if (isset($byCategory[$slug])) {
                    throw new BusinessException(
                        'В одной сборке допускается только одно комплектующее категории '
                        . $slug
                    );
                }

                $byCategory[$slug] = $component;
            }

            $cpu = $byCategory['cpu'] ?? null;
            $motherboard = $byCategory['motherboard'] ?? null;
            $ram = $byCategory['ram'] ?? null;
            $psu = $byCategory['psu'] ?? null;
            $gpu = $byCategory['gpu'] ?? null;
            $case = $byCategory['case'] ?? null;

            // Проверка сокета процессора
            if (
                $cpu &&
                $motherboard &&
                $cpu->socket !== $motherboard->socket
            ) {
                throw new BusinessException(
                    'Сокет процессора не совпадает с материнской платой'
                );
            }

            // Проверка типа оперативной памяти
            if (
                $ram &&
                $motherboard &&
                $ram->ram_type !== $motherboard->ram_type
            ) {
                throw new BusinessException(
                    'Тип памяти не поддерживается материнской платой'
                );
            }

            // Проверка мощности блока питания
            // Учитывается количество каждого комплектующего
            if (
                $psu &&
                (float) $psu->wattage < $power * 1.2
            ) {
                throw new BusinessException(
                    'Недостаточная мощность блока питания (необходим запас 20%)'
                );
            }

            // Проверка длины видеокарты
            if (
                $gpu &&
                $case &&
                $gpu->length_mm &&
                $case->max_gpu_length_mm &&
                $gpu->length_mm > $case->max_gpu_length_mm
            ) {
                throw new BusinessException(
                    'Видеокарта не помещается в корпус'
                );
            }

            // Сохранение итоговой стоимости и статуса
            $configuration->update([
                'total_price' => $total,
                'status' => 'valid',
            ]);

            // Сохранение выбранных комплектующих
            $configuration->components()->sync(
                collect($items)
                    ->mapWithKeys(fn ($item) => [
                        $item['component_id'] => [
                            'quantity' => $item['quantity'],
                        ],
                    ])
                    ->all()
            );

            Log::info('Configuration saved', [
                'configuration_id' => $configuration->id,
                'user_id' => $configuration->user_id,
            ]);

            return $configuration->load('items.component');
        });
    }
}
