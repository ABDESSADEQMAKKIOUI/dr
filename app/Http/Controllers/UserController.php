<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['roles'])->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        // Split name into first and last name
        $nameParts = explode(' ', $validated['name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->has('status'),
        ]);

        if (!empty($validated['role_id'])) {
            $user->roles()->attach($validated['role_id']);
        }

        return redirect()->route('users.index')->with('success', __('app.created_success'));
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function profile(User $user): View
    {
        return view('users.profile', compact('user'));
    }

    /**
     * Self-service profile update: name, email and password.
     *
     * resources/views/users/profile.blade.php has always posted to
     * route('users.profile.update'), but neither the route nor this method
     * existed — so the page every deployment tells the admin to visit in order
     * to change the seeded password threw RouteNotFoundException and 500'd.
     *
     * The form field is a single `name`, which is NOT a column: the users table
     * has first_name / last_name. It is split here rather than changing the
     * view, so the same page keeps working for anyone who has customised it.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:190|unique:users,email,'.$user->id,
            // Only enforced when a new password is actually being set.
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|confirmed|min:8',
        ]);

        // Verify the CURRENT password before allowing a change. Without this,
        // anyone with a hijacked session could lock the real owner out.
        if (! empty($validated['password'])) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()
                    ->withErrors(['current_password' => __('auth.password')])
                    ->onlyInput('name', 'email');
            }

            $user->password = Hash::make($validated['password']);
        }

        $parts = preg_split('/\s+/', trim($validated['name']), 2);

        $user->first_name = $parts[0];
        $user->last_name = $parts[1] ?? '';
        $user->email = $validated['email'];
        $user->save();

        return redirect()
            ->route('users.profile', $user)
            ->with('success', __('app.saved_success'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'nullable|string|max:255',
        ]);

        // Split name into first and last name
        $nameParts = explode(' ', $validated['name'], 2);
        
        $user->update([
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'email' => $validated['email'],
        ]);
        
        return redirect()->route('users.index')->with('success', __('app.updated_success'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', __('app.deleted_success'));
    }
}
