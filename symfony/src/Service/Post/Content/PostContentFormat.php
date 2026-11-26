<?php

namespace App\Service\Post\Content;

enum PostContentFormat: string
{
    case MARKDOWN = 'markdown';
    case HTML = 'html';
    case JSON = 'json';
}
