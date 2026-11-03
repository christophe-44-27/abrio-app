<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HousingController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        $household = $request->user()->currentHousehold();

        if (! $household) {
            return redirect()->route('onboarding.menage');
        }

        return Inertia::render('household/Housings', [
            'household' => $household->only(['members', 'common_expenses', 'personal_expenses']),
            'housings' => $household->housings ?? [],
            'revision' => $household->revision,
            'limit' => $request->user()->housingLimit(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $household = $request->user()->currentHousehold();

        abort_if($household === null, 404);

        $limit = $request->user()->housingLimit();
        $money = ['required', 'numeric', 'min:0', 'max:10000000'];

        $data = $request->validate([
            'housings' => ['present', 'array', 'max:'.($limit ?? 200)],
            'housings.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'housings.*.name' => ['required', 'string', 'max:80'],
            'housings.*.rent' => $money,
            'housings.*.parking' => $money,
            'housings.*.heating' => ['required', Rule::in(['included', 'paid'])],
            'housings.*.heating_amount' => $money,
            'housings.*.one_time' => $money,
            'revision' => ['nullable', 'integer'],
        ]);

        $saved = $household->saveEdit([
            'housings' => array_values(array_map(fn (array $h) => [
                'id' => $h['id'],
                'name' => $h['name'],
                'rent' => (float) $h['rent'],
                'parking' => (float) $h['parking'],
                'heating' => $h['heating'],
                'heating_amount' => $h['heating'] === 'paid' ? (float) $h['heating_amount'] : 0.0,
                'one_time' => (float) $h['one_time'],
            ], $data['housings'])),
        ], $data['revision'] ?? null);

        return $this->afterHouseholdSave($request, $saved, 'verdicts');
    }

    public function verdicts(Request $request): Response|RedirectResponse
    {
        $household = $request->user()->currentHousehold();

        if (! $household) {
            return redirect()->route('onboarding.menage');
        }

        if (empty($household->housings)) {
            return redirect()->route('housings.edit');
        }

        return Inertia::render('household/Verdicts', [
            'household' => $household->only(['members', 'common_expenses', 'personal_expenses']),
            'housings' => $household->housings,
            'justUnlocked' => $request->boolean('unlocked') && $request->user()->isUnlocked(),
        ]);
    }
}
