<?php declare(strict_types=1);

namespace WoowaWebhooks\Tests;

use PHPUnit\Framework\TestCase;
use function is_seven_days_before;

/**
 * Unit tests for date utility functions.
 */
final class DateUtilitiesTest extends TestCase
{
    /**
     * Test is_seven_days_before returns true for dates seven or more days in the past,
     * and false for today.
     *
     * @return void
     */
    public function testIsDateSevenDaysBefore(): void
    {
        $date         = date('m/d/Y', strtotime('-7 days'));
        $further_date = date('m/d/Y', strtotime('-8 days'));

        $this->assertTrue(is_seven_days_before($date));

        $this->assertTrue(is_seven_days_before($further_date));
        
        $this->assertFalse(is_seven_days_before(date('m/d/Y')));
    }
}