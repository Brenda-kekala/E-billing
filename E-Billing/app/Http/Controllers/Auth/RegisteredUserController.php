<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
    $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'occupation' => ['required', 'string', 'in:'.implode(',', array_keys(config('tax.occupations')))],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);

    $isAdmin = $request->filled('admin_code')
    && $request->admin_code === config('tax.admin_code');

    $user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'occupation' => $request->occupation,
    'password' => Hash::make($request->password),
    'is_admin' => $isAdmin,
]);
        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
