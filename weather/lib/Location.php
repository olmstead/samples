<?php
/** 
 * data class for weather locations
 */
class Location extends Entity implements Persistable {
    
    private $_name;
    private $_wban;
    
    public function __construct($name, $wban) {
        parent::__construct();
        $this->_name = $name;
        $this->_wban = $wban;
    }
    /**
     * store location in database
     */
    public function persist() {
        try {
            $this->_conn->query("INSERT INTO location (name) VALUES ('$this->_name')");
        } catch (PDOException $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Find a unique weather location buy wban id
     * 
     * @param integer $wban
     * @return null|\Location
     */
    static function find($wban) {
        try {
            $sql = "SELECT name FROM location WHERE wban = $wban";
            $results = DB::getConnection()->query($sql);
        } catch (PDOException $e) {
            return NULL;
        }
        
        if (!$results->rowCount()) { 
            return NULL;
        }   
        
        return new Location($results->row['name'], $wban);
    }
}
