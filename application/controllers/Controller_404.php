<?php

class Controller_404 extends Controller {

    function __construct() {
        
    }
    
    public function Action_index() {
        $view = new View();
        $view->generate('404');
    }

}