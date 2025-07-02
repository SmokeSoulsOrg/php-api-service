<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePornstarRequest;
use App\Http\Requests\UpdatePornstarRequest;
use App\Http\Resources\PornstarResource;
use App\Models\Pornstar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PornstarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $pornstars = Pornstar::with(['aliases', 'thumbnails.urls'])->paginate($perPage);

        return response()->api(
            PornstarResource::collection($pornstars),
            true,
            'Pornstars retrieved successfully.'
        );
    }

    public function store(StorePornstarRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pornstar = Pornstar::create($data);

        if (!empty($data['aliases'])) {
            $pornstar->aliases()->createMany($data['aliases']);
        }

        if (!empty($data['thumbnails'])) {
            foreach ($data['thumbnails'] as $thumbData) {
                $urls = $thumbData['urls'] ?? [];
                unset($thumbData['urls']);

                $thumbnail = $pornstar->thumbnails()->create($thumbData);

                if (!empty($urls)) {
                    $thumbnail->urls()->createMany($urls);
                }
            }
        }

        $pornstar->load(['aliases', 'thumbnails.urls']);

        return response()->api(new PornstarResource($pornstar), true, 'Pornstar created successfully.', 201);
    }

    public function show(Pornstar $pornstar): JsonResponse
    {
        $pornstar->load(['aliases', 'thumbnails.urls']);

        return response()->api(
            new PornstarResource($pornstar),
            true,
            'Pornstar retrieved successfully.'
        );
    }

    public function update(UpdatePornstarRequest $request, Pornstar $pornstar): JsonResponse
    {
        $data = $request->validated();

        $pornstar->update($data);

        // Replace aliases if provided
        if (isset($data['aliases'])) {
            $pornstar->aliases()->delete();
            $pornstar->aliases()->createMany($data['aliases']);
        }

        // Replace thumbnails and nested urls if provided.
        // Could provide a merge or selective update if needed.
        if (isset($data['thumbnails'])) {
            $pornstar->thumbnails()->each(function ($thumbnail) {
                $thumbnail->urls()->delete();
                $thumbnail->delete();
            });

            foreach ($data['thumbnails'] as $thumbData) {
                $urls = $thumbData['urls'] ?? [];
                unset($thumbData['urls']);

                $thumbnail = $pornstar->thumbnails()->create($thumbData);

                if (!empty($urls)) {
                    $thumbnail->urls()->createMany($urls);
                }
            }
        }

        $pornstar->load(['aliases', 'thumbnails.urls']);

        return response()->api(new PornstarResource($pornstar), true, 'Pornstar updated successfully.');
    }


    public function destroy(Pornstar $pornstar): JsonResponse
    {
        $pornstar->delete();

        return response()->api(
            null,
            true,
            'Pornstar deleted successfully.'
        );
    }
}
