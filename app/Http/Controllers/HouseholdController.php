<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdController extends Controller
{
    /**
     * Onboarding step: who lives here (names only).
     */
    public function people(Request $request): Response
    {
        $household = $request->user()->household;

        return Inertia::render('onboarding/Menage', [
            'people' => $household
                ? array_map(fn ($m) => ['id' => $m['id'], 'name' => $m['name']], $household->members)
                : [],
        ]);
    }

    public function storePeople(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'people' => ['required', 'array', 'min:1', 'max:8'],
            'people.*.id' => ['nullable', 'string'],
            'people.*.name' => ['required', 'string', 'max:50'],
        ]);

        $household = $request->user()->household;
        $existing = collect($household?->members ?? [])->keyBy('id');

        $members = collect($data['people'])->map(function (array $person) use ($existing) {
            $current = $existing->get($person['id'] ?? '');

            return [
                'id' => $current['id'] ?? (string) Str::uuid(),
                'name' => $person['name'],
                'income' => $current['income'] ?? 0,
            ];
        })->values()->all();

        $ids = array_column($members, 'id');

        $personal = collect($household?->personal_expenses ?? [])->map(fn (array $expense) => [
            ...$expense,
            'amounts' => collect($ids)
                ->mapWithKeys(fn ($id) => [$id => $expense['amounts'][$id] ?? 0])
                ->all(),
        ])->all();

        Household::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'members' => $members,
                'common_expenses' => $household?->common_expenses ?? [],
                'personal_expenses' => $personal,
            ],
        );

        return redirect()->route('household.edit');
    }

    /**
     * Step 1: incomes and expenses.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $household = $request->user()->household;

        if (! $household) {
            return redirect()->route('onboarding.menage');
        }

        return Inertia::render('household/Edit', [
            'household' => $household->only(['members', 'common_expenses', 'personal_expenses']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $household = $request->user()->household;

        abort_unless($household, 404);

        $money = ['required', 'numeric', 'min:0', 'max:10000000'];

        $data = $request->validate([
            'members' => ['required', 'array', 'min:1', 'max:8'],
            'members.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'members.*.name' => ['required', 'string', 'max:50'],
            'members.*.income' => $money,
            'common_expenses' => ['present', 'array', 'max:50'],
            'common_expenses.*.id' => ['required', 'string', 'max:64'],
            'common_expenses.*.name' => ['required', 'string', 'max:80'],
            'common_expenses.*.amount' => $money,
            'personal_expenses' => ['present', 'array', 'max:50'],
            'personal_expenses.*.id' => ['required', 'string', 'max:64'],
            'personal_expenses.*.name' => ['required', 'string', 'max:80'],
            'personal_expenses.*.amounts' => ['present', 'array'],
            'personal_expenses.*.amounts.*' => $money,
        ]);

        $ids = array_column($data['members'], 'id');

        $household->update([
            'members' => collect($data['members'])
                ->map(fn (array $m) => [
                    'id' => $m['id'],
                    'name' => $m['name'],
                    'income' => (float) $m['income'],
                ])->values()->all(),
            'common_expenses' => collect($data['common_expenses'])
                ->map(fn (array $e) => [
                    'id' => $e['id'],
                    'name' => $e['name'],
                    'amount' => (float) $e['amount'],
                ])->values()->all(),
            'personal_expenses' => collect($data['personal_expenses'])
                ->map(fn (array $e) => [
                    'id' => $e['id'],
                    'name' => $e['name'],
                    'amounts' => collect($ids)
                        ->mapWithKeys(fn ($id) => [$id => (float) ($e['amounts'][$id] ?? 0)])
                        ->all(),
                ])->values()->all(),
        ]);

        // Background saves ("continue" absent) stay on the page.
        return $request->boolean('continue')
            ? redirect()->route('housings.edit')
            : back();
    }
}
