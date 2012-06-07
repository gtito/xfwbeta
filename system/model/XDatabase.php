<?php
/**
 * Description of XDatabase
 *
 * @author Gt
 */
class XDatabase {
    private $type='mysql';
    private $link=null;
    private $database=null;
    private $database_connection=null;
    private $data=array();
    private $setup=array();
    /**
     * Class to control databases
     * @param string $type Type of database.
     * @param array $database Configuration of database an array with key ('host','user','password','port','name').
     */
    public function __construct($type=null,$database=null) {
        global $setup; $this->setup = $setup;
        $this->type=isset($type)?$type:$this->type;
        $database=isset($database)?$database:$setup['database'];
        $this->conect($database);
        $this->select_database($database['name']);
    }
    public function __get($name) { 
        if($name=='error')  return mysql_error();
        return $this->data[$name]; 
    }
    public function __set($name, $value) { $this->data[$name]=$value; }
    public function __destruct() {mysql_close($this->link);}
    
    /**
     * Conect to database
     * @param array $database Configuration of database an array with key ('host','user','password','port').
     */
    public function conect($database){
        $this->link = mysql_pconnect($database['host'].":".$database['port'],$database['user'],$database['password']);
        if(!$this->link)
            die('Could not connect: ' . mysql_error($this->link));
        mysql_set_charset("utf8", $this->link);
    }
    
    /**
     * Select a database
     * @param string $database Name of database.
     */
    public function select_database($database){
        $this->database_connection=mysql_select_db($this->database = $database, $this->link);
        if(!$this->database_connection)
            die('Could not connect: ' . mysql_error($this->link));
    }
    
    /**
     * Execute sql query
     * @param string $sql sql query.
     * @return resource Result of a sql query.
     */
    public function execute($sql=null){
        $this->sql=(isset ($sql)?($sql):($this->sql));
        return mysql_query($this->sql,$this->link);
    }
    
    /**
     * Get select sql query or the result of query
     * @param array $struct Structure to form select query<br />(<br />
     * {string}'table'=><br />Table name,<br />{array}'select'=><br />Options to select,<br />{string}'condition'=><br />Condition,<br />
     * {string}'complement'=><br />Sql query to complement the selection (order by, limit, ...)<br />)
     * @param bool $return_sql [optional] If true then return sql query otherwise return result of query
     * @return mixed Depending of $return_sql, return a sql query or the result of query
     */
    public function select($struct,$return_sql=false){
        $select=(isset($struct['select'])?(implode(",", $struct['select'])):("*")) ;
        $condition=(isset ($struct['condition']) ?($struct['condition']):("1"));
        $complement=(isset ($struct['complement']) ?($struct['complement']):(""));
//        $this->sql=vsprintf("SELECT %s FROM %s WHERE %s %s", array( $select ,$struct['table'],$condition,$complement));
        $this->sql=sprintf("SELECT %s FROM %s WHERE %s %s",$select,$struct['table'],$condition,$complement);
        return ($return_sql?$this->sql:$this->execute());
    }
    
    /**
     * Insert in a table
     * @param array $struct Structure to form insert query<br />(<br />
     * {string}'table'=><br />Table name,<br />
     * {array}'insert'=><br />Form key=>value like column=>value to be inserted, if is a multiline insert, the array is bidimentional,<br />
     * {string}'finsert'=><br />Form key=>value like column=>format to set the format of each value by key,<br />
     * {string}'columns'=><br />Is needed in a multilne insert, this is the names of columns to insert, be careful with the position, the form key=>value is like column=>true<br />).
     * @param bool $multiline Indicate if is a multiline insert or not
     * @return resource Return tha result of insert
     */
    public function insert($struct,$multiline=false){
        $finsert = array();
        if(isset($struct['finsert'])){
            $row = (!$multiline)?$struct['insert']:$struct['columns'];
            foreach ($row as $key => $value) 
                $finsert[]= isset($struct['finsert'][$key])?$struct['finsert'][$key]:"'%s'";
        }
        if(!$multiline){
            $this->sql=sprintf("INSERT INTO %s (%s) VALUES (%s)",$struct['table'],implode(",", array_keys($struct['insert'])),$this->sql_implode(",",array_values($struct['insert']),$finsert,"'%s'"));
        }else{
            $values = array();
            foreach ($struct['insert'] as $insert) $values[] = "(".$this->sql_implode(",",array_values($insert),$finsert,"'%s'").")";
            $this->sql=sprintf("INSERT INTO %s (%s) VALUES %s", $struct['table'],implode(',',array_keys($struct['columns'])) ,implode(",",$values));
        }
        return $this->execute();
    }

