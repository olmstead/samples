<?php

/**
 * base entity class
 */
class Entity {
    private $_conn;
    public function __construct() {
        $this->_conn = DB::getConnection();
    }
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return NULL;
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}
