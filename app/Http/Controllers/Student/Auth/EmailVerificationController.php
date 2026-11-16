<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * E-mail confirmation. Requested but never required to use the account
 * (PM decision, Sprint 2.3 plan).
 */
class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('curriculum.index')->with('status', 'E-posta adresin doğrulandı.');
    }

    public function send(Request $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('status', 'Doğrulama e-postasını yeniden gönderdik. Gelen kutunu kontrol et.');
    }
}
