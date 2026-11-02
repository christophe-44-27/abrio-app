<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

abstract class Controller
{
    /**
     * Go back (or to a route) and show a success toast on the next page.
     */
    protected function withToast(RedirectResponse $redirect, string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return $redirect;
    }
}
