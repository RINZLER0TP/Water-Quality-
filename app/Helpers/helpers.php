<?php

use Illuminate\Support\Carbon;

if (! function_exists('friendly_date')) {
    function friendly_date(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->toDateTimeString();
    }
}
