<?php

class controller {

    function __construct() {
        
    }
    
    public function actionIndex() {
        $model = new modelIndex();
        $view = new view();
        $view->generate('Index', $model->getName());
    }

}