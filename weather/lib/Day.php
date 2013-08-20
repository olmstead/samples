<?php

class Day extends Entity implements Persistable {
    // data indices
    const TMAX_KEY = 'tmax';
    const TMIN_KEY = 'tmin';
    const TAVG_KEY = 'tavg';
    const WBAN_KEY = 'wban';
    
    private $_id;
    private $_data;
    private $_date;
    private $_wban;

    public function __construct($data, $date, $wban) {
        parent::__construct();
        $this->_data = $data;
        $this->_date = $date;
        $this->_wban = $wban;
    }
    /**
     * 
     * @return array<interger>
     */
    public function getData() {
        return $this->_data;
    }
    
    /**
     * persist data to databse
     * @return boolean
     */
    public function persist() {
        
        // validate data
        $hasData = false;
        foreach ($this->_data as $item) {
            if (!empty($item)) {
                $hasData = TRUE;
            }
        }

        // no data found, do not bother to persist
        if (!$hasData) { 
            return FALSE;
        }
        
        try {
            // verify wban location exists
            $sql = "SELECT * FROM location WHERE wban = $this->_wban";
            $results = $this->_conn->query($sql);
            if (!$results->rowCount()) {
                echo "ERROR: wban loction $this->_wban missing from database. Add before importing datasets";
                return FALSE;
            }

            // get the data
            $tmax = $this->_data[Day::TMAX_KEY] ? $this->_data[Day::TMAX_KEY] : 'NULL';
            $tmin = $this->_data[Day::TMIN_KEY] ? $this->_data[Day::TMIN_KEY] : 'NULL';
            $tavg = $this->_data[Day::TAVG_KEY] ? $this->_data[Day::TAVG_KEY] : 'NULL'; 

            // check if day already exists
            $sql = "SELECT * FROM day WHERE day_of_year = '$this->_date' AND wban = $this->_wban";
            $results = $this->_conn->query($sql);

            // upsert data
            if ($results->rowCount()) {
                // day does not exists, insert
                $sql = "UPDATE day SET wban = $this->_wban, day_of_year = '$this->_date', max_temp = $tmax, min_temp = $tmin, avg_temp = $tavg";
                $sql .= " WHERE day_of_year = '$this->_date' AND wban = $this->_wban";
                $results = $this->_conn->query($sql);
            } else {
                // day exists, update
                $sql = "INSERT INTO day (wban, day_of_year, max_temp, min_temp, avg_temp) VALUES ($this->_wban, '$this->_date', $tmax, $tmin, $tavg)";
                $results = $this->_conn->query($sql);
            }
        } catch (PDOException $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * find a unique data for a day by wban and date
     * 
     * @param integer $wban
     * @param string $day
     * @return null|\Day
     */
    static function find($wban, $day) {
        try {
            $sql = "SELECT * FROM day WHERE day_of_year = '$day' AND wban = $wban";
            $results = DB::getConnection()->query($sql);
        } catch (PDOException $e) {
            return NULL;
        }
        
        if (!$results->rowCount()) { 
            return NULL;
        }   
        
        // wban is unique, so only one row
        foreach ($results as $row) {
            $data = array(
                self::TMAX_KEY => $row['max_temp'],
                self::TMIN_KEY => $row['min_temp'],
                self::TAVG_KEY => $row['avg_temp'],
            );
        }
        
        return new Day($data, $day, $wban);
    }
}

