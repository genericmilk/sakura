<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExampleController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        
        return response()->json([
            'users' => $users,
            'count' => $users->count()
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        return response()->json([
            'user' => $user
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create($validated);

        return response()->json([
            'user' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id
        ]);

        $user->update($validated);

        return response()->json([
            'user' => $user,
            'message' => 'User updated successfully'
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
} 