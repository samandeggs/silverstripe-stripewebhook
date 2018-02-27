<?php

namespace Vulcan\StripeWebhook\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use Stripe\Event;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;
use Vulcan\StripeWebhook\Models\EventOccurrence;
use Vulcan\StripeWebhook\StripeWebhook;

class StripeWebhookController extends Controller
{
    private static $allowed_actions = [
        'index'
    ];

    /** @var StripeWebhook */
    protected $client;

    public function init()
    {
        parent::init();

        $this->client = StripeWebhook::create();
    }

    /**
     * @param HTTPRequest $request
     *
     * @return \SilverStripe\Control\HTTPResponse
     */
    public function index(HTTPRequest $request)
    {
        $body = $request->getBody();
        $header = $request->getHeader('Stripe-Signature');
        $eventJson = json_decode($body, true);

        if (!$header) {
            $this->httpError(401);
        }

        if (!$eventJson) {
            $this->httpError(422, 'The body did not contain valid json');
        }

        $result = null;

        try {
            $event = \Stripe\Webhook::constructEvent($body, $header, $this->client->getEndpointSecret());

            if ($occurrence = EventOccurrence::getByEventID($event->id)) {
                // this event occurrence has already been processed
                // for some unknown reason lets record the fact that this event was
                // sent a number of times
                $occurrence->Occurrences = $occurrence->Occurrences + 1;
                $occurrence->write();

                return $this->getResponse()->setBody('OK - Duplicate');
            }

            $result = $this->delegateEvent($event);

            if (!$result) {
                return $this->getResponse()->setBody('No handlers defined for event ' . $event->type);
            }

            $occurrence = EventOccurrence::create();
            $occurrence->EventID = $event->id;
            $occurrence->Type = $event->type;
            $occurrence->Data = $body;
            $occurrence->Handlers = implode(PHP_EOL, $result['Handlers']);
            $occurrence->HandlerResponses = implode(PHP_EOL, $result['Responses']);
            $occurrence->write();
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            $this->httpError(400);
        } catch (\Stripe\Error\SignatureVerification $e) {
            $this->httpError(400);
        }

        $break = (Director::is_cli()) ? PHP_EOL : "<br/>";
        return $this->getResponse()->setBody(implode($break, $result['Responses']));
    }

    /**
     * @param Event $event
     *
     * @return array|null
     */
    public function delegateEvent(Event $event)
    {
        $handlers = $this->client->getHandlers();

        if (!isset($handlers[$event->type])) {
            return null;
        }


        $responses = [];
        /** @var StripeEventHandler $class */
        foreach ($handlers[$event->type] as $class) {
            $response = $class::handle($event->type, $event);
            $responses[] = $class . ':' . $response ?: "NULL";
        }

        return [
            'Handlers' => $handlers[$event->type],
            'Responses' => $responses
        ];
    }
}