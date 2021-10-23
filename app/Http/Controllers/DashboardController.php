<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Theme;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {

        $data['posts'] = "Crazy";
        // Theme::uses('demoone');
        // return Theme::view('dashboard.panel', $data);
        return view('dashboard.panel', $data);

    }

    public function logIn()
    {
        $userdata = array(
                'email'     => 'chris@scotch.io',
                'password'  => 'awesome'
        ); 

        // $remember_me = $request->has('remember') ? true : false;
        $remember_me = true;

        if (Auth::attempt($userdata,$remember_me)) {
            // return true;
            // $usermodel = new User();
            // Auth::login($usermodel, $remember = true);
              $user = Auth::user();
              Auth::login($user,true);
              // dd($user);

            echo "Welcome ". $user->name;
        } else {
            // return false;
            echo "fail";
        }

    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
        Auth::logout();
        return redirect('/dashboard');
        } else {
        echo "not logged in";
        }
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();
        // Session::flush();
        
    }


    public function createUser()
    {
        // DB::table('users')->delete();
        User::create(array(
            'name'     => 'Chris Sevilleja',
            'username' => 'sevilayha',
            'email'    => 'chris@scotch.io',
            'password' => Hash::make('awesome'),
        ));

        echo "Done..";
    }


}
