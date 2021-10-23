<?php

namespace App\Classes;

use App\Models\BlogsToThemes;

class Theme
{

    public $renderedHTML;
    public function __construct($data) {
        $blog = $data['page'];
        // dd($blog);
        $thmObj = BlogsToThemes::where('blog_id', '=', $blog['blog_id'])->get();

        $data['theme'] = array(
            'title' => 'Default Theme',
            'styles1' => '',
        );

        $content = "";
        $html = "";
        foreach($thmObj as $atheme){

            $data['theme']['title'] = $atheme->parent_theme_id;
            $data['theme']['styles1'] = $atheme->styles_1;


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
                $content .= $atheme->post_body;
            } elseif( $data['type'] == 'post-edit' ) { 
                // dd($data['post']); 
            } elseif( $data['type'] == 'blog' ) {  
                $content .= $atheme->page_body;         
            } else {
                $content .= $atheme->page_body;    
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
