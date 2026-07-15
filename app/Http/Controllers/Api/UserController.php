<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users (paginated) with their roles
     */
    public function index(Request $request)
    {
        $users = User::with('roleRelation')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    /**
     * Create a new user (admin action)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $role = Role::find($validated['role_id']);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $role->name,
            'role_id'   => $role->id,
            'is_active' => true,
        ]);\App\Models\ActivityLog::record('created', $user);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data'    => $user->load('roleRelation'),
        ], 201);
    }

    /**
     * Show a single user
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data'    => $user->load('roleRelation.permissions'),
        ]);
    }

    /**
     * Update a user's role and details
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        if (isset($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            $validated['role'] = $role->name;
        }

        $oldValues = $user->only(array_keys($validated));
        $user->update($validated);
        \App\Models\ActivityLog::record('updated', $user, $oldValues, $validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data'    => $user->fresh()->load('roleRelation'),
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        \App\Models\ActivityLog::record($user->is_active ? 'activated' : 'deactivated', $user);
        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'User activated' : 'User deactivated',
            'data'    => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy(User $user)
    {
        $user->tokens()->delete();
        \App\Models\ActivityLog::record('deleted', $user);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }
}