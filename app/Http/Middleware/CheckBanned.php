<?php

namespace App\Http\Middleware;

use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Keep banned emails and IP addresses out: they cannot sign up or log in,
     * and a session that is already open is closed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && Ban::matches($user->email, $request->ip())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, __('ban.message'));
        }

        if ($request->isMethod('POST') && $request->is('login', 'register') && Ban::matches($request->input('email'), $request->ip())) {
            throw ValidationException::withMessages(['email' => __('ban.message')]);
        }

        return $next($request);
    }
}
