<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Path\To\DOMDocument;
use App\Domains\ImportExportRepositoryInterface;
use Session;

class ImportExportController extends Controller
{
    private $importExportRepo;

    public function __construct(ImportExportRepositoryInterface $importExportRepository)
    {
        $this->importExportRepo = $importExportRepository;
    }

    public function index()
    {

        // return view('history.history');

        $platform = "ghost";

        // Wordpress Import Section
        if ($platform == "wordpress") {
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
                    'creator' => $node->getElementsByTagName('creator')->item(0)->nodeValue,
                    'post_modified' => $node->getElementsByTagName('post_modified')->item(0)->nodeValue,
                    // 'category' => $node->getElementsByTagName('category').getAttribute('post_tag')->item(0)->nodeValue,
                    'content' => trim(strip_tags($node->getElementsByTagName('encoded')->item(0)->nodeValue))
                    );
                if ($item['content'] != null) {
                    array_push($feed, $item);
                }
            }
            $check = json_encode($feed, JSON_FORCE_OBJECT);
        }

        // Ghost Import Section
        elseif ($platform == "ghost") {
            $ghostPath = "tools/Import/test.json";
            $ghostJsonFile = file_get_contents($ghostPath);
            $ghostArray = json_decode($ghostJsonFile, true);

            $postArray = $ghostArray["db"][0]["data"]["posts"];
            $userArray = $ghostArray["db"][0]["data"]["users"];

            // print_r($ghostArray);
            // $test = $ghostArray["db"][0]["data"]["posts"][0]->id;
            // dd($test);

            if ($postArray != null) {
                foreach ($postArray as $single) {
                    dd($single);
                }

                // User
                $arrayUser = $userArray;
                // posts
                $arraycheck = $postArray;
            }

            $check = json_encode($arraycheck, JSON_FORCE_OBJECT);
        } else {
            echo 'hello world none';
        }

        $final = json_decode($check, true);

        $this->importExportRepo->index($final);

        // Creating a common format with html
        // $hyvorBlogHtml = "Hyvor blog html file";
        // $hyvorBlogHtml .= "<ul>";

        // foreach($final as $html)
        // {
        //     $hyvorBlogHtml .= "<li>$html</li>";
        // }

        // $hyvorBlogHtml .= "</ul>";

        // echo $hyvorBlogHtml;


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
