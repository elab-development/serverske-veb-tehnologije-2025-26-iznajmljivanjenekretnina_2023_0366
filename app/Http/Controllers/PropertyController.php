<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Models\Category;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    private const LISTING_TYPES = [
        Property::LISTING_TYPE_SALE,
        Property::LISTING_TYPE_RENT,
    ];

    private const STATUSES = [
        Property::STATUS_DRAFT,
        Property::STATUS_ACTIVE,
        Property::STATUS_ARCHIVED,
    ];

    private const SORTABLE_FIELDS = [
        'title',
        'price',
        'area',
        'city',
        'listing_type',
        'status',
        'published_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'listing_type' => ['sometimes', Rule::in(self::LISTING_TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'city' => ['sometimes', 'string', 'max:255'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'min_area' => ['sometimes', 'numeric', 'min:0'],
            'max_area' => ['sometimes', 'numeric', 'min:0', 'gte:min_area'],
            'sort_by' => ['sometimes', Rule::in(self::SORTABLE_FIELDS)],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Property::query()->with('category');

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['listing_type'])) {
            $query->where('listing_type', $validated['listing_type']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['city'])) {
            $query->where('city', 'like', "%{$validated['city']}%");
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        if (isset($validated['min_area'])) {
            $query->where('area', '>=', $validated['min_area']);
        }

        if (isset($validated['max_area'])) {
            $query->where('area', '<=', $validated['max_area']);
        }

        $properties = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'count' => $properties->count(),
            'total' => $properties->total(),
            'per_page' => $properties->perPage(),
            'current_page' => $properties->currentPage(),
            'last_page' => $properties->lastPage(),
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection,
            ],
            'filters' => $request->only([
                'search',
                'category_id',
                'listing_type',
                'status',
                'city',
                'is_featured',
                'min_price',
                'max_price',
                'min_area',
                'max_area',
            ]),
            'properties' => PropertyResource::collection($properties->getCollection()),
        ]);
    }
    /**
     * Display properties for the specified category.
     */
    public function byCategory(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'listing_type' => ['sometimes', Rule::in(self::LISTING_TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'city' => ['sometimes', 'string', 'max:255'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'min_area' => ['sometimes', 'numeric', 'min:0'],
            'max_area' => ['sometimes', 'numeric', 'min:0', 'gte:min_area'],
            'sort_by' => ['sometimes', Rule::in(self::SORTABLE_FIELDS)],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Property::query()
            ->with('category')
            ->where('category_id', $category->id);

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (isset($validated['listing_type'])) {
            $query->where('listing_type', $validated['listing_type']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['city'])) {
            $query->where('city', 'like', "%{$validated['city']}%");
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        if (isset($validated['min_area'])) {
            $query->where('area', '>=', $validated['min_area']);
        }

        if (isset($validated['max_area'])) {
            $query->where('area', '<=', $validated['max_area']);
        }

        $properties = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'count' => $properties->count(),
            'total' => $properties->total(),
            'per_page' => $properties->perPage(),
            'current_page' => $properties->currentPage(),
            'last_page' => $properties->lastPage(),
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection,
            ],
            'filters' => $request->only([
                'search',
                'listing_type',
                'status',
                'city',
                'min_price',
                'max_price',
                'min_area',
                'max_area',
            ]),
            'category_id' => $category->id,
            'properties' => PropertyResource::collection($properties->getCollection()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->isAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate($this->rules());
        $validated['status'] ??= Property::STATUS_DRAFT;
        $validated['is_featured'] ??= false;

        $property = Property::create($validated)->load('category');

        return response()->json([
            'message' => 'Property created successfully.',
            'property' => new PropertyResource($property),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property): JsonResponse
    {
        $property->load('category');

        return response()->json([
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        if (! $this->isAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate($this->rules(true));

        if ($validated === []) {
            $property->load('category');

            return response()->json([
                'message' => 'Nothing to update.',
                'property' => new PropertyResource($property),
            ]);
        }

        $property->update($validated);
        $property->load('category');

        return response()->json([
            'message' => 'Property updated successfully.',
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        if (! $this->isAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return [
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'title' => [$required, 'string', 'max:255'],
            'description' => [$required, 'string'],
            'price' => [$required, 'numeric', 'min:0'],
            'city' => [$required, 'string', 'max:255'],
            'address' => [$required, 'string', 'max:255'],
            'area' => [$required, 'numeric', 'min:0'],
            'rooms' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'bathrooms' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:255'],
            'floor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'total_floors' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:255'],
            'year_built' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:' . now()->year],
            'listing_type' => [$required, Rule::in(self::LISTING_TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'is_featured' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === User::ROLE_ADMIN;
    }
}
