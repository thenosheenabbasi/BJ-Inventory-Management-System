<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $users = User::query()
            ->with('customer:id,user_id,customer_code,full_name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => $this->roles(),
            'statuses' => $this->statuses(),
            'summary' => [
                'total' => User::count(),
                'active' => User::where('status', User::STATUS_ACTIVE)->count(),
                'admins' => User::where('role', User::ROLE_ADMIN)->count(),
                'managers' => User::where('role', User::ROLE_MANAGER)->count(),
                'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('users.create', [
            'managedUser' => new User([
                'role' => User::ROLE_MANAGER,
                'status' => User::STATUS_ACTIVE,
            ]),
            'roles' => $this->roles(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $this->validatedUserData($request);
        $validated['email_verified_at'] = now();

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorizeAdmin();

        return view('users.edit', [
            'managedUser' => $user,
            'roles' => $this->roles(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($user->is(Auth::user())
            && ($request->input('role') !== $user->role || $request->input('status') !== User::STATUS_ACTIVE)) {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own role or deactivate your own account.',
            ]);
        }

        if ($user->role === User::ROLE_ADMIN
            && $user->status === User::STATUS_ACTIVE
            && User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->count() === 1
            && ($request->input('role') !== User::ROLE_ADMIN || $request->input('status') !== User::STATUS_ACTIVE)) {
            throw ValidationException::withMessages([
                'role' => 'At least one active administrator account is required.',
            ]);
        }

        $validated = $this->validatedUserData($request, $user);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account updated successfully.');
    }

    private function validatedUserData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'required' => 'Please enter :attribute.',
            'email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'role.in' => 'Please select a valid role.',
            'status.in' => 'Please select a valid status.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->role === User::ROLE_ADMIN, 403);
    }

    private function roles(): array
    {
        return [
            User::ROLE_ADMIN => 'Administrator',
            User::ROLE_MANAGER => 'Manager',
            User::ROLE_CUSTOMER => 'Customer',
        ];
    }

    private function statuses(): array
    {
        return [
            User::STATUS_ACTIVE => 'Active',
            User::STATUS_INACTIVE => 'Inactive',
        ];
    }
}