    /**
     * Update in table
     * @param array $struct Structure to form update query<br />(<br />
     * {string}'table'=><br />Table name,<br />{array}'update'=><br />Form key=>value like column=>value to be updated,<br />
     * {array}'fupdate'=><br />Form key=>value like column=>format to set the format of each value,<br />
     * {string}'condition'=><br />Condition to update<br />{string}'complement'=>Sql query to complement the updating (order by, limit, ...)<br />)
     * @return resource Return update query result 
     */
    public function update($struct){
        $update = $this->sql_implode(",", $struct['update'],(isset ($struct['fupdate'])?$struct['fupdate']:array()));
        $condition=(isset ($struct['condition'])?$struct['condition']:"1");
        $complement=(isset ($struct['complement'])?$struct['complement']:"");
//        $this->sql=vsprintf("UPDATE %s SET %s WHERE %s %s", array($struct['table'],$update,$condition,$complement));
        $this->sql=sprintf("UPDATE %s SET %s WHERE %s %s",$struct['table'],$update,$condition,$complement);
        return $this->execute();
    }
    
    /**
     * Delete in a table
     * @param array $struct Structure to form delete query<br />(<br />
     * {string}'table'=><br />Table name,<br />{string}'options'=><br />Options to delete,<br />
     * {string}'condition'=><br />Condition,<br />{string}'complement'=><br />Sql query to complement deleting<br />).
     * @return resource Return delete query result 
     */
    public function delete($struct){
        $options=(isset($struct['options'])?($struct['options']):("")) ;
        $condition=(isset ($struct['condition']) ?($struct['condition']):("0"));
        $complement=(isset ($struct['complement']) ?($struct['complement']):(""));
//        $this->sql=vsprintf("DETELE %s FROM %s WHERE %s %s", array( $options ,$struct['table'],$condition,$complement));
        $this->sql=sprintf("DELETE %s FROM %s WHERE %s %s", $options ,$struct['table'],$condition,$complement);
        return $this->execute();
    }
    
    /**
     * Get an sql comparation sentece
     * @param string $glue Operator conditional (AND, OR) to join each element of $array.
     * @param array $array A key array to form key = 'value'; if key is a number then form value. 
     * @param array $format_array A key array to set the format of $array value.
     * @return string Generally a sql query comparation.
     */
    public function sql_implode($glue,$array,$format_array=array(),$format_value="'%s'"){
        $sentence=array();
        foreach ($array as $key => $value){
            $sentence[] = vsprintf(
                            is_numeric($key)?(isset ($format_array[$key])?$format_array[$key]:$format_value):("%s = ".(isset ($format_array[$key])?$format_array[$key]:"'%s'")),
                            is_numeric($key)?array($value):array($key,$value)
                        );
        }
        return implode($glue,$sentence);
    }
    
    /**
     * Get last id inserted
     * @return string Get last inserted id 
     */
    public function get_last_id() { $row = $this->get_row("SELECT LAST_INSERT_ID()"); return $row[0]; }

    /**
     * Get the first row of a select sql query
     * @param string $sql Select sql query.
     * @return array The firt row of a select (like numeric array).
     */
    public function get_row($sql=null) { return mysql_fetch_row($this->execute($sql)); }

}

?>
