<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogsToThemes;

class BlogThemeCustomizerController extends Controller
{

    public function index()
    {
      
    }

    public function create()
    {

    }

    public function store(Request $request,$data)
    {
        $blog = $data['blog_id'];
        $orgiTheme = $data['theme'];
        $instance = new BlogsToThemes();
        $instance->blog_id = $data['blog_id'];
        $instance->parent_theme_id = $orgiTheme->id;
        $instance->header = $orgiTheme->header;
        $instance->footer = $orgiTheme->footer;
        $instance->page_body = $orgiTheme->pageBody;
        $instance->post_body = $orgiTheme->postBody;
        $instance->styles_1 = $orgiTheme->styles_1;
        $instance->revision_id = 0;
        $instance->is_unsaved = false;

        $instance->save();
    }

    public function show(Blog $blog)
    {
    }

    public function edit(Blog $blog)
    {

    }

    public function update(Blog $blog, Request $request)
    {

    }

    public function destroy(Themes $theme)
    {
        $theme->delete();
        return redirect('/theme/list')->with('success','Theme deleted successfully!');
    }
}
