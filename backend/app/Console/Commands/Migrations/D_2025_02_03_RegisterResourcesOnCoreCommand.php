<?php

namespace App\Console\Commands\Migrations;

use App\Models\Blog;
use Hyvor\Internal\InternalApi\Exceptions\InternalApiCallFailedException;
use Hyvor\Internal\Resource\Resource;
use Illuminate\Console\Command;

class D_2025_02_03_RegisterResourcesOnCoreCommand extends Command
{

    protected $signature = 'migrations:register-resources-on-core';

    protected $description = 'Register resources on core';

    public function handle(): void
    {
        $this->info('Registering resources on core...');

        $blogs = Blog::orderBy('id')->get();
        /** @var Resource $resource */
        $resource = app(Resource::class);

        foreach ($blogs as $blog) {
            if ($blog->hyvor_user_id === null) {
                $this->info('Skipping blog: ' . $blog->id . ' (no user ID)');
                continue;
            }

            $this->info('Registering blog: ' . $blog->id);

            // Register blog on core
            try {
                $resource->register($blog->hyvor_user_id, $blog->id, $blog->created_at);
            } catch (InternalApiCallFailedException $e) {
                $message = $e->getMessage();

                if (str_contains($message, 'User not found')) {
                    $this->info('Skipping blog: ' . $blog->id . ' (user not found)');
                } else {
                    throw $e;
                }
            }
        }
    }

}
