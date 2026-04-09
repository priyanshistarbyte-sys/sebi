<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'is_admin' => ['sometimes','boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_admin'] = (bool)($data['is_admin'] ?? false);

        User::create($data);
        return redirect()->route('admin.users.index')->with('status','User created');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'is_admin' => ['sometimes','boolean'],
            'companies'=> ['array'],                         // <-- new
            'companies.*' => ['integer','exists:companies,id'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_admin'] = (bool)($data['is_admin'] ?? false);

        $user->update($data);

        $user->companies()->sync($request->input('companies', []));
        return redirect()->route('admin.users.index')->with('status','User updated');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('status','You cannot delete your own admin account');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('status','User deleted');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }
}
