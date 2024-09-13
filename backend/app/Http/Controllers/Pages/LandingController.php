<?php declare(strict_types=1);

namespace App\Http\Controllers\Pages;

use App\Domains\Theme\ThemeRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LandingController 
{

    public const FOR = [

        'students' => 'Students',
        'teachers' => 'Teachers',
        'journalists' => 'Journalists',
        'startups' => 'Startups',
        'businesses' => 'Businesses',
        'developers' => 'Developers',
        'professionals' => 'Professionals',
        'programmers' => 'Programmers',
        'designers' => 'Designers',
        'freelancers' => 'Freelancers',
        'bloggers' => 'Bloggers',
        'writers' => 'Writers',
        'authors' => 'Authors',
        'publishers' => 'Publishers',
        'marketers' => 'Marketers',
        
    ];

    public function for(Request $request) : mixed
    {

        $type = is_string($request->route('type')) ? strval($request->route('type')) : '';

        if (!array_key_exists($type, self::FOR)) {
            return abort(404);
        }

        return view('landing.for', [
            'slug' => $type,
            'name' => self::FOR[$type],
        ]);

    }


    public function sitemap() : Response
    {

        $slugs = [
            '',
            '/pricing',
            '/themes',
        ];
        
        // docs
        $docsNav = require base_path('resources/docs/nav.php');

        foreach ($docsNav as $section => $pages) {
            foreach ($pages as $page) {
                if (is_array($page)) {
                    $slugs[] = '/docs' . ($page[0] ? '/' . $page[0] : '');
                }
            }
        }

        // /for/
        foreach (self::FOR as $slug => $name) {
            $slugs[] = '/for/' . $slug;
        }

        // /themes/
        $themes = ThemeRepository::getAllThemesWithLatestVersions();
        foreach ($themes as $theme) {
            if ($theme->name === 'hello' || $theme->name === 'blank')
                continue;
            $slugs[] = '/themes/' . $theme->name;
        }

        $appUrl = strval(config('app.url'));
        $appUrl = $appUrl ? $appUrl : 'https://blogs.hyvor.com';

        $urls = array_map(fn ($slug) => $appUrl . $slug, $slugs);
        $str = implode("\n", $urls);

        return response($str, 200)
            ->header('Content-Type', 'text/plain');
    }


}