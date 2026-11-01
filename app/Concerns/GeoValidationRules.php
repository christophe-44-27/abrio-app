<?php

namespace App\Concerns;

use Illuminate\Validation\Rule;

trait GeoValidationRules
{
    /**
     * Rules for the country and province of residence.
     *
     * The province must be one of the known regions for Canada and the
     * United States; elsewhere it is an optional free-text value.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, array<int, mixed>>
     */
    protected function geoRules(array $input): array
    {
        $country = $input['country'] ?? null;
        $regions = config("geo.regions.{$country}");

        return [
            'country' => ['required', 'string', Rule::in(config('geo.countries'))],
            'province' => $regions
                ? ['required', 'string', Rule::in(array_keys($regions))]
                : ['nullable', 'string', 'max:100'],
        ];
    }
}
