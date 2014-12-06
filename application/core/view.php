<?php

class View {

    function __construct() {
        
    }
    
    public function generate($view,$data=array()) {
        include Q_PATH.'/application/views/template.php';
    }

}