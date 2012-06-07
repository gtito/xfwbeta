<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XError
 *
 * @author Gt
 */
class XError {
    public static function log($message,$file='error.log'){error_log("\n".$message,3,$file);}
}

?>
