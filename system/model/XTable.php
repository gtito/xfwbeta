<?php
/**
 * Description of XTabla
 *
 * @author Gt
 */
class XTable {
    protected $table=array();
    protected $data=array();
    protected $html_names=array();
    protected $html_prefix='x_';
    protected $hidden=array('table'=>true);
    protected $list=array();
    protected $instances=array();
    public function after_construct() { ; }
    public function __construct() {
        $this->set_html_prefix($this->html_prefix);
        $this->set_instances();
        $this->after_construct();
    }
    public function __set($name, $value) { $this->data[$name]=$value; }
    public function __get($name) { return $this->data[$name]; }
    public function __isset($name) { return isset($this->data[$name]); }
    protected function set_instances(){
        foreach ($this->instances as $name => $xtable)
            $this->data[$name."_instance"]=new $xtable;
    } 
    public function clear_data(){
        foreach ($this->table as $key => $value) 
            if(!isset ($this->hidden[$key]))
                $this->data[$key]=null;
    }    
    public function set_html_prefix($html_prefix){
        $this->html_prefix=$html_prefix;
        foreach ($this->table as $key => $value) 
            if(!isset ($this->hidden[$key]))
                $this->html_names[$key]=$this->html_prefix.$value;
    }
    public function set_data_array($array){
        foreach ($this->table as $key => $value) {
            if(!isset ($this->hidden[$key]) && isset ($array[$key]))
                $this->__set($key,$array[$key]);
        };
    }
    public function set_data_html($post=true){
        ($post)?($method = &$_POST):($method = &$_GET);
        foreach ($this->table as $key => $value)
            if(!isset ($this->hidden[$key]) && isset ($method[$this->html_names[$key]]))
                $this->__set($key,$method[$this->html_names[$key]]);
    }
    public function set_list_data_html($post=true,$return=false){
        ($post)?($method = &$_POST):($method = &$_GET);
        $this->list=array();
        $n=0;$req="";$j=0;
        foreach ($this->html_names as $key => $value)
            if(isset ($method["n".$this->html_names[$key]]))
                $n=$method["n".($req=$this->html_names[$key])];
        for($i=0;$i<$n;$i++){
            if(isset ($method[$i.$req])) {
                $list[$j]=$this->instance();
                $list[$j]->set_html_prefix($i.$this->prefix_html);
                $list[$j++]->set_data_html($post);
            }
        }
        if($return) return $list;
    }

    public function set_data_row($row){
        foreach ($this->table as $key => $value) 
            if(!isset ($this->hidden[$key]) && isset($row[$value]))
                $this->__set($key,$row[$value]);
    }
    public function get_html_names($data=null){
        $names=array();
        $data=( ($all=!is_array($data))?(array()):($data) );
        foreach ($this->html_names as $key => $value) 
            if($all || isset ($data[$key]))
                $names[$key]=$value;
        return $names;
    }
    public function get_array($data=null){
        $array=array();
        $data = (($all = !is_array($data))?(array()):($data));
        foreach ($this->table as $key => $value)
            if(!isset($this->hidden[$key]) && ($all || isset($data[$key])) )
                $array[$key] = $this->__get($key);
        return $array;
    }
    protected function get_insert_array(){
        $insert_array=array();
        foreach ($this->table as $key => $value)
            if(!isset($this->hidden[$key]) && $this->__isset($key) )
                $insert_array[$value] = $this->__get($key);
        return $insert_array;
    }
    protected function instance() { return new XTable(); }
    protected function get_index_condition(){return "{$this->tabla['id']}='$this->id'";}
    protected function set_id($id){$this->id=$id;}

    public function save(){return $this->_save();}
    protected function _save(){
        $database=new XDatabase();
        $exito=$database->insert(array(
            'table'=>$this->table['table'],
            'insert'=>$this->get_insert_array()
        ));
        if(!$exito)
            XError::log($database->sql."\n".$database->error);
        else
            $this->set_id($database->get_last_id());
        return !($exito===false);
    }
    public function load($list=false){return $this->_load($list);}
    protected function _load($list=false,$condition=null){
        $condition=((!$list)?$this->get_index_condition():(isset ($condition)?$condition:"1"));
        $database=new XDatabase();
        $result=$database->select(array(
            'table'=>$this->table['table'],
            'select'=>array("*"),
            'condition'=>$condition
        ));
        if($result==false)
            XError::log($database->sql."\n".$database->error);
        $exito=((!$list)?($this->load_self($result)):($this->load_list($result)));
        return $exito;
    }
    protected function load_self(&$result){
        $row=mysql_fetch_array($result,MYSQL_ASSOC);
        if( ($exito = ($row!=false)) )
            $this->set_data_row($row);
        return $exito;
    }
    protected function load_list(&$result){
        $this->list = array();$i=0;
        while($row=mysql_fetch_array($result,MYSQL_ASSOC)){
            $this->list[$i]=$this->instance(); 
            $this->list[$i++]->set_data_row($row);
        }
        return $this->list;
    } 
    public function load_by($data=null,$lista=false){
        if(is_array($data)){
            $condicion=array();
            foreach ($data as $key) 
                if(isset ($this->table[$key]))
                    $condicion[]="{$this->table[$key]}='{$this->__get($key)}'";
            if(count($condicion)!=0)
                return $this->_load($lista, implode(" AND ", $condicion));
        }
        return false;
    }
    public function update(){return $this->_update();}
    protected function _update($condition=null){
        $condition = isset ($condition)?$condition:$this->get_index_condition();
        $database=new XDatabase();
        $exito=$database->update(array(
            'tabla'=>$this->tabla['tabla'],
            'update'=>$this->get_insert_array() ,
            'condition'=>$condition
        ));
        if(!$exito)
            XError::log($database->sql."\n".$database->error);
        return !($exito===false);
    }
    public function delete(){return $this->_delete();}
    protected function _delete($condition = null){
        $condition = (isset ($condition))?($condition):($this->condicion_index());
        $database=new XDatabase();
        $exito = $database->delete(array(
            'table'=>$this->tabla['tabla'],
            'condition'=>$condition
        ));
        if(!$exito)
            XError::log($database->sql."\n".$database->error);
        return !($exito===false);
    }
    public function get_tabla_join($name=null){
        $table=array();
        $name = isset($name)?($name):($this->table['table']);
        foreach ($this->table as $key => $value)
            $table[$key]=array('o'=>$value,'j'=>"{$name}.{$value}");
        $table['table']['j']=$this->table['table'].(($name==$this->table['table'])?"":" $name");
        return $table;
    }
}

?>
