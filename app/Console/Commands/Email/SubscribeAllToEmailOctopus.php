<?php

namespace App\Console\Commands\Email;

use App\Models\Blog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SubscribeAllToEmailOctopus extends Command
{

    protected $signature = 'email:subscribe-all';

    protected $description = 'Subscribe all users to Email Octopus';

    public function handle()
    {
        $this->info('Subscribing all users to Email Octopus');

        $listId = config('services.email_octopus.list_id');

        $users = Blog::selectRaw('DISTINCT hyvor_user_id')
            ->whereNotNull('email')
            ->get();

        $response = Http::post(
            "https://emailoctopus.com/api/1.6/lists/$listId/contacts",
            [
                'api_key' => config('services.email_octopus.api_key'),
                'email_address' => 'test@hyvor.com',
                'status' => 'SUBSCRIBED',
                'fields' => [
                    'FirstName' => 'Test',
                ]
            ],
        );

        $this->info($response->body());

        $this->info('Done');
    }

}