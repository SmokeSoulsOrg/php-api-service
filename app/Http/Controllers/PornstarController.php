<?php

namespace App\Http\Controllers;

use App\Models\Pornstar;
use Illuminate\Http\Request;
use App\Http\Requests\StorePornstarRequest;
use App\Http\Requests\UpdatePornstarRequest;
use App\Http\Resources\PornstarResource;
use Illuminate\Http\JsonResponse;

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
        $pornstar = Pornstar::create($request->validated());

        return response()->api(
            new PornstarResource($pornstar),
            true,
            'Pornstar created successfully.',
            201
        );
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
        $pornstar->update($request->validated());

        return response()->api(
            new PornstarResource($pornstar),
            true,
            'Pornstar updated successfully.'
        );
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
