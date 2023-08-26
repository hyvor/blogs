<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleGptController
{

    public function newPrompt(Request $request, Blog $blog) : JsonResponse
    {

        $request->validate([
            'post_id' => 'required|integer',
            'prompt' => 'required|string'
        ]);

        return response()->json();

    }

}