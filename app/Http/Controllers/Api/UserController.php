<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Read: list all users
  public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json($users);
    }

    
public function store(Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8'],
        'profile_image' => [
            'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048',
        ],
    ]);

    if ($request->hasFile('profile_image')) {
        $data['profile_image'] = $request->file('profile_image')
            ->store('profiles', 'public');
    }

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'profile_image' => $data['profile_image'] ?? null,
    ]);

    return response()->json([
        'message' => 'User created successfully.',
        'user' => $user->only('id', 'name', 'email', 'profile_image'),
    ], 201);
}

public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'email',
            Rule::unique('users', 'email')->ignore($user->id),
        ],
        'password' => ['nullable', 'string', 'min:8'],
        'profile_image' => [
            'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048',
        ],
    ]);

    if ($request->hasFile('profile_image')) {
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $data['profile_image'] = $request->file('profile_image')
            ->store('profiles', 'public');
    }

    $user->name = $data['name'];
    $user->email = $data['email'];

    if (!empty($data['password'])) {
        $user->password = Hash::make($data['password']);
    }

    if (isset($data['profile_image'])) {
        $user->profile_image = $data['profile_image'];
    }

    $user->save();

    return response()->json([
        'message' => 'User updated successfully.',
        'user' => $user->only('id', 'name', 'email', 'profile_image'),
    ]);
}


    // Read: view one user
    public function show(User $user)
    {
        return response()->json([
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }

 
    // Delete: remove a user
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
