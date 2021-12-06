<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
// use Path\To\DOMDocument;
use App\Repositories\ImportExportRepositoryInterface;


class ImportExportController extends Controller
{

    private $importExportRepo;

    public function __construct(ImportExportRepositoryInterface $importExportRepository)
    {
        $this->importExportRepo = $importExportRepository;
    }

    public function index(){

        // return view('history.history');

        $platform = "ghost";
        
        // Wordpress Import Section
        if($platform == "wordpress"){

            $wordpressPath = 'tools/Import/test.xml';
            $rss = new \DOMDocument();
            $rss->load($wordpressPath);
            $feed = array();
            foreach ($rss->getElementsByTagName('item') as $node) {
               $item = array (
                    'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                    'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                    'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                    'description' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                    'content' => trim(strip_tags($node->getElementsByTagName('encoded')->item(0)->nodeValue))
                    );

                if ($item['content'] != null){
                    array_push($feed, $item);
                }
            }
            $check = json_encode($feed, JSON_FORCE_OBJECT);

        }

        // Ghost Import Section
        elseif($platform == "ghost"){

            $ghostPath = "tools/Import/test.json";
            $ghostJsonFile = file_get_contents($ghostPath);  
            $ghostArray = json_decode($ghostJsonFile, true);

            if ($ghostArray["db"][0]["data"]["posts"] != null){

                $arraycheck = $ghostArray["db"][0]["data"]["posts"];

                // $arr[$newkey] = $arr[$oldkey];
                // $ghostItem = array ($arraycheck['title'], $arraycheck['plaintext'], $arraycheck['published_at'], $arraycheck['custom_excerpt']);

                // $ids = array_column($ghostItem);

                // $userNames = array_reduce($ghostItem, function ($carry, $user) {
                //     array_push($carry, $user['title']);
                //     // return $carry;
                // }, []);
            }

            $check = json_encode($arraycheck, JSON_FORCE_OBJECT);
        }
        else{
            echo 'hello world none';
        }

        $final = json_decode($check, true);

        $this->importExportRepo->index($final);

        // Blogger Import Section
        // $bloggerPath = "tools/Import/test.xml";
        // $bloggerXmlFile = file_get_contents($bloggerPath); 
        // $bloggerNew = simplexml_load_string($bloggerXmlFile); 
        // $bloggerCon = json_encode($bloggerNew); 
        // $bloggerArr = json_decode($bloggerCon, true); 
        // dd($bloggerArr);

        // Medium Import Section
        // $mediumPath = "tools/Import/test.html";
        // $mediumXmlFile = file_get_contents($mediumPath);
        // // $mediumArr = json_decode($mediumXmlFile, true); 
        // $string = preg_replace('#<ul(.*?)>(.*?)</ul>#is', '', $mediumXmlFile);   // Remove script tag
        // // $string = str_replace(' ', '', $string); // Remove space from string
        // $string = preg_replace('/\s+/', '', $string); // Remove whitespace from string
        // $mediumXmlFile = explode(',',$string); // explode string 
        // dd($mediumXmlFile);

        return view('test.test');
    } 
}
