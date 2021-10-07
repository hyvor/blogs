<?php

namespace App\Classes;

use App\Models\Themes;

class Theme
{

    public $renderedHTML;
    public function __construct($data) {
        $id = 0;
        $thmObj = Themes::where('id', '=', $id)->get();

        $data['theme'] = array(
            'title' => 'Default Theme',
        );

        $content = "";
        $html = "";
        foreach($thmObj as $atheme){
            $html .= "<html>";
            $html .= "<head>";
            $html .= $atheme->header;
            $html .= "</head>";
            $html .= "<body>";
            $html .= "<h3>Laravel - Twig tests</h3>";
            $html .= "<div class='container'>";
            $html .= "{% block content %}";
            $html .= "<div class='content-div'>Bustee</div>";
            $html .= "{% endblock content %}";
            $html .= "</div>";
            $html .= $atheme->footer;
            $html .= "</body>";
            $html .= "</html>";

            $content = "{% extends 'baseTemplate.php' %}";
            $content .= "{% block content %}";
            if( $data['type'] == 'post' ) {                
                $content .= $atheme->postBody;
            } elseif( $data['type'] == 'post-edit' ) { 
                // dd($data['post']);           
            } else {
                $content .= $atheme->pageBody;    
            }
            $content .= "{% endblock content %}";

        }

        $loaderBase = new \Twig\Loader\ArrayLoader([
            'baseTemplate.php' => $html,
        ]);

        $loaderContent = new \Twig\Loader\ArrayLoader([
            'content.php' => $content,
        ]);

        $loaderFinal = new \Twig\Loader\ChainLoader([$loaderBase, $loaderContent]);

        $twig = new \Twig\Environment($loaderFinal);
        // $template = $twig->load('content.php'); 

        $this->renderedHTML = $twig->render('content.php',$data);
        // $this->renderedHTML .= $template->renderBlock('content', $data);
    }

    public function view() {
        return $this->renderedHTML;
    }

    public function printVar($post) {
        dd($this->renderedHTML);
    }

}
