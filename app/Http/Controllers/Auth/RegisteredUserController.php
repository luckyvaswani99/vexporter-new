<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BuyerProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', [
            'accountType' => $request->query('as') === User::TYPE_VENDOR ? User::TYPE_VENDOR : User::TYPE_BUYER,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'account_type' => ['required', 'in:buyer,vendor'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'type' => $data['account_type'],
            ]);

            $user->syncRoles($data['account_type'] === User::TYPE_VENDOR
                ? RoleSeeder::ROLE_VENDOR_OWNER
                : RoleSeeder::ROLE_BUYER);

            if ($data['account_type'] === User::TYPE_BUYER) {
                BuyerProfile::create([
                    'user_id' => $user->id,
                    'company_name' => $data['company_name'] ?? null,
                    'country_code' => $data['country_code'] ?? null,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return $user->isVendor()
            ? redirect()->route('vendor.onboarding.create')
            : redirect()->route('account.dashboard')->with('status', 'Welcome to VEXPORTER!');
    }
}
