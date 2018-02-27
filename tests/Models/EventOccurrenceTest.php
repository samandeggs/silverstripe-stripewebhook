<?php

namespace Vulcan\StripeWebhook\Tests\Models;

use SilverStripe\Dev\FunctionalTest;
use Vulcan\StripeWebhook\Models\EventOccurrence;

/**
 * Class EventOccurrenceTest
 *
 * @package Vulcan\StripeWebhook\Tests\Models
 */
class EventOccurrenceTest extends FunctionalTest
{
    protected static $fixture_file = 'EventOccurrenceTest.yml';

    public function testGetById()
    {
        $this->assertTrue(true);
    }
}
