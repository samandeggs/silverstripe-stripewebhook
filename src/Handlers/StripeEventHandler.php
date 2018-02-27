<?php

namespace Vulcan\StripeWebhook\Handlers;

use SilverStripe\Core\Config\Configurable;
use Stripe\Event;

/**
 * Class StripeEventHandler
 *
 * @package Vulcan\StripeWebhook\Handlers
 */
abstract class StripeEventHandler
{
    use Configurable;

    /**
     * The dot notated event this handler is responsible for, full list at https://stripe.com/docs/api#event_types, can either be an
     * array, or a string. If handling multiple you should check what $event is in your {@link ::handle()} override
     *
     * @config
     * @var    array
     */
    private static $events = null;

    /**
     * You should override this method in your subclass and create any functionality you need
     * to handle the data from the event
     *
     * @param $event
     * @param Event $data
     *
     * @return string
     */
    public static function handle($event, Event $data)
    {
        throw new \RuntimeException('You must override "handle" in ' . static::class);
    }
}
