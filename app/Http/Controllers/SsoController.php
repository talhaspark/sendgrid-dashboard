<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->query('token');
        abort_unless($token, 400, 'Missing SSO token.');

    $response = Http::withToken(config('services.sso.secret'))
    ->post(config('services.sso.central_url') . '/api/sso/verify', [
        'token' => $token,
    ]);

$profile = $response->json();

$user = User::updateOrCreate(
    ['central_user_id' => $profile['id']],
    [
        'name' => $profile['name'],
        'email' => $profile['email'],
        'password' => Str::password(32),
    ]
);

Auth::login($user);

$request->session()->regenerate();

return redirect()->route('dashboard');
    }
}