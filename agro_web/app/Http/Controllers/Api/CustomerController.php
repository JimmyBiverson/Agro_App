<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Customer::forFranchise($user->franchise_id)->active();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->get();

        return response()->json(['data' => $customers]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'location' => 'nullable|string',
            'is_wholesale' => 'boolean',
        ]);

        $user = $request->user();

        $address = $request->input('address') ?? $request->input('location');

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone ?? '',
            'email' => $request->email,
            'address' => $address,
            'is_wholesale' => $request->boolean('is_wholesale', false),
            'franchise_id' => $user->franchise_id,
        ]);

        return response()->json(['message' => 'Customer created successfully.', 'data' => $customer], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $user = request()->user();

        if ($customer->franchise_id !== $user->franchise_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $customer->loadCount('sales');

        return response()->json(['data' => $customer]);
    }
}
