<?php

namespace App\Http\Controllers;

use App\Models\PornstarThumbnail;
use Illuminate\Http\Request;
use App\Http\Requests\StorePornstarThumbnailRequest;
use App\Http\Requests\UpdatePornstarThumbnailRequest;
use App\Http\Resources\PornstarThumbnailResource;
use Illuminate\Http\JsonResponse;

class PornstarThumbnailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $thumbnails = PornstarThumbnail::with('urls')->paginate($perPage);

        return response()->api(
            PornstarThumbnailResource::collection($thumbnails),
            true,
            'Thumbnails retrieved successfully.'
        );
    }

    public function store(StorePornstarThumbnailRequest $request): JsonResponse
    {
        $data = $request->validated();

        $urls = $data['urls'] ?? [];
        unset($data['urls']);

        $thumbnail = PornstarThumbnail::create($data);

        if (!empty($urls)) {
            $thumbnail->urls()->createMany($urls);
        }

        $thumbnail->load('urls');

        return response()->api(new PornstarThumbnailResource($thumbnail), true, 'Thumbnail created.', 201);
    }

    public function show(PornstarThumbnail $pornstarThumbnail): JsonResponse
    {
        $pornstarThumbnail->load('urls');

        return response()->api(
            new PornstarThumbnailResource($pornstarThumbnail),
            true,
            'Thumbnail retrieved successfully.'
        );
    }

    public function update(UpdatePornstarThumbnailRequest $request, PornstarThumbnail $pornstarThumbnail): JsonResponse
    {
        $data = $request->validated();

        $urls = $data['urls'] ?? [];
        unset($data['urls']);

        $pornstarThumbnail->update($data);

        // Replace nested urls if provided
        // Could provide a merge or selective update if needed.
        if (!empty($urls)) {
            $pornstarThumbnail->urls()->delete();
            $pornstarThumbnail->urls()->createMany($urls);
        }

        $pornstarThumbnail->load('urls');

        return response()->api(new PornstarThumbnailResource($pornstarThumbnail), true, 'Thumbnail updated successfully.');
    }

    public function destroy(PornstarThumbnail $pornstarThumbnail): JsonResponse
    {
        $pornstarThumbnail->delete();

        return response()->api(
            null,
            true,
            'Thumbnail deleted successfully.'
        );
    }
}
