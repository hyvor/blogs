<?php

namespace App\Console\Commands\Cloud;

use App\Models\Blog;
use Illuminate\Console\Command;

class Sep2024DisableBlogBrandingForCurrentCustomers extends Command
{

    protected $signature = 'cloud:sep2024:disable-blog-branding-for-current-customers';

    protected $description = 'Disable blog branding for current customers';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $this->info('Disabling blog branding for current customers...');

        $blogs = Blog::all();

        foreach ($blogs as $blog) {
            $this->info('Disabling blog branding for blog: ' . $blog->subdomain . '(ID: ' . $blog->id . ')');
            $blog->setMeta('hb_branding', false);
        }

        $this->info('Done!');
    }

}