<?php

namespace Vulcan\StripeWebhook\Models;

use SilverStripe\ORM\DataObject;

/**
 * Class EventOccurrence
 * @package Vulcan\StripeWebhook
 *
 * @property string EventID
 * @property string Type
 * @property string Handlers
 * @property string HandlerResponses
 * @property string Data
 * @property int    Occurrences
 */
class EventOccurrence extends DataObject
{
    private static $table_name = 'StripeEventOccurrence';

    private static $db = [
        'EventID'          => 'Varchar(255)',
        'Type'             => 'Varchar(255)',
        'Handlers'         => 'Text',
        'HandlerResponses' => 'Text',
        'Data'             => 'Text',
        'Occurrences'      => 'Int'
    ];

    private static $defaults = [
        'Occurrences' => 1
    ];

    /**
     * @param $eventId
     *
     * @return static|DataObject
     */
    public static function getByEventID($eventId)
    {
        return static::get()->filter('EventID', $eventId)->first();
    }
}