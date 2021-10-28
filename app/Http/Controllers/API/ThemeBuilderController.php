<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Themes;

class ThemeBuilderController extends Controller
{


    public function index()
    {
        $themes = Themes::all();
        return $themes;
    }

    public function show(Themes $theme)
    {
        return $theme;
    }

    public function store(Request $request)
    {
        $thm = new Themes();
        $thm->title = $request->title;
        $thm->header = $request->header;
        $thm->footer = $request->footer;
        $thm->pageBody = $request->pageBody;
        $thm->postBody = $request->postBody;
        $thm->styles_1 = $request->styles1;
        $thm->author = '1';
        $thm->save();

        return response()->json($thm, 201);
    }

    public function update(Request $request, Themes $theme)
    {
        return response()->json($theme, 200);
    }

    public function destroy(Themes $theme)
    {
        $theme->delete();
        return response()->json(null, 204);
    }


}