<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(User::class, strtolower('User'));
    }
    public function index()
    {
        $this->authorize('Users.view');
        $users = User::with('roles')->get();
        return view('user-management.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('Users.create');
        $roles = Role::all();
        return view('user-management.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('Users.create');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|unique:users',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'status' => 'required|in:Active,Inactive,Suspended,Locked',
            'role' => 'required|exists:roles,name',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'role', 'photo']);
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->extension();
            \Intervention\Image\ImageManagerStatic::make($file)->encode($file->extension(), 90)->save(storage_path('app/public/user_photos/' . $filename));
                $data['photo'] = 'user_photos/' . $filename;
        }

        $user = User::create($data);
        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorize('Users.edit');
        $roles = Role::all();
        return view('user-management.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('Users.edit');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'status' => 'required|in:Active,Inactive,Suspended,Locked',
            'role' => 'required|exists:roles,name',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'role', 'photo', 'employee_id']); // Employee ID non editable

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $file = $request->file('photo');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->extension();
            \Intervention\Image\ImageManagerStatic::make($file)->encode($file->extension(), 90)->save(storage_path('app/public/user_photos/' . $filename));
                $data['photo'] = 'user_photos/' . $filename;
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('Users.delete');
        // Prevent deleting oneself
        if ($user->id == auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete yourself.');
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
