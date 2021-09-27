<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Themes;
// use Theme;

class ThemeBuilderController extends Controller
{
    public function createTheme()
    {
        // Theme::uses('demoone');
        // return Theme::view('theme_builder.create');
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

    public function loadTheme($id)
    {
        // $thm = new Themes();
        $srvLiquid = "";
        $thmObj = Themes::where('id', '=', 2)->get();
        foreach($thmObj as $atheme){
            $srvLiquid .= $atheme->header;
            // $srvLiquid .= $atheme->pageBody;
            $srvLiquid .= $atheme->postBody;
            $srvLiquid .= $atheme->footer;
        }
        return $srvLiquid;
    }


}
