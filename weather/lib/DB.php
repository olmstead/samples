<?php

/**
 *  database connection
 */
class DB {
    
    // configure databse here
    const DB_HOST = 'localhost';
    const DB_USER = 'root';
    const DB_PASSWORD = 'shanidog';
    const DB_NAME = 'weather';
    
    static private $_conn;
    
    static public function getConnection() {
        if (empty(self::$_conn)) {
            // create connection if doesnt exists already
            try {
                $conStr = 'mysql: host='.self::DB_HOST.';dbname='.self::DB_NAME;
                self::$_conn = new PDO($conStr, self::DB_USER, self::DB_PASSWORD);  
            } catch (PDOException $e) {
                return NULL;
            }
        }
        return self::$_conn;
    }
}
