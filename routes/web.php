<?php

use App\Http\Controllers\Auth\EmailCodeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\HousingController;
use App\Http\Controllers\UnlockController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

foreach (['fr' => '/', 'en' => '/en'] as $locale => $uri) {
    Route::get($uri, function () use ($locale) {
        app()->setLocale($locale);
        session(['locale' => $locale]);

        return Inertia::render('Landing', [
            'locale' => $locale,
            'urls' => ['fr' => url('/'), 'en' => url('/en')],
        ]);
    })->name($locale === 'fr' ? 'home' : 'home.en');
}

Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

// Language switch for pages that have no /en twin (auth, onboarding, app).
Route::post('locale/{locale}', function (string $locale) {
    session(['locale' => $locale]);

    return back();
})->whereIn('locale', SetLocale::LOCALES)->name('locale.update');

Route::post('email/verify-code', [EmailCodeController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.code');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('onboarding', 'onboarding/Bienvenue')->name('onboarding.bienvenue');
    Route::get('onboarding/menage', [HouseholdController::class, 'people'])->name('onboarding.menage');
    Route::post('onboarding/menage', [HouseholdController::class, 'storePeople'])->name('onboarding.menage.store');

    Route::get('menage', [HouseholdController::class, 'edit'])->name('household.edit');
    Route::put('menage', [HouseholdController::class, 'update'])->name('household.update');

    Route::get('logements', [HousingController::class, 'edit'])->name('housings.edit');
    Route::put('logements', [HousingController::class, 'update'])->name('housings.update');
    Route::get('verdicts', [HousingController::class, 'verdicts'])->name('verdicts');

    Route::get('unlock', [UnlockController::class, 'start'])->name('unlock');
    Route::get('unlock/success', [UnlockController::class, 'success'])->name('unlock.success');

    // New accounts go through the onboarding until their household exists.
    // Also where signed-in users land from the landing page and after login:
    // resume wherever they left off.
    Route::get('dashboard', function (Request $request) {
        $household = $request->user()->household;

        if (! $household) {
            return redirect()->route('onboarding.bienvenue');
        }

        return redirect()->route(empty($household->housings) ? 'housings.edit' : 'verdicts');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
