<?php declare(strict_types=1);

namespace WoowaWebhooks\Tests;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use function jakarta_date;
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

    /**
     * Tests the jakarta_date function to ensure it returns the correct
     * date and time in Jakarta for a given time offset.
     *
     * @return void
     */
    public function testJakartaDate(): void
    {
        $time_offset = 'now';

        $date = new DateTime($time_offset, new DateTimeZone("Asia/Jakarta"));
        $date = $date->format('Y-m-d H:i');

        $this->assertEquals($date, jakarta_date($time_offset));
        $this->assertNotEquals($date, jakarta_date('tomorrow'));
    }
}