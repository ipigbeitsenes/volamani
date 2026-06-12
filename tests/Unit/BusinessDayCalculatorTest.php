<?php

namespace Tests\Unit;

use App\Support\BusinessDayCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class BusinessDayCalculatorTest extends TestCase
{
    private function calc(): BusinessDayCalculator
    {
        return new BusinessDayCalculator();
    }

    public function test_three_business_days_skip_weekend_and_public_holiday(): void
    {
        // Thu 11 Jun 2026. Fri 12 Jun is Democracy Day (holiday) → skip.
        // Sat/Sun skip. So Mon 15 (1), Tue 16 (2), Wed 17 (3).
        $due = $this->calc()->addBusinessDays(Carbon::parse('2026-06-11 14:00'), 3);

        $this->assertSame('2026-06-17', $due->toDateString());
        $this->assertSame('14:00', $due->format('H:i')); // time of day preserved
    }

    public function test_three_business_days_on_a_clear_week(): void
    {
        // Mon 2 Feb 2026 + 3 business days → Thu 5 Feb 2026.
        $due = $this->calc()->addBusinessDays(Carbon::parse('2026-02-02 09:00'), 3);

        $this->assertSame('2026-02-05', $due->toDateString());
    }

    public function test_recognises_annual_and_movable_holidays(): void
    {
        $calc = $this->calc();

        $this->assertTrue($calc->isHoliday(Carbon::parse('2026-06-12')));  // Democracy Day (annual)
        $this->assertTrue($calc->isHoliday(Carbon::parse('2026-12-25')));  // Christmas (annual)
        $this->assertTrue($calc->isHoliday(Carbon::parse('2026-04-06')));  // Easter Monday (movable)
        $this->assertFalse($calc->isHoliday(Carbon::parse('2026-02-03'))); // ordinary Tuesday
    }

    public function test_weekends_are_not_business_days(): void
    {
        $calc = $this->calc();

        $this->assertFalse($calc->isBusinessDay(Carbon::parse('2026-06-13'))); // Saturday
        $this->assertFalse($calc->isBusinessDay(Carbon::parse('2026-06-14'))); // Sunday
        $this->assertTrue($calc->isBusinessDay(Carbon::parse('2026-06-16')));  // Tuesday
    }
}
