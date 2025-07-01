<?php

namespace App\Http\Controllers;

use App\Models\PornstarAlias;
use Illuminate\Http\Request;
use App\Http\Requests\StorePornstarAliasRequest;
use App\Http\Requests\UpdatePornstarAliasRequest;
use App\Http\Resources\PornstarAliasResource;
use Illuminate\Http\JsonResponse;

class PornstarAliasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $aliases = PornstarAlias::paginate($perPage);

        return response()->api(
            PornstarAliasResource::collection($aliases),
            true,
            'Aliases retrieved successfully.'
        );
    }

    public function store(StorePornstarAliasRequest $request): JsonResponse
    {
        $alias = PornstarAlias::create($request->validated());

        return response()->api(
            new PornstarAliasResource($alias),
            true,
            'Alias created successfully.',
            201
        );
    }

    public function show(PornstarAlias $pornstarAlias): JsonResponse
    {
        return response()->api(
            new PornstarAliasResource($pornstarAlias),
            true,
            'Alias retrieved successfully.'
        );
    }

    public function update(UpdatePornstarAliasRequest $request, PornstarAlias $pornstarAlias): JsonResponse
    {
        $pornstarAlias->update($request->validated());

        return response()->api(
            new PornstarAliasResource($pornstarAlias),
            true,
            'Alias updated successfully.'
        );
    }

    public function destroy(PornstarAlias $pornstarAlias): JsonResponse
    {
        $pornstarAlias->delete();

        return response()->api(
            null,
            true,
            'Alias deleted successfully.'
        );
    }
}
