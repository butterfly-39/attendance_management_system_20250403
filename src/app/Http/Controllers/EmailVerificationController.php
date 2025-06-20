<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;

class EmailVerificationController extends Controller
{
    /**
     * メール認証画面を表示
     */
    public function show()
    {
        return view('auth.verify-email');
    }

    /**
     * メール認証を実行
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('attendance.index') . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('attendance.index') . '?verified=1');
    }

    /**
     * 認証メールを再送信
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('attendance.index'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('resent', '認証メールを再送信しました。');
    }
} 