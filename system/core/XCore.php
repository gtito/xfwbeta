<?php
/**
 * Description of XCore
 *
 * @author Gt
 */
class XCore{
    private $data=array();
    public function __construct() {
        global $setup;
        $this->data['setup'] = $setup;
        $this->data['model'] = new XModel();
        $this->data['view'] = new XView();
        $this->data['controller'] = new XController();
    }
    public function __get($name) { return $this->data[$name]; }
    public function run(){
        if(!$this->_isset('c')){
            $this->controller->load_view($this->setup['view_default']);
        }else{
            $this->execute($this->_get('c'),$this->_get('m'),$this->_get('p'));
        }
    }
    public function execute($controller,$method,$parameter){
        if(class_exist($controller,$path)){
            $c=new $controller;
            $c->__call($method,explode($this->setup['url_map']['s'],$parameter));
        }else
             $this->controller->load_view($this->setup['not_found']);
    }
    public function _isset($purl) { return isset($_GET[$this->setup['url_map'][$purl]]); }
    public function _set($purl,$value) { $_GET[$this->setup['url_map'][$purl]]=$value; }
    public function _get($purl) { return $_GET[$this->setup['url_map'][$purl]]; }
}

?>
