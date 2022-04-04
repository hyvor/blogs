<?php

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\Content\SyntaxHighlighting;
use App\Jobs\Scheduled\Counts\BlogCountJob;
use App\Models\Blog;
use App\Models\Post;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Support\Facades\Route;
use MeiliSearch\Client;

Route::get('/run-blog-counts', function() {
    dispatch(new BlogCountJob);
});

Route::get('/tiptap', function() {
    PostContentRepository::getHtml('', Blog::find(1));
});

// syntax highlighting
Route::get('/syntax', function() {

    $value = <<<JS
    function getName(user) {
        let name = user.name;
        if (name === null) {
            throw new Error('A girl has no name');
        }
        return <div>WOW</div>
    }

    function makeFriends(user1, user2) {
        user1.friendNames.push(getName(user2));
        user2.friendNames.push(getName(user1));
    }

    const arya = { name: null, friendNames: [] };
    const gendry = { name: 'Gendry', friendNames: [] };
    try {
        makeFriends(arya, gendry);
    } catch (err) {
        console.log("Oops, that didn't work out: ", err);
    }
    JS;

    $value = file_get_contents('https://prismjs.com/prism.js');

    $code = Highlighter::highlight($value, 'js', 'monokai', true, '');

    return response(<<<HTML
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.0/styles/night-owl.min.css" integrity="sha512-i5X6Fdn/ZqvGSqPrdMa3FgcpXM/Nr6YccSKFYT93zljl/HZDEpvBbE5Pxp91eiWGccZLrL/LDQJd7fjTRYsVaA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .highlighted-line {
            background-color: #022a4b;
            border-left: 0.25em solid #ffa7c4;
        }
        pre code {
            display: block;
            padding: 20px 0;
            line-height: 1.5;
            font-family: Consolas,Menlo,Monaco,source-code-pro,Courier New,monospace;
            font-size:13.6px;
        }
        pre code div {
            padding: 0 20px;
        }
    </style>

    $code
    HTML);

});