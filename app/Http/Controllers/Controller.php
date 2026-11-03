<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /**
     * What follows a household edit: the conflict error when a teammate saved
     * first, otherwise the next step ("continue") or the page itself (background save).
     */
    protected function afterHouseholdSave(Request $request, bool $saved, string $nextRoute): RedirectResponse
    {
        if (! $saved) {
            return back()->withErrors(['conflict' => __('team.conflict')]);
        }

        return $request->boolean('continue') ? redirect()->route($nextRoute) : back();
    }
}
