<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Domains\Import\Parsers\WordpressParser;
use App\Models\User;
use App\Models\UserVariant;
use Session;

class ParserController extends Controller
{
    public function index()
    {

        $file  = file_get_contents('wordpress/wordpress.xml');
        
        $parser = new WordpressParser($file);
        
        if(!empty($parser->authors)){
            foreach($parser->authors as $author_key=>$authors){
                $user = new User();

                $user->createdAt = $authors['createdAt'];    
            }
        }
    }
}