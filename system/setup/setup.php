<?php

$setup['database']=array('host'=>'localhost','user'=>'root','password'=>'gino','port'=>'3306','name'=>'enperuyoviajo');
$setup['system']="C:/Servidor/www/xfw/";
$setup['core']=$setup['system']."system/core/";
$setup['model']=$setup['system']."system/model/";
$setup['controller']=$setup['system']."system/controller/";
$setup['view']=$setup['system']."system/view/";
$setup['view_default']='default';
$setup['view_not_found']='not_found';

$setup['url_map']=array('c'=>'c','m'=>'m','p'=>'p','s'=>'/');

function class_exist($classname,&$path){
    global $setup;
    if(file_exists($path=$setup['core']."$classname.php")) return true;
    else if(file_exists($path=$setup['controller']."$classname.php")) return true; 
    else if(file_exists($path=$setup['model']."$classname.php")) return true; 
    else if(file_exists($path=$setup['view']."$classname.php")) return true;
    return false;
}
function __autoload($classname) {
    if(class_exist($classname,$path))
        include_once $path;
}

?>
