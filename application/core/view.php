<?php

class view {

    function __construct() {
        
    }
    
    public function generate($view, $data = array()) {
        include Q_PATH.'/application/view/template.php';
    }

}