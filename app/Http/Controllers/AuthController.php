<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
        public function showLoginForm()
    {
        return view('auth.login');
    }

       public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->with('error', 'Invalid credentials')->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', 'Login successful');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully');
    }

}
