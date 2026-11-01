<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class EmailCodeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(Fortify::redirects('email-verification'));
        }

        if (! EmailVerificationCode::check($user, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => app()->getLocale() === 'en'
                    ? 'This code is invalid or has expired.'
                    : 'Ce code est invalide ou a expiré.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(Fortify::redirects('email-verification'));
    }
}
