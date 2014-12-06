<?php

class controller404 extends controller {

    function __construct() {
        
    }
    
    public function actionIndex() {
        $view = new view();
        $view->generate('404');
    }

}