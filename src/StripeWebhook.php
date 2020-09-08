<?php

namespace Vulcan\StripeWebhook;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use Vulcan\StripeWebhook\Handlers\StripeEventHandler;

class StripeWebhook implements Flushable
{
    use Injectable, Configurable;

    private static $secret_key = false;
    private static $endpoint_secret = false;

    public function __construct()
    {
        \Stripe\Stripe::setApiKey($this->getSecret());
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getSecret()
    {
        $key = Environment::getEnv('STRIPE_SECRET');

        if (!$key) {
            throw new \RuntimeException('$secret_key needs to be configured via .env');
        }

        return $key;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getEndpointSecret()
    {
        $key = Environment::getEnv('STRIPE_WEBHOOK_SECRET');

        if (!$key) {
            throw new \RuntimeException('$endpoint_secret needs to be configured via .env');
        }

        return $key;
    }

    /**
     * Returns a multi-dimensional array of classes indexed by the event they handle
     *
     * @return array|mixed
     */
    public function getHandlers()
    {
        /**
*
         *
 * @var CacheInterface $cache
*/
        $cache = Injector::inst()->get(CacheInterface::class . '.stripeWebhook');

        if ($manifest = $cache->get('eventHandlers')) {
            return $manifest;
        }

        $classes = ClassInfo::subclassesFor(StripeEventHandler::class);
        $manifest = [];

        /**
*
         *
 * @var StripeEventHandler $class
*/
        foreach ($classes as $class) {
            if ($class == StripeEventHandler::class) {
                continue;
            }

            $handlerFor = $class::config()->get('events');

            if (!$handlerFor) {
                throw new \InvalidArgumentException($class . ' is missing private static $events');
            }

            if (is_array($handlerFor)) {
                foreach ($handlerFor as $event) {
                    $manifest[$event][] = $class;
                }
                continue;
            }

            if (!is_string($handlerFor)) {
                throw new \InvalidArgumentException('Invalid type, expecting string or array but got ' . gettype($handlerFor) . ' instead');
            }

            $manifest[$handlerFor] = $class;
        }

        $cache->set('eventHandlers', $manifest);

        return $manifest;
    }

    /**
     * This function is triggered early in the request if the "flush" query
     * parameter has been set. Each class that implements Flushable implements
     * this function which looks after it's own specific flushing functionality.
     *
     * @see FlushMiddleware
     */
    public static function flush()
    {
        /**
*
         *
 * @var CacheInterface $cache
*/
        $cache = Injector::inst()->get(CacheInterface::class . '.stripeWebhook');
        $cache->delete('eventHandlers');
    }
}
