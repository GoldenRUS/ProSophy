<?php
/**
 * Created by PhpStorm.
 * User: Ярослав
 * Date: 05.12.2014
 * Time: 23:02
 */

function __autoload($class_name) {
    include $class_name . '.php';
}

class Index extends DB{

    function test(){
        $mas = $this->DBselect('wp_users');
        return $mas;
    }

}

header('Content-Type: text/html; charset=utf-8');

$index  = new Index('localhost', 'blog', 'krava', '');
//var_dump($index->getTables());
var_dump($index->test());
?>