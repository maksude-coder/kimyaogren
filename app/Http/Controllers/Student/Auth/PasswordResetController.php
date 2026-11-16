<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * "Şifremi unuttum" (design § 08): request a reset link, then set a new password.
 */
class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('student.auth.forgot-password');
    }

    /**
     * Always answers with the same message, whether or not the address
     * belongs to an account (Sprint 2.3 plan § 2, rule 7).
     */
    public function email(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'E-posta adresini yaz.',
            'email.email' => 'E-posta adresi eksik görünüyor (ör. elif@gmail.com)',
        ]);

        Password::sendResetLink(['email' => mb_strtolower(trim($validated['email']))]);

        return back()->with('status', __('passwords.sent'));
    }

    public function edit(Request $request, string $token): View
    {
        return view('student.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', PasswordRule::min(8)->numbers(), 'max:255'],
        ], [
            'email.required' => 'E-posta adresini yaz.',
            'email.email' => 'E-posta adresi eksik görünüyor (ör. elif@gmail.com)',
            'password.required' => 'Yeni bir şifre belirle.',
            'password.max' => 'Şifre çok uzun.',
        ]);

        $status = Password::reset(
            ['email' => mb_strtolower(trim($validated['email'])), 'password' => $validated['password'], 'token' => $validated['token']],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', __($status));
    }
}
