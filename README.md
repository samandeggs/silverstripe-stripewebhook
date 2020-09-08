# silverstripe-stripewebhook

Fork from vulcandigital/silverstripe-stripewebhook to update its use in a few ways:

-   Replace yml configuration with .env variables
-   Updated version of `stripe/stripe-php` and updated composer.json

This module is a Stripe webhook event handling delegation interface, a subclass can handle one or
more event and an event can be handled by one or more subclass

## Requirements

-   silverstripe/framework: ^4
-   stripe/stripe-php: ^7.43

## Configuration

replace `<key>`, and write within the `""`.

```
STRIPE_SECRET="<key>"
STRIPE_WEBHOOK_SECRET="<key>"
```

You can also use test keys and the webhook simulator will work fine with this module

## Usage

1. Install and dev/build
1. Add a webhook endpoint to Stripe that points to https://yourdomain.com/stripe-webhook and ensure that it sends the events you require
1. Create your functionality for your event(s):

```php
<?php
use Stripe\Event;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;
use SilverStripe\Security\Member;

class CustomerEventsHandler extends StripeEventHandler
{
    private static $events = [
        'customer.created',
        'customer.deleted'
    ];

    public static function handle($event, Event $data)
    {
        // $event is the string identifier of the event
        if ($event == 'customer.created') {
            // create member
            return "Member created";
        }

        $member = Member::get()->filter('Email', $event->data->object->email)->first();

        if (!$member) {
            return "Member did not exist";
        }

        $member->delete();
        return "Member deleted";
    }
}
```

Any subclass of `StripeEventHandler` is detected and requires both the `private static $events`
and `public static function handle($event, $data)` to be defined.

`private static $events` must be defined and can be a string containing a single [event identifier](https://stripe.com/docs/api#event_types) or an array with multiple

`public static function handle($event,$data)` must be defined and should not call the parent. \$data will be a `\Stripe\Event` object which has the exact same hierarchy as the JSON response depicted in their examples.

## Features

-   All _handled_ events are logged, along with the responses from their handlers.
-   Duplicates are ignored, if Stripe sends the same event more than once it won't be processed, but the logged event will count the occurence
-   All events are verified to have been sent from Stripe using your endpoint_secret you defined in the configuration above

## Why?

Easily introduce new event handling functionality without needing to touch any files relating to other event handling classes.

## License

[BSD-3-Clause](LICENSE.md) - [Vulcan Digital Ltd](https://vulcandigital.co.nz) (original authors - all rights remain theirs.)
