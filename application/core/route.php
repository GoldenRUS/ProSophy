<?php

class Route {

    function __construct() {
        
    }
    
    public static function Start() {
            //назначение параметров по умолчанию
            $controllerName = 'Index';
            $actionName = 'Index';
            $actionParameters = array();
            
            //преобразуем строку запроса в массив
            $url = explode('/', $_SERVER['REQUEST_URI']);
            
            if(!empty($url[1])){
                $controllerName = $url[1];
            }

            if(!empty($url[2])){
                $actionName = $url[2];
            }
            
            // добавляем префиксы
            $modelName = 'model'.$controllerName;
            $controllerName = 'controller'.$controllerName;
            $actionName = 'action'.$actionName;
            
            if(file_exists(Q_PATH.'/application/model/'.$modelName.'.php')){
                include Q_PATH.'/application/model/'.$modelName.'.php';
            }

            if(file_exists(Q_PATH.'/application/controller/'.$controllerName.'.php')){
                include Q_PATH.'/application/controller/'.$controllerName.'.php';
            }else{
                header('Location: /404');
                exit;
            }

            $controller = new $controllerName();
            $controller->$actionName();
            
    }

}
