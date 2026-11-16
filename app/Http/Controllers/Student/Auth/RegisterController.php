<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Registration step 1 (design § 08): creates the student account.
 *
 * The account exists and is signed in after this step; the grade is asked
 * on the next screen (Sprint 2.3 plan § 2, rule 1).
 */
class RegisterController extends Controller
{
    public function create(): View
    {
        return view('student.auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));

        // Never taken from the request: every self-registration is an active student.
        $user->forceFill([
            'role' => UserRole::Student,
            'status' => UserStatus::Active,
            'terms_accepted_at' => now(),
        ])->save();

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('register.grade');
    }
}
