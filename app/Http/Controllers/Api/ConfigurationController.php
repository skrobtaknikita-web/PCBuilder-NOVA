<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfigurationRequest;
use App\Http\Resources\ConfigurationResource;
use App\Models\Configuration;
use App\Services\ConfigurationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ConfigurationController extends Controller
{
    public function __construct(
        private ConfigurationService $service
    ) {
    }

    public function index()
    {
        return ConfigurationResource::collection(
            Configuration::query()
                ->where('user_id', auth()->id())
                ->with('items.component')
                ->latest()
                ->paginate(10)
        );
    }

    public function show(Configuration $configuration)
    {
        Gate::authorize('view', $configuration);

        return new ConfigurationResource(
            $configuration->load('items.component')
        );
    }

    public function store(ConfigurationRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            $configuration = $request->user()
                ->configurations()
                ->create([
                    'name' => $data['name'],
                ]);

            if (array_key_exists('items', $data)) {
                $configuration = $this->service->save(
                    $configuration,
                    $data['items']
                );
            }

            return (new ConfigurationResource(
                $configuration->load('items.component')
            ))->response()->setStatusCode(201);
        });
    }

    public function update(
        ConfigurationRequest $request,
        Configuration $configuration
    ) {
        Gate::authorize('update', $configuration);

        return DB::transaction(function () use (
            $request,
            $configuration
        ) {
            $data = $request->validated();

            $configuration->update([
                'name' => $data['name'],
            ]);

            if (array_key_exists('items', $data)) {
                $configuration = $this->service->save(
                    $configuration,
                    $data['items']
                );
            }

            return new ConfigurationResource(
                $configuration->load('items.component')
            );
        });
    }

    public function destroy(Configuration $configuration)
    {
        Gate::authorize('delete', $configuration);

        $configuration->delete();

        return response()->noContent();
    }
}
