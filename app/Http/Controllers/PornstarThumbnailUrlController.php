<?php

namespace App\Http\Controllers;

use App\Models\PornstarThumbnailUrl;
use Illuminate\Http\Request;
use App\Http\Requests\StorePornstarThumbnailUrlRequest;
use App\Http\Requests\UpdatePornstarThumbnailUrlRequest;
use App\Http\Resources\PornstarThumbnailUrlResource;
use Illuminate\Http\JsonResponse;

class PornstarThumbnailUrlController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $urls = PornstarThumbnailUrl::paginate($perPage);

        return response()->api(
            PornstarThumbnailUrlResource::collection($urls),
            true,
            'Thumbnail URLs retrieved successfully.'
        );
    }

    public function store(StorePornstarThumbnailUrlRequest $request): JsonResponse
    {
        $url = PornstarThumbnailUrl::create($request->validated());

        return response()->api(
            new PornstarThumbnailUrlResource($url),
            true,
            'Thumbnail URL created successfully.',
            201
        );
    }

    public function show(PornstarThumbnailUrl $pornstarThumbnailUrl): JsonResponse
    {
        return response()->api(
            new PornstarThumbnailUrlResource($pornstarThumbnailUrl),
            true,
            'Thumbnail URL retrieved successfully.'
        );
    }

    public function update(UpdatePornstarThumbnailUrlRequest $request, PornstarThumbnailUrl $pornstarThumbnailUrl): JsonResponse
    {
        $pornstarThumbnailUrl->update($request->validated());

        return response()->api(
            new PornstarThumbnailUrlResource($pornstarThumbnailUrl),
            true,
            'Thumbnail URL updated successfully.'
        );
    }

    public function destroy(PornstarThumbnailUrl $pornstarThumbnailUrl): JsonResponse
    {
        $pornstarThumbnailUrl->delete();

        return response()->api(
            null,
            true,
            'Thumbnail URL deleted successfully.'
        );
    }
}
