<?php

namespace App\Service\App\Messenger;

use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class MessageTransport
{
    public const ASYNC = 'async';
    public const SYNC = 'sync';

    public static function syncStamp(): TransportNamesStamp
    {
        return new TransportNamesStamp([self::SYNC]);
    }
}
