<?php

namespace App\Domains\Integrations\EmailOctopus;

use Exception;
use Illuminate\Support\Facades\Http;

class EmailOctopusService
{

    public static function subscribeUser(string $email, string $name, string $listId = null) : void
    {

        $listId ??= strval(config('services.email_octopus.list_id'));

        $response = Http::post(
            "https://emailoctopus.com/api/1.6/lists/$listId/contacts",
            [
                'api_key' => config('services.email_octopus.api_key'),
                'email_address' => $email,
                'status' => 'SUBSCRIBED',
                'fields' => [
                    'FirstName' => $name,
                ]
            ],
        );

        if (!$response->successful()) {
            throw new Exception('Failed to subscribe user');
        }

    }

}