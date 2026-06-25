<?php

namespace App\Http\Controllers;

use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InquiryController extends Controller
{
    private const STATUSES = [
        Inquiry::STATUS_NEW,
        Inquiry::STATUS_CONTACTED,
        Inquiry::STATUS_SCHEDULED,
        Inquiry::STATUS_CANCELLED,
        Inquiry::STATUS_CLOSED,
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => ['sometimes', 'integer', 'exists:properties,id'],
        ]);

        $user = $request->user();

        $query = Inquiry::query()
            ->with(['user', 'property.category'])
            ->latest();

        if ($user->role === User::ROLE_ADMIN) {
            if (isset($validated['property_id'])) {
                $query->where('property_id', $validated['property_id']);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $inquiries = $query->get();

        return response()->json([
            'count' => $inquiries->count(),
            'inquiries' => InquiryResource::collection($inquiries),
        ]);
    }
    /**
     * Display inquiries for the specified property.
     */
    public function byProperty(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();

        $query = Inquiry::query()
            ->with(['user', 'property.category'])
            ->where('property_id', $property->id)
            ->latest();

        if ($user->role !== User::ROLE_ADMIN) {
            $query->where('user_id', $user->id);
        }

        $inquiries = $query->get();

        return response()->json([
            'count' => $inquiries->count(),
            'property_id' => $property->id,
            'inquiries' => InquiryResource::collection($inquiries),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()?->role !== User::ROLE_USER) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['prohibited'],
            'property_id' => [
                'required',
                'integer',
                'exists:properties,id',
                Rule::unique('inquiries', 'property_id')
                    ->where('user_id', $request->user()->id)
                    ->where('status', Inquiry::STATUS_NEW),
            ],
            'message' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'status' => ['prohibited'],
            'admin_note' => ['prohibited'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = Inquiry::STATUS_NEW;

        $inquiry = Inquiry::create($validated)->load(['user', 'property.category']);

        return response()->json([
            'message' => 'Inquiry created successfully.',
            'inquiry' => new InquiryResource($inquiry),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inquiry $inquiry): JsonResponse
    {
        if ($request->user()?->role !== User::ROLE_ADMIN) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['prohibited'],
            'property_id' => ['prohibited'],
            'message' => ['prohibited'],
            'phone' => ['prohibited'],
            'preferred_date' => ['prohibited'],
            'preferred_time' => ['prohibited'],
            'status' => [
                'sometimes',
                Rule::in(self::STATUSES),
                Rule::unique('inquiries', 'status')
                    ->where('user_id', $inquiry->user_id)
                    ->where('property_id', $inquiry->property_id)
                    ->ignore($inquiry->id),
            ],
            'admin_note' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($validated === []) {
            $inquiry->load(['user', 'property.category']);

            return response()->json([
                'message' => 'Nothing to update.',
                'inquiry' => new InquiryResource($inquiry),
            ]);
        }

        $inquiry->update($validated);
        $inquiry->load(['user', 'property.category']);

        return response()->json([
            'message' => 'Inquiry updated successfully.',
            'inquiry' => new InquiryResource($inquiry),
        ]);
    }
}
