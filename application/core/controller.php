<?php

class controller {

    function __construct() {
        
    }
    
    public function actionIndex() {
        $model = new modelIndex('localhost', 'blog', 'krava', '');
        $view = new view();
        $view->generate('Index', $model->getName());
    }

}