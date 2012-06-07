<?php
/**
 * Description of XModel
 *
 * @author Gt
 */
class XModel {
    private $data=array();
    public function __construct() {
        global $setup,$xcore;
        $this->data['setup']=$setup;
        $this->data['xcore']=$xcore;
    }
    public function __get($name) { return $this->data[$name]; }

}

?>
