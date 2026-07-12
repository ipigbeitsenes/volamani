<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Adds business ("working") days to a date, skipping weekends and the Nigerian
 * public holidays configured in config/business_days.php. Dependency-free so it
 * can be reused anywhere a working-day deadline is needed (escrow release, SLAs,
 * etc.).
 */
class BusinessDayCalculator
{
    /**
     * Return the date that is $days business days after $from. The starting day
     * itself is never counted — we always advance to the next qualifying day.
     */
    public function addBusinessDays(CarbonInterface $from, int $days): Carbon
    {
        $date = $from->copy();
        $added = 0;

        while ($added < max(1, $days)) {
            $date->addDay();

            if ($this->isBusinessDay($date)) {
                $added++;
            }
        }

        return $date;
    }

    public function isBusinessDay(CarbonInterface $date): bool
    {
        return ! $date->isWeekend() && ! $this->isHoliday($date);
    }

    public function isHoliday(CarbonInterface $date): bool
    {
        $annual = (array) config('business_days.holidays.annual', []);
        $dates = (array) config('business_days.holidays.dates', []);

        return in_array($date->format('m-d'), $annual, true)
            || in_array($date->format('Y-m-d'), $dates, true);
    }
}
