<?php
/**
 * Description of XController
 *
 * @author Gt
 */
class XController {
    private $data=array();
    public function __construct() {
        global $setup,$xcore;
        $this->data['setup']=$setup;
        $this->data['xcore']=$xcore;
    }
    public function __get($name) { return $this->data[$name]; }
    public function __call($name, $arguments) {
        switch ($name){
            case 'view':
                $this->load_view($this->xcore->_get('c'));
            break;
            default:
                $this->__call('view',$arguments);
            break;
        }
    }
    public function load_view($name){
        if(file_exists($this->setup['view'].$name.".php"))  
            include_once $this->setup['view'].$name.".php";
        else
            include_once $this->setup['view'].$this->setup['view_not_found'].".php";
    }
    
}

?>
