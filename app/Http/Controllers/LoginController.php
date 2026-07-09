<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (session('admin_logged_in')) {
            return redirect('/');
        }
        return view('auth.login');
    }

    /**
     * Handle login submission
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $envUser = env('ADMIN_USERNAME', 'syarifat');
        $envPass = env('ADMIN_PASSWORD', 'matahary02');

        if ($request->username === $envUser && $request->password === $envPass) {
            session(['admin_logged_in' => true]);
            return redirect()->intended('/');
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->withErrors(['login_error' => 'Username atau password salah!']);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        session()->forget('admin_logged_in');
        return redirect()->route('login');
    }
}
