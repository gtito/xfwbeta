<?php
/**
 * Description of XView
 *
 * @author Gt
 */
class XView {
    private $data=array();
    public function __construct() {
        global $setup,$xcore;
        $this->data['setup']=$setup;
        $this->data['xcore']=$xcore;
    }
    public function __get($name) { return $this->data[$name]; }

}

?>
