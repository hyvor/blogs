<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Themes;

class ThemeBuilderController extends Controller
{
    public function createThemeForm()
    {
        return view('theme_builder.create');
    }

    public function saveTheme(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'header' => 'required',
            'footer' => 'required',
            'pageBody' => 'required',
            'postBody' => 'required',            
        ]);

        $thm = new Themes();
        $thm->title = $request->title;
        $thm->header = $request->header;
        $thm->footer = $request->footer;
        $thm->pageBody = $request->pageBody;
        $thm->postBody = $request->postBody;
        $thm->author = '1';

        $thm->save();
        return redirect('/dashboard')->with('success','Theme created successfully!');
    }

    public function themesListAll()
    {
        $themes = Themes::all();
        $data['themes'] = $themes;
        return view('theme_builder.index', $data);
    }

    public function destroy(Themes $theme)
    {
        $theme->delete();
        return redirect('/theme/list')->with('success','Theme deleted successfully!');
    }




}