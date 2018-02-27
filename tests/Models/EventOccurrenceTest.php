<?php

namespace Vulcan\StripeWebhook\Tests\Models;

use SilverStripe\Dev\FunctionalTest;
use Vulcan\StripeWebhook\EventOccurrence;

/**
 * Class EventOccurrenceTest
 * @package Vulcan\StripeWebhook\Tests\Models
 */
class EventOccurrenceTest extends FunctionalTest
{
    protected static $fixture_file = 'evt_00000000000000';

    protected $obj;

    public function setUp()
    {
        $this->obj = $this->objFromFixture(EventOccurrence::class, 'first');
    }

    public function testGetById()
    {
        $event = EventOccurrence::getByEventID('evt_00000000000000');

        $this->assertTrue((bool)$event);
    }
}