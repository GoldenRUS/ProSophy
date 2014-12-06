<?php

class Controller {

    function __construct() {
        
    }
    
    public function Action_index() {
        $model = new Model_index();
        $view = new View();
        $view->generate('index', $model->getName());
    }

}