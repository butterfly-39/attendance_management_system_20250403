<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'メールアドレスを入力してください',
                'email.email' => '正しいメールアドレスを入力してください',
                'password.required' => 'パスワードを入力してください',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません']
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません']
                ]);
            }

            if (!$user->is_admin) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません']
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.attendance.list'));

        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
}