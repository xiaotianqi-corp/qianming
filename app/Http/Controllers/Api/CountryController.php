<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        if ($request->has('active')) {
            $query->active();
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'     => 'required|string|size:2|unique:countries,code',
            'name'     => 'required|string|max:100',
            'currency' => 'required|string|size:3',
            'active'   => 'boolean'
        ]);

        $country = Country::create($validated);

        return response()->json([
            'message' => 'Country successfully created',
            'data'    => $country
        ], 201);
    }
    
    public function show(Country $country): JsonResponse
    {
        return response()->json($country->load('providerConfigs.provider'));
    }

    public function update(Request $request, Country $country): JsonResponse
    {
        $validated = $request->validate([
            'code'     => 'sometimes|string|size:2|unique:countries,code,' . $country->id,
            'name'     => 'sometimes|string|max:100',
            'currency' => 'sometimes|string|size:3',
            'active'   => 'boolean'
        ]);

        $country->update($validated);

        return response()->json([
            'message' => 'Country updated correctly',
            'data'    => $country
        ]);
    }

    public function destroy(Country $country): JsonResponse
    {

        if ($country->identities()->exists() || $country->providerConfigs()->exists()) {
            return response()->json([
                'error' => 'The country cannot be removed because it has associated identities or settings.'
            ], 422);
        }

        $country->delete();

        return response()->json(['message' => 'Country successfully removed']);
    }
}